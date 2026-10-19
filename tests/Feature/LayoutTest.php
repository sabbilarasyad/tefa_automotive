<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_has_hamburger_button_and_contact_link(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('aria-controls="public-menu"', false)
            ->assertSee('aria-label="Buka menu navigasi"', false)
            ->assertSee('Kontak');
    }

    public function test_public_nav_has_no_duplicate_login(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Login', false);
        
        // Count occurrences of Login link/button - should be 1
        $response = $this->get(route('public.home'));
        $content = $response->getContent();
        $loginCount = substr_count($content, 'Login');
        $this->assertEquals(1, $loginCount, 'Login link should appear exactly once');
    }

    public function test_admin_nav_has_id_main_nav_and_all_links_once_for_super_admin(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.pkb.index'))
            ->assertOk();

        $content = $response->getContent();
        
        // Check for main-nav id
        $this->assertStringContainsString('id="main-nav"', $content);
        
        // Extract nav content to check links only in nav
        $navStart = strpos($content, 'id="main-nav"');
        $navEnd = strpos($content, '</nav>', $navStart);
        $navContent = substr($content, $navStart, $navEnd - $navStart + 6);
        
        // Check that each expected menu item appears once in the nav
        $expectedLinks = [
            'Daftar PKB',
            'Progres Pekerjaan',
            'Kasir',
            'Servis Baru',
            'Pengguna',
            'Stok',
            'Jasa',
            'Riwayat Servis',
            'Reminder',
        ];

        foreach ($expectedLinks as $link) {
            $count = substr_count($navContent, $link);
            $this->assertEquals(1, $count, "Link '{$link}' should appear exactly once in nav for Super Admin");
        }
    }

    public function test_login_page_has_no_green_brand_color(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('#126b56');
    }

    public function test_public_pages_do_not_show_hardcoded_address_and_email(): void
    {
        $routes = [
            route('public.home'),
            route('public.about'),
            route('public.services.index'),
            route('public.gallery'),
            route('public.contact'),
        ];

        foreach ($routes as $route) {
            $this->get($route)
                ->assertOk()
                ->assertDontSee('Jl. Raya No. 123')
                ->assertDontSee('info@tefa.co.id');
        }
    }

    public function test_public_footer_services_come_from_database(): void
    {
        Service::create([
            'code' => 'SRV-FOOTER-1',
            'name' => 'Footer Service 1',
            'price' => 100000,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        Service::create([
            'code' => 'SRV-FOOTER-2',
            'name' => 'Footer Service 2',
            'price' => 200000,
            'documentation_required' => true,
            'is_active' => true,
        ]);

        Service::create([
            'code' => 'SRV-INACTIVE',
            'name' => 'Inactive Service',
            'price' => 300000,
            'documentation_required' => false,
            'is_active' => false,
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Footer Service 1')
            ->assertSee('Footer Service 2')
            ->assertDontSee('Inactive Service');
    }
}