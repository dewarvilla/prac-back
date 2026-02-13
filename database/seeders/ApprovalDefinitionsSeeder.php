<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalDefinition;
use App\Models\ApprovalDefinitionStep;

class ApprovalDefinitionsSeeder extends Seeder
{
    public function run(): void
    {
        // ===== CREACIONES =====
        $this->upsertFlow(
            code: 'CREACION_PRACTICA',
            name: 'Aprobación de Creación de Práctica',
            steps: [
                ['order' => 1, 'role_key' => 'comite_acreditacion', 'requires_comment_on_reject' => true],
                ['order' => 2, 'role_key' => 'consejo_facultad',    'requires_comment_on_reject' => true],
                ['order' => 3, 'role_key' => 'consejo_academico',   'requires_comment_on_reject' => true],
            ]
        );

        // ===== PROGRAMACIONES (PREGRADO) =====
        $this->upsertFlow(
            code: 'PROGRAMACION_PREGRADO',
            name: 'Aprobación de Programación (Pregrado)',
            steps: [
                ['order' => 1, 'role_key' => 'departamento',  'requires_comment_on_reject' => true],
                ['order' => 2, 'role_key' => 'decano',        'requires_comment_on_reject' => true],
                ['order' => 3, 'role_key' => 'vicerrectoria', 'requires_comment_on_reject' => true],
            ]
        );

        // ===== PROGRAMACIONES (POSGRADO) =====
        $this->upsertFlow(
            code: 'PROGRAMACION_POSGRADO',
            name: 'Aprobación de Programación (Posgrado)',
            steps: [
                // OJO: role_key = "postgrados" porque tu permiso es programaciones.aprobar.postgrados
                ['order' => 1, 'role_key' => 'postgrados',      'requires_comment_on_reject' => true],
                ['order' => 2, 'role_key' => 'jefe_postgrados', 'requires_comment_on_reject' => true],
                ['order' => 3, 'role_key' => 'vicerrectoria',   'requires_comment_on_reject' => true],
            ]
        );
    }

    private function upsertFlow(string $code, string $name, array $steps): void
    {
        $def = ApprovalDefinition::updateOrCreate(
            ['code' => $code],
            ['name' => $name, 'is_active' => true]
        );

        ApprovalDefinitionStep::where('approval_definition_id', $def->id)->delete();

        foreach ($steps as $s) {
            ApprovalDefinitionStep::create([
                'approval_definition_id'      => $def->id,
                'step_order'                  => (int) $s['order'],
                'role_key'                    => $s['role_key'],
                'requires_comment_on_reject'  => (bool) $s['requires_comment_on_reject'],
                'sla_days'                    => $s['sla_days'] ?? null,
            ]);
        }
    }
}
