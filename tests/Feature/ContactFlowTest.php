<?php

namespace Tests\Feature;

use App\Jobs\SendContactMessageNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_submission_saves_and_queues_job(): void
    {
        Queue::fake();

        $response = $this->postJson('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Hello there',
            'message' => 'This is a long enough test message.',
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Thank you! Your message received.']);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_read' => false,
        ]);

        Queue::assertPushed(SendContactMessageNotificationJob::class);
    }

    public function test_invalid_submission_returns_422_with_errors(): void
    {
        $response = $this->postJson('/contact', [
            'name' => 'A',
            'email' => 'bad-email',
            'subject' => 'ok',
            'message' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_html_tags_are_sanitized(): void
    {
        $response = $this->postJson('/contact', [
            'name' => 'Jane <script>alert(1)</script>',
            'email' => 'jane@example.com',
            'subject' => 'Hello There',
            'message' => 'Nice message with <b>html</b> tags',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Jane alert(1)',
            'email' => 'jane@example.com',
            'message' => 'Nice message with html tags',
        ]);
    }
}
