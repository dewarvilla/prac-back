<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            // Catalogos
            'catalogos.view','catalogos.create','catalogos.edit','catalogos.delete',

            // Programaciones
            'programaciones.view','programaciones.create','programaciones.edit','programaciones.delete',
            'programaciones.aprobar.departamento','programaciones.rechazar.departamento',
            'programaciones.aprobar.postgrados','programaciones.rechazar.postgrados',
            'programaciones.aprobar.decano','programaciones.rechazar.decano',
            'programaciones.aprobar.jefe_postgrados','programaciones.rechazar.jefe_postgrados',
            'programaciones.aprobar.vicerrectoria','programaciones.rechazar.vicerrectoria',

            // Creaciones
            'creaciones.view','creaciones.create','creaciones.edit','creaciones.delete',
            'creaciones.aprobar.comite_acreditacion','creaciones.rechazar.comite_acreditacion',
            'creaciones.aprobar.consejo_facultad','creaciones.rechazar.consejo_facultad',
            'creaciones.aprobar.consejo_academico','creaciones.rechazar.consejo_academico',

            // Fechas
            'fechas.view','fechas.create','fechas.edit','fechas.delete',

            // Salarios
            'salarios.view','salarios.create','salarios.edit','salarios.delete',

            // Rutas (rutas + peajes + compute + sync)
            'rutas.view','rutas.create','rutas.edit','rutas.delete',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roles = [
            'docente' => [
            'programaciones.view',
            'programaciones.create','programaciones.edit','programaciones.delete',

            'creaciones.view',
            'creaciones.create','creaciones.edit','creaciones.delete',

            'rutas.view','rutas.create','rutas.edit','rutas.delete',

            'catalogos.view'
            ],

            'jefe_departamento' => [
                'programaciones.view',
                'programaciones.aprobar.departamento','programaciones.rechazar.departamento',
                'rutas.view'
            ],
            'coordinador_postgrados' => [
                'programaciones.view',
                'programaciones.aprobar.postgrados','programaciones.rechazar.postgrados',
                'rutas.view'
            ],
            'decano' => [
                'programaciones.view',
                'programaciones.aprobar.decano','programaciones.rechazar.decano',
                'rutas.view'
            ],
            'jefe_postgrados' => [
                'programaciones.view',
                'programaciones.aprobar.jefe_postgrados','programaciones.rechazar.jefe_postgrados',
                'rutas.view'
            ],
            'vicerrectoria' => [
                'programaciones.view','fechas.view',
                'programaciones.aprobar.vicerrectoria','programaciones.rechazar.vicerrectoria',
                'fechas.create','fechas.edit','fechas.delete',
                'rutas.view'
            ],
            'comite_acreditacion' => [
                'creaciones.view',
                'creaciones.aprobar.comite_acreditacion','creaciones.rechazar.comite_acreditacion',
            ],
            'consejo_facultad' => [
                'creaciones.view',
                'creaciones.aprobar.consejo_facultad','creaciones.rechazar.consejo_facultad',
            ],
            'consejo_academico' => [
                'creaciones.view',
                'creaciones.aprobar.consejo_academico','creaciones.rechazar.consejo_academico',
            ],
            'administrador' => [
                'programaciones.view','creaciones.view','fechas.view','catalogos.view','salarios.view',
                'creaciones.create','creaciones.edit','creaciones.delete',
                'programaciones.create','programaciones.edit','programaciones.delete',
                'fechas.create','fechas.edit','fechas.delete',
                'catalogos.create','catalogos.edit','catalogos.delete',
                'salarios.create','salarios.edit','salarios.delete',

                // Rutas
                'rutas.view','rutas.create','rutas.edit','rutas.delete',
            ],
        ];

        $allRolePerms = collect($roles)->flatten()->unique()->values();
        $missing = $allRolePerms->diff($perms);
        if ($missing->isNotEmpty()) {
            throw new \RuntimeException('Faltan permisos en $perms: '.implode(', ', $missing->all()));
        }

        $super = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $super->syncPermissions(Permission::where('guard_name', 'web')->get());

        foreach ($roles as $name => $permsOfRole) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions($permsOfRole);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
