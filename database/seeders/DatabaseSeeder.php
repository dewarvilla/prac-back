<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            TestUsersSeeder::class,
        ]);

        // Solo datos dummy en entornos locales (opcional)
        // if (app()->environment(['local','testing']) && (bool) env('SEED_FAKE', false)) {
        //     $this->call(DevSampleSeeder::class);
        // }
    }
}
