<?php

namespace Tests\Feature;

use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    private function educationData(array $overrides = []): array
    {
        return array_merge([
            'user_id' => auth()->id(),
            'institution' => 'Harvard University',
            'field_of_study' => 'Computer Science',
            'degree' => 'Bachelor',
            'start_date' => 2018,
            'end_date' => 2022,
            'is_currently_studying' => false,
        ], $overrides);
    }

    public function test_guest_is_redirected_from_education_index(): void
    {
        $this->get('/222/educations')->assertRedirect(route('login'));
    }

    public function test_index_lists_educations(): void
    {
        $this->actingAsAdmin();

        Education::create($this->educationData(['institution' => 'MIT']));
        Education::create($this->educationData(['institution' => 'Stanford']));

        $this->get(route('admin.educations.index'))
            ->assertOk()
            ->assertSee('MIT')
            ->assertSee('Stanford');
    }

    public function test_index_searches_by_institution(): void
    {
        $user = $this->actingAsAdmin();

        Education::create($this->educationData(['institution' => 'MIT']));
        Education::create($this->educationData(['institution' => 'Stanford']));

        $this->get(route('admin.educations.index', ['q' => 'stan']))
            ->assertOk()
            ->assertSee('Stanford')
            ->assertDontSee('MIT');
    }

    public function test_create_page_renders(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.educations.create'))->assertOk();
    }

    public function test_store_validates_and_saves(): void
    {
        $user = $this->actingAsAdmin();

        $this->post(route('admin.educations.store'), $this->educationData())
            ->assertRedirect(route('admin.educations.index'));

        $this->assertDatabaseHas('educations', [
            'user_id' => $user->id,
            'institution' => 'Harvard University',
            'field_of_study' => 'Computer Science',
            'degree' => 'Bachelor',
            'start_date' => '2018-01-01 00:00:00',
            'end_date' => '2022-01-01 00:00:00',
            'is_currently_studying' => false,
        ]);
    }

    public function test_store_rejects_missing_fields(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.educations.store'), [])
            ->assertSessionHasErrors(['institution', 'field_of_study', 'degree', 'start_date']);

        $this->assertDatabaseCount('educations', 0);
    }

    public function test_store_rejects_duplicate_institution(): void
    {
        $user = $this->actingAsAdmin();

        Education::create($this->educationData());

        $this->post(route('admin.educations.store'), $this->educationData())
            ->assertSessionHasErrors('institution');

        $this->assertDatabaseCount('educations', 1);
    }

    public function test_store_requires_end_date_when_not_currently_studying(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.educations.store'), $this->educationData([
            'is_currently_studying' => false,
            'end_date' => null,
        ]))->assertSessionHasErrors('end_date');
    }

    public function test_store_allows_missing_end_date_when_currently_studying(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.educations.store'), $this->educationData([
            'is_currently_studying' => true,
            'end_date' => null,
        ]))->assertRedirect(route('admin.educations.index'));

        $this->assertDatabaseHas('educations', [
            'institution' => 'Harvard University',
            'is_currently_studying' => true,
            'end_date' => null,
        ]);
    }

    public function test_store_rejects_end_year_before_start_year(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.educations.store'), $this->educationData([
            'start_date' => 2022,
            'end_date' => 2020,
        ]))->assertSessionHasErrors('end_date');
    }

    public function test_edit_page_is_prefilled(): void
    {
        $this->actingAsAdmin();

        $education = Education::create($this->educationData(['institution' => 'MIT']));

        $this->get(route('admin.educations.edit', $education))
            ->assertOk()
            ->assertSee('MIT');
    }

    public function test_update_validates_and_updates(): void
    {
        $this->actingAsAdmin();

        $education = Education::create($this->educationData());

        $this->put(route('admin.educations.update', $education), $this->educationData([
            'institution' => 'Cambridge University',
            'degree' => 'Master',
        ]))->assertRedirect(route('admin.educations.index'));

        $this->assertDatabaseHas('educations', [
            'id' => $education->id,
            'institution' => 'Cambridge University',
            'degree' => 'Master',
        ]);
    }

    public function test_update_ignores_own_institution_on_duplicate_check(): void
    {
        $this->actingAsAdmin();

        $education = Education::create($this->educationData());

        $this->put(route('admin.educations.update', $education), $this->educationData([
            'institution' => 'Harvard University',
        ]))->assertRedirect(route('admin.educations.index'));
    }

    public function test_destroy_soft_deletes_education(): void
    {
        $this->actingAsAdmin();

        $education = Education::create($this->educationData());

        $this->delete(route('admin.educations.destroy', $education))
            ->assertRedirect(route('admin.educations.index'));

        $this->assertSoftDeleted('educations', ['id' => $education->id]);
    }

    public function test_restore_recovers_soft_deleted_education(): void
    {
        $this->actingAsAdmin();

        $education = Education::create($this->educationData());
        $education->delete();

        $this->post(route('admin.educations.restore', $education->id))
            ->assertRedirect(route('admin.educations.index'));

        $this->assertDatabaseHas('educations', [
            'id' => $education->id,
            'deleted_at' => null,
        ]);
    }
}
