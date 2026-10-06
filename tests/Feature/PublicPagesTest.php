<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    // Public pages should be accessible without authentication

    public function test_public_home_page_accessible(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('TEFA Automotive');
    }

    public function test_public_about_page_accessible(): void
    {
        $this->get(route('public.about'))
            ->assertOk()
            ->assertSee('Tentang TEFA Automotive');
    }

    public function test_public_services_index_accessible(): void
    {
        Service::create([
            'code' => 'SRV-001',
            'name' => 'Servis Berkala',
            'description' => 'Servis berkala kendaraan',
            'price' => 200000,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->get(route('public.services.index'))
            ->assertOk()
            ->assertSee('Layanan Kami')
            ->assertSee('Servis Berkala');
    }

    public function test_public_services_does_not_show_price(): void
    {
        Service::create([
            'code' => 'SRV-002',
            'name' => 'Ganti Oli',
            'description' => 'Ganti oli mesin',
            'price' => 150000,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->get(route('public.services.index'))
            ->assertOk()
            ->assertDontSee('Rp150.000')
            ->assertDontSee('150000');
    }

    public function test_public_service_show_accessible(): void
    {
        $service = Service::create([
            'code' => 'SRV-003',
            'name' => 'Tune Up',
            'description' => 'Tune up mesin',
            'price' => 300000,
            'documentation_required' => true,
            'is_active' => true,
        ]);

        $this->get(route('public.services.show', $service))
            ->assertOk()
            ->assertSee('Tune Up')
            ->assertDontSee('SRV-003')
            ->assertDontSee('Rp300.000')
            ->assertDontSee('300000')
            ->assertDontSee('Dokumentasi Foto');
    }

    public function test_public_gallery_page_accessible(): void
    {
        $this->get(route('public.gallery'))
            ->assertOk()
            ->assertSee('Galeri');
    }

    public function test_public_contact_page_accessible(): void
    {
        $this->get(route('public.contact'))
            ->assertOk()
            ->assertSee('Kontak');
    }

    public function test_public_contact_form_submission(): void
    {
        $this->post(route('public.contact.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
        ]);
    }

    public function test_public_contact_form_validation(): void
    {
        $this->post(route('public.contact.store'), [
            'name' => '',
            'email' => 'invalid',
            'subject' => '',
            'message' => '',
        ])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_public_routes_not_404(): void
    {
        $routes = [
            route('public.home'),
            route('public.about'),
            route('public.services.index'),
            route('public.gallery'),
            route('public.contact'),
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertOk();
        }
    }

    public function test_public_services_show_inactive_returns_404(): void
    {
        $service = Service::create([
            'code' => 'SRV-INACTIVE',
            'name' => 'Inactive Service',
            'price' => 100000,
            'documentation_required' => false,
            'is_active' => false,
        ]);

        $this->get(route('public.services.show', $service))
            ->assertNotFound();
    }
}