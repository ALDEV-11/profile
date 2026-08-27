<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    private function makeMessage(array $overrides = []): ContactMessage
    {
        return ContactMessage::factory()->create($overrides);
    }

    public function test_guest_is_redirected_from_inbox(): void
    {
        $this->get('/222/messages')->assertRedirect(route('login'));
    }

    public function test_index_lists_messages_newest_first(): void
    {
        $this->actingAsAdmin();

        $older = $this->makeMessage(['subject' => 'Older subject']);
        $newer = $this->makeMessage(['subject' => 'Newer subject']);

        $this->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee('Newer subject')
            ->assertSee('Older subject');
    }

    public function test_index_searches_by_name_email_and_subject(): void
    {
        $this->actingAsAdmin();

        $this->makeMessage(['name' => 'Budi Santoso', 'email' => 'budi@example.com', 'subject' => 'Tanya harga']);
        $this->makeMessage(['name' => 'Ani', 'email' => 'ani@example.com', 'subject' => 'Kerja sama']);

        $this->get(route('admin.messages.index', ['q' => 'Budi']))
            ->assertOk()
            ->assertSee('budi@example.com')
            ->assertDontSee('ani@example.com');

        $this->get(route('admin.messages.index', ['q' => 'ani@example.com']))
            ->assertOk()
            ->assertSee('ani@example.com')
            ->assertDontSee('budi@example.com');

        $this->get(route('admin.messages.index', ['q' => 'Kerja sama']))
            ->assertOk()
            ->assertSee('ani@example.com')
            ->assertDontSee('budi@example.com');
    }

    public function test_index_filters_by_read_status(): void
    {
        $this->actingAsAdmin();

        $this->makeMessage(['email' => 'belum@example.com', 'subject' => 'Unread one']);
        $this->makeMessage(['email' => 'dibaca@example.com', 'subject' => 'Read one', 'is_read' => true]);

        $this->get(route('admin.messages.index', ['status' => 'unread']))
            ->assertOk()
            ->assertSee('belum@example.com')
            ->assertDontSee('dibaca@example.com');

        $this->get(route('admin.messages.index', ['status' => 'read']))
            ->assertOk()
            ->assertSee('dibaca@example.com')
            ->assertDontSee('belum@example.com');
    }

    public function test_index_paginates_ten_per_page(): void
    {
        $this->actingAsAdmin();

        for ($i = 1; $i <= 11; $i++) {
            $this->makeMessage(['email' => "nomor{$i}@example.com", 'subject' => "Pesan nomor {$i}"]);
        }

        $this->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee('Pesan nomor 11')
            ->assertDontSee('nomor1@example.com');

        $this->get(route('admin.messages.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Pesan nomor 1');
    }

    public function test_show_displays_message_and_marks_as_read(): void
    {
        $this->actingAsAdmin();

        $message = $this->makeMessage([
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'subject' => 'Halo Admin',
            'message' => 'Isi pesan yang cukup panjang untuk ditampilkan.',
        ]);

        $this->get(route('admin.messages.show', $message))
            ->assertOk()
            ->assertSee('Rina')
            ->assertSee('rina@example.com')
            ->assertSee('Halo Admin')
            ->assertSee('Isi pesan yang cukup panjang untuk ditampilkan.');

        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'is_read' => true,
        ]);
    }

    public function test_show_provides_prev_next_navigation(): void
    {
        $this->actingAsAdmin();

        $a = $this->makeMessage(['subject' => 'Pesan A']);
        $b = $this->makeMessage(['subject' => 'Pesan B']);
        $c = $this->makeMessage(['subject' => 'Pesan C']);

        $this->get(route('admin.messages.show', $b))
            ->assertOk()
            ->assertSee('Pesan A')
            ->assertSee('Pesan C');
    }

    public function test_toggle_read_marks_message_as_read(): void
    {
        $this->actingAsAdmin();

        $message = $this->makeMessage(['is_read' => false]);

        $this->postJson(route('admin.messages.toggle-read', $message), ['is_read' => 1])
            ->assertOk()
            ->assertJson(['success' => true, 'is_read' => true]);

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'is_read' => true]);
    }

    public function test_toggle_read_marks_message_as_unread(): void
    {
        $this->actingAsAdmin();

        $message = $this->makeMessage(['is_read' => true]);

        $this->postJson(route('admin.messages.toggle-read', $message), ['is_read' => 0])
            ->assertOk()
            ->assertJson(['success' => true, 'is_read' => false]);

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'is_read' => false]);
    }

    public function test_destroy_soft_deletes_message(): void
    {
        $this->actingAsAdmin();

        $message = $this->makeMessage();

        $this->delete(route('admin.messages.destroy', $message))
            ->assertRedirect(route('admin.messages.index'));

        $this->assertSoftDeleted('contact_messages', ['id' => $message->id]);
        $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);
    }

    public function test_trashed_filter_lists_soft_deleted_messages(): void
    {
        $this->actingAsAdmin();

        $kept = $this->makeMessage(['email' => 'aktif@example.com', 'subject' => 'Pesan aktif']);
        $trashed = $this->makeMessage(['email' => 'hapus@example.com', 'subject' => 'Pesan dihapus']);
        $trashed->delete();

        $this->get(route('admin.messages.index', ['status' => 'trashed']))
            ->assertOk()
            ->assertSee('Pesan dihapus')
            ->assertDontSee('aktif@example.com');
    }

    public function test_restore_brings_message_back_to_inbox(): void
    {
        $this->actingAsAdmin();

        $message = $this->makeMessage();
        $message->delete();

        $this->post(route('admin.messages.restore', $message->id))
            ->assertRedirect(route('admin.messages.index'));

        $this->assertNotSoftDeleted('contact_messages', ['id' => $message->id]);
    }
}
