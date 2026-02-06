<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // AJUSTA estos nombres a los roles reales de tu app
        $roles = [
            'administrador',
            'docente',
            'jefe_departamento',
            'coordinador_postgrados',
            'decano',
            'jefe_postgrados',
            'comite_acreditacion',
            'consejo_facultad',
            'consejo_academico',
            'vicerrectoria',
        ];

        foreach ($roles as $roleName) {

            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);

            for ($i = 1; $i <= 2; $i++) {
                $slug      = Str::slug($roleName, '_');
                $email     = "{$slug}{$i}@test.local";
                $fullName  = ucfirst($roleName) . " {$i}";

                $user = User::firstOrCreate(
                    [
                        'email' => $email,
                    ],
                    [
                        'name'     => $fullName,
                        'password' => Hash::make('password'), // SOLO para desarrollo
                    ]
                );

                $user->assignRole($role);

                $this->command?->info("Usuario de prueba: {$email} (rol: {$roleName})");
            }
        }
    }
}
