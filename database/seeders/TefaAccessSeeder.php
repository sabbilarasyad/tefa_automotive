<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TefaAccessSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roleNames = ['SUPER_ADMIN', 'HEAD_TEFA', 'ADMIN_CS', 'MEKANIK', 'KASIR'];
        $roles = collect($roleNames)->mapWithKeys(fn (string $name) => [
            $name => Role::create(['name' => $name]),
        ]);

        $permissionNames = [
            'pkb.view_all' => 'Melihat semua PKB',
            'pkb.view_own' => 'Melihat PKB tugas sendiri',
            'pkb.view_waiting_payment' => 'Melihat PKB menunggu pembayaran',
            'pkb.create' => 'Membuat PKB',
            'pkb.edit' => 'Mengubah PKB',
            'pkb.assign_mechanic' => 'Menugaskan mekanik',
            'pkb.cancel' => 'Membatalkan PKB',
            'pkb.force_cancel' => 'Membatalkan PKB secara paksa',
            'pkb.print' => 'Mencetak PKB',
            'pkb.start_work' => 'Mencatat mulai pekerjaan',
            'pkb.update_work_progress' => 'Memperbarui progres pekerjaan',
            'pkb.upload_documentation' => 'Mengunggah dokumentasi pekerjaan',
            'pkb.delete_documentation' => 'Menghapus dokumentasi pekerjaan',
            'pkb.validate_complete' => 'Memvalidasi pekerjaan selesai',
            'kasir.view_checklist' => 'Melihat checklist dokumentasi',
            'kasir.process_payment' => 'Memproses pembayaran',
            'kasir.print_invoice' => 'Mencetak invoice',
            'master.manage_services' => 'Mengelola master jasa',
            'master.manage_spareparts' => 'Mengelola master sparepart',
            'master.adjust_stock' => 'Menyesuaikan stok',
            'user.manage' => 'Mengelola pengguna',
            'user.reset_password' => 'Reset password pengguna',
            'report.view' => 'Melihat laporan',
            'reminder.manage' => 'Mengelola reminder',
            'activity_log.view' => 'Melihat audit log',
        ];

        $permissions = collect($permissionNames)->mapWithKeys(fn (string $name, string $code) => [
            $code => Permission::create(['code' => $code, 'name' => $name]),
        ]);

        $rolePermissions = [
            'SUPER_ADMIN' => array_values(array_diff(
                array_keys($permissionNames),
                ['pkb.view_own', 'pkb.view_waiting_payment'],
            )),
            'HEAD_TEFA' => ['pkb.view_all', 'report.view'],
            'ADMIN_CS' => [
                'pkb.view_all', 'pkb.create', 'pkb.edit', 'pkb.assign_mechanic', 'pkb.cancel', 'pkb.print',
                'pkb.start_work', 'pkb.update_work_progress', 'pkb.upload_documentation',
                'pkb.delete_documentation', 'pkb.validate_complete', 'master.manage_services',
                'master.manage_spareparts', 'master.adjust_stock', 'report.view', 'reminder.manage',
            ],
            'MEKANIK' => ['pkb.view_own'],
            'KASIR' => [
                'pkb.view_waiting_payment', 'pkb.start_work', 'pkb.update_work_progress',
                'pkb.upload_documentation', 'pkb.delete_documentation', 'pkb.validate_complete',
                'kasir.view_checklist', 'kasir.process_payment', 'kasir.print_invoice', 'reminder.manage',
            ],
        ];

        foreach ($rolePermissions as $roleName => $codes) {
            $roles[$roleName]->permissions()->sync($permissions->only($codes)->pluck('id')->all());
        }

        $demoAccounts = [
            ['SUPER_ADMIN', 'superadmin', 'Super Administrator'],
            ['HEAD_TEFA', 'headtefa', 'Kepala TEFA'],
            ['ADMIN_CS', 'admincs', 'Admin Customer Service'],
            ['MEKANIK', 'mekanik', 'Mekanik Demo'],
            ['KASIR', 'kasir', 'Kasir Demo'],
        ];

        foreach ($demoAccounts as [$roleName, $username, $name]) {
            User::create([
                'role_id' => $roles[$roleName]->id,
                'name' => $name,
                'username' => $username,
                'email' => $username.'@tefa.local',
                'password' => 'password',
                'is_active' => true,
            ]);
        }
    }
}