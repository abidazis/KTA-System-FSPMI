<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'anggota-view',
            'anggota-create',
            'anggota-edit',
            'anggota-delete',
            'import',
            'cetak',
            'pengurus',
            'wilayah',
            'user-view',
            'user-create',
            'user-edit',
            'user-delete',
            'formulasi-nomor-anggota',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'anggota-view',
            'anggota-create',
            'anggota-edit',
            'anggota-delete',
            'import',
            'cetak',
            'formulasi-nomor-anggota',
        ]);

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions([
            'anggota-view',
            'anggota-create',
            'import',
            'cetak',
        ]);
    }
}
