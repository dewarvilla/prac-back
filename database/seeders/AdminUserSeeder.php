<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Demo', 'password' => Hash::make('secret123')]
        );

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $admin->syncRoles([$role]);
    }
}
