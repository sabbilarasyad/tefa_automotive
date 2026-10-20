<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_each_demo_role_can_login_and_receive_a_token(): void
    {
        foreach (['superadmin', 'headtefa', 'admincs', 'mekanik', 'kasir'] as $username) {
            $this->postJson('/api/login', [
                'username' => $username,
                'password' => 'password',
            ])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.token_type', 'Bearer')
                ->assertJsonPath('data.user.username', $username)
                ->assertJsonStructure(['data' => ['token']]);
        }

        $this->assertDatabaseCount('activity_logs', 5);
    }

    public function test_login_response_contains_token_instead_of_access_token(): void
    {
        $this->postJson('/api/login', [
            'username' => 'admincs',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['token']])
            ->assertJsonMissingPath('data.access_token');
    }

    public function test_me_returns_the_role_permission_matrix_and_requires_a_token(): void
    {
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);

        $rolePermissions = [
            'superadmin' => [
                'pkb.view_all', 'pkb.create', 'pkb.edit', 'pkb.assign_mechanic', 'pkb.cancel',
                'pkb.force_cancel', 'pkb.print', 'pkb.start_work', 'pkb.update_work_progress',
                'pkb.upload_documentation', 'pkb.delete_documentation', 'pkb.validate_complete',
                'kasir.view_checklist', 'kasir.process_payment', 'kasir.print_invoice',
                'master.manage_services', 'master.manage_spareparts', 'master.adjust_stock',
                'user.manage', 'user.reset_password', 'report.view', 'reminder.manage', 'activity_log.view',
                'contact_message.manage',
            ],
            'headtefa' => ['pkb.view_all', 'report.view'],
            'admincs' => [
                'pkb.view_all', 'pkb.create', 'pkb.edit', 'pkb.assign_mechanic', 'pkb.cancel', 'pkb.print',
                'pkb.start_work', 'pkb.update_work_progress', 'pkb.upload_documentation',
                'pkb.delete_documentation', 'pkb.validate_complete', 'master.manage_services',
                'master.manage_spareparts', 'master.adjust_stock', 'report.view', 'reminder.manage',
                'contact_message.manage',
            ],
            'mekanik' => ['pkb.view_own'],
            'kasir' => [
                'pkb.view_waiting_payment', 'pkb.start_work', 'pkb.update_work_progress',
                'pkb.upload_documentation', 'pkb.delete_documentation', 'pkb.validate_complete',
                'kasir.view_checklist', 'kasir.process_payment', 'kasir.print_invoice', 'reminder.manage',
            ],
        ];

        $expectedRoles = [
            'superadmin' => 'SUPER_ADMIN',
            'headtefa' => 'HEAD_TEFA',
            'admincs' => 'ADMIN_CS',
            'mekanik' => 'MEKANIK',
            'kasir' => 'KASIR',
        ];

        foreach ($rolePermissions as $username => $expectedPermissions) {
            $token = $this->login($username);
            $response = $this->withToken($token)->getJson('/api/me')->assertOk();
            $actualPermissions = $response->json('data.permissions');
            sort($actualPermissions);
            sort($expectedPermissions);

            $response->assertJsonPath('data.role', $expectedRoles[$username]);
            $this->assertSame($expectedPermissions, $actualPermissions);
        }
    }

    public function test_invalid_bearer_token_returns_401(): void
    {
        $this->get('/api/me', [
            'Authorization' => 'Bearer token-ngasal-yang-salah',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_logout_revokes_only_the_current_device_token(): void
    {
        $firstToken = $this->login('admincs', 'browser');
        $secondToken = $this->login('admincs', 'android');

        $this->withToken($firstToken)->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withToken($firstToken)->getJson('/api/me')->assertUnauthorized();
        $this->withToken($secondToken)->getJson('/api/me')->assertOk();
    }

    public function test_seeded_passwords_are_hashed(): void
    {
        $user = User::where('username', 'superadmin')->firstOrFail();

        $this->assertNotSame('password', $user->password);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_role_middleware_rejects_a_user_without_the_required_role(): void
    {
        Route::get('/api/test/super-admin', fn () => response()->json(['success' => true]))
            ->middleware(['auth:sanctum', 'role:SUPER_ADMIN']);

        $token = $this->login('mekanik');

        $this->withToken($token)->getJson('/api/test/super-admin')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    private function login(string $username, string $deviceName = 'test-device'): string
    {
        return $this->postJson('/api/login', [
            'username' => $username,
            'password' => 'password',
            'device_name' => $deviceName,
        ])
            ->assertOk()
            ->json('data.token');
    }
}
