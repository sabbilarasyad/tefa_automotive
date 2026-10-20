<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_super_admin_can_access_contact_messages_index(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.contact-messages.index'));

        $response->assertOk();
    }

    /** @test */
    public function test_admin_cs_can_access_contact_messages_index(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $response = $this->actingAs($adminCs)
            ->get(route('admin.contact-messages.index'));

        $response->assertOk();
    }

    /** @test */
    public function test_head_tefa_cannot_access_contact_messages_index(): void
    {
        $this->seed();
        $headTefa = User::where('username', 'headtefa')->firstOrFail();

        $response = $this->actingAs($headTefa)
            ->get(route('admin.contact-messages.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function test_kasir_cannot_access_contact_messages_index(): void
    {
        $this->seed();
        $kasir = User::where('username', 'kasir')->firstOrFail();

        $response = $this->actingAs($kasir)
            ->get(route('admin.contact-messages.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function test_mekanik_cannot_access_contact_messages_index(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();

        $response = $this->actingAs($mekanik)
            ->get(route('admin.contact-messages.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function test_super_admin_can_mark_read_and_unread(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $message = ContactMessage::factory()->create(['read_at' => null]);

        // Mark as read
        $this->actingAs($superAdmin)
            ->put(route('admin.contact-messages.mark-read', $message))
            ->assertRedirect();

        $message->refresh();
        $this->assertNotNull($message->read_at);

        // Mark as unread
        $this->actingAs($superAdmin)
            ->put(route('admin.contact-messages.mark-unread', $message))
            ->assertRedirect();

        $message->refresh();
        $this->assertNull($message->read_at);
    }

    /** @test */
    public function test_admin_cs_can_mark_read_and_unread(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();
        $message = ContactMessage::factory()->create(['read_at' => null]);

        $this->actingAs($adminCs)
            ->put(route('admin.contact-messages.mark-read', $message))
            ->assertRedirect();

        $message->refresh();
        $this->assertNotNull($message->read_at);

        $this->actingAs($adminCs)
            ->put(route('admin.contact-messages.mark-unread', $message))
            ->assertRedirect();

        $message->refresh();
        $this->assertNull($message->read_at);
    }

    /** @test */
    public function test_head_tefa_cannot_mark_read(): void
    {
        $this->seed();
        $headTefa = User::where('username', 'headtefa')->firstOrFail();
        $message = ContactMessage::factory()->create(['read_at' => null]);

        $this->actingAs($headTefa)
            ->put(route('admin.contact-messages.mark-read', $message))
            ->assertForbidden();
    }

    /** @test */
    public function test_super_admin_can_delete_contact_message(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $message = ContactMessage::factory()->create();

        $this->actingAs($superAdmin)
            ->delete(route('admin.contact-messages.destroy', $message))
            ->assertRedirect();

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    /** @test */
    public function test_admin_cs_cannot_delete_contact_message(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();
        $message = ContactMessage::factory()->create();

        $this->actingAs($adminCs)
            ->delete(route('admin.contact-messages.destroy', $message))
            ->assertForbidden();
    }

    /** @test */
    public function test_head_tefa_cannot_delete_contact_message(): void
    {
        $this->seed();
        $headTefa = User::where('username', 'headtefa')->firstOrFail();
        $message = ContactMessage::factory()->create();

        $this->actingAs($headTefa)
            ->delete(route('admin.contact-messages.destroy', $message))
            ->assertForbidden();
    }

    /** @test */
    public function test_kasir_cannot_delete_contact_message(): void
    {
        $this->seed();
        $kasir = User::where('username', 'kasir')->firstOrFail();
        $message = ContactMessage::factory()->create();

        $this->actingAs($kasir)
            ->delete(route('admin.contact-messages.destroy', $message))
            ->assertForbidden();
    }

    /** @test */
    public function test_mekanik_cannot_delete_contact_message(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();
        $message = ContactMessage::factory()->create();

        $this->actingAs($mekanik)
            ->delete(route('admin.contact-messages.destroy', $message))
            ->assertForbidden();
    }

    /** @test */
    public function test_public_contact_form_submission_returns_success_message(): void
    {
        $response = $this->post('/kontak', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
    }

    /** @test */
    public function test_public_contact_form_honeypot_prevents_spam(): void
    {
        // Honeypot field is filled - should silently ignore (return success but not store)
        $response = $this->post('/kontak', [
            'name' => 'Spam Bot',
            'email' => 'spam@example.com',
            'subject' => 'Spam',
            'message' => 'Buy cheap viagra',
            'website' => 'http://spam.com', // honeypot field
        ]);

        // Should return success but not store the message
        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'spam@example.com',
        ]);
    }

    /** @test */
    public function test_honeypot_with_valid_submission_still_works(): void
    {
        // Valid submission without honeypot should work
        $response = $this->post('/kontak', [
            'name' => 'Real User',
            'email' => 'real@example.com',
            'subject' => 'Real Question',
            'message' => 'How much is the service?',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'real@example.com',
        ]);
    }
}