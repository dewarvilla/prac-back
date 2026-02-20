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

        /**
         * Permisos base del sistema (pantallas/acciones generales)
         * + Permisos legacy por módulo (creaciones.*, programaciones.*)
         * + Permisos CENTRALIZADOS por step (approvals.aprobar.{role_key})
         */
        $perms = [
            // ===== Catálogos =====
            'catalogos.view','catalogos.create','catalogos.edit','catalogos.delete',

            // ===== Programaciones =====
            'programaciones.view','programaciones.create','programaciones.edit','programaciones.delete',

            // Legacy por módulo (si aún hay pantallas/guards que los usan)
            'programaciones.aprobar.departamento','programaciones.rechazar.departamento',
            'programaciones.aprobar.postgrados','programaciones.rechazar.postgrados',
            'programaciones.aprobar.decano','programaciones.rechazar.decano',
            'programaciones.aprobar.jefe_postgrados','programaciones.rechazar.jefe_postgrados',
            'programaciones.aprobar.vicerrectoria','programaciones.rechazar.vicerrectoria',

            // ===== Creaciones =====
            'creaciones.view','creaciones.create','creaciones.edit','creaciones.delete',

            // Legacy por módulo
            'creaciones.aprobar.comite_acreditacion','creaciones.rechazar.comite_acreditacion',
            'creaciones.aprobar.consejo_facultad','creaciones.rechazar.consejo_facultad',
            'creaciones.aprobar.consejo_academico','creaciones.rechazar.consejo_academico',

            // ===== Fechas =====
            'fechas.view','fechas.create','fechas.edit','fechas.delete',

            // ===== Salarios =====
            'salarios.view','salarios.create','salarios.edit','salarios.delete',

            // ===== Rutas =====
            'rutas.view','rutas.create','rutas.edit','rutas.delete',

            // ===== Approvals =====
            'approvals.inbox','approvals.view','approvals.act',

            // ===== Approvals=====
            // Creaciones
            'approvals.aprobar.comite_acreditacion','approvals.rechazar.comite_acreditacion',
            'approvals.aprobar.consejo_facultad','approvals.rechazar.consejo_facultad',
            'approvals.aprobar.consejo_academico','approvals.rechazar.consejo_academico',

            // Programaciones
            'approvals.aprobar.departamento','approvals.rechazar.departamento',
            'approvals.aprobar.postgrados','approvals.rechazar.postgrados',
            'approvals.aprobar.decano','approvals.rechazar.decano',
            'approvals.aprobar.jefe_postgrados','approvals.rechazar.jefe_postgrados',
            'approvals.aprobar.vicerrectoria','approvals.rechazar.vicerrectoria',
        ];

        // Crear permisos
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Roles y sus permisos
        $roles = [
            'docente' => [
                'programaciones.view',
                'programaciones.create','programaciones.edit','programaciones.delete',

                'creaciones.view',
                'creaciones.create','creaciones.edit','creaciones.delete',

                'rutas.view','rutas.create','rutas.edit','rutas.delete',

                'catalogos.view',
            ],

            'jefe_departamento' => [
                'programaciones.view',
                'programaciones.aprobar.departamento','programaciones.rechazar.departamento',
                'approvals.aprobar.departamento','approvals.rechazar.departamento',

                'approvals.inbox','approvals.view','approvals.act',
                'rutas.view',
            ],

            'coordinador_postgrados' => [
                'programaciones.view',
                'programaciones.aprobar.postgrados','programaciones.rechazar.postgrados',
                'approvals.aprobar.postgrados','approvals.rechazar.postgrados',

                'approvals.inbox','approvals.view','approvals.act',
                'rutas.view',
            ],

            'decano' => [
                'programaciones.view',
                'programaciones.aprobar.decano','programaciones.rechazar.decano',
                'approvals.aprobar.decano','approvals.rechazar.decano',

                'approvals.inbox','approvals.view','approvals.act',
                'rutas.view',
            ],

            'jefe_postgrados' => [
                'programaciones.view',
                'programaciones.aprobar.jefe_postgrados','programaciones.rechazar.jefe_postgrados',
                'approvals.aprobar.jefe_postgrados','approvals.rechazar.jefe_postgrados',

                'approvals.inbox','approvals.view','approvals.act',
                'rutas.view',
            ],

            'vicerrectoria' => [
                'programaciones.view','fechas.view',
                'programaciones.aprobar.vicerrectoria','programaciones.rechazar.vicerrectoria',
                'approvals.aprobar.vicerrectoria','approvals.rechazar.vicerrectoria',

                'approvals.inbox','approvals.view','approvals.act',
                'fechas.create','fechas.edit','fechas.delete',
                'rutas.view',
            ],

            'comite_acreditacion' => [
                'creaciones.view',
                'creaciones.aprobar.comite_acreditacion','creaciones.rechazar.comite_acreditacion',
                'approvals.aprobar.comite_acreditacion','approvals.rechazar.comite_acreditacion',

                'approvals.inbox','approvals.view','approvals.act',
            ],

            'consejo_facultad' => [
                'creaciones.view',
                'creaciones.aprobar.consejo_facultad','creaciones.rechazar.consejo_facultad',
                'approvals.aprobar.consejo_facultad','approvals.rechazar.consejo_facultad',

                'approvals.inbox','approvals.view','approvals.act',
            ],

            'consejo_academico' => [
                'creaciones.view',
                'creaciones.aprobar.consejo_academico','creaciones.rechazar.consejo_academico',
                'approvals.aprobar.consejo_academico','approvals.rechazar.consejo_academico',

                'approvals.inbox','approvals.view','approvals.act',
            ],

            'administrador' => [
                'programaciones.view','creaciones.view','fechas.view','catalogos.view','salarios.view',
                'creaciones.create','creaciones.edit','creaciones.delete',
                'programaciones.create','programaciones.edit','programaciones.delete',
                'fechas.create','fechas.edit','fechas.delete',
                'catalogos.create','catalogos.edit','catalogos.delete',
                'salarios.create','salarios.edit','salarios.delete',

                'approvals.inbox','approvals.view','approvals.act',

                // Rutas
                'rutas.view','rutas.create','rutas.edit','rutas.delete',
            ],
        ];

        // Validación: que todos los permisos usados por roles existan en $perms
        $allRolePerms = collect($roles)->flatten()->unique()->values();
        $missing = $allRolePerms->diff($perms);
        if ($missing->isNotEmpty()) {
            throw new \RuntimeException('Faltan permisos en $perms: '.implode(', ', $missing->all()));
        }

        // Super admin: todos los permisos
        $super = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $super->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Crear/sincronizar roles
        foreach ($roles as $name => $permsOfRole) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions($permsOfRole);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}