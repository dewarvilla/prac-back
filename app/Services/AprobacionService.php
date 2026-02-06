<?php

namespace App\Services;

use App\Models\Aprobacion;
use App\Models\Creacion;
use App\Models\Programacion;
use App\Models\User;
use App\Notifications\ApprovalDecisionNotification;
use App\Notifications\ApprovalPendingNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AprobacionService
{
    /** Mapa etapa -> permiso (Spatie) */
    private array $permissionMap = [
        Creacion::class => [
            'comite_acreditacion' => 'creaciones.aprobar.comite_acreditacion',
            'consejo_facultad'    => 'creaciones.aprobar.consejo_facultad',
            'consejo_academico'   => 'creaciones.aprobar.consejo_academico',
        ],
        Programacion::class => [
            // depende del nivel; igual lo resolvemos abajo por etapa
            'departamento'    => 'programaciones.aprobar.departamento',
            'decano'          => 'programaciones.aprobar.decano',
            'postgrados'      => 'programaciones.aprobar.postgrados',
            'jefe_postgrados' => 'programaciones.aprobar.jefe_postgrados',
            'vicerrectoria'   => 'programaciones.aprobar.vicerrectoria',
        ],
    ];

    /** ========= API pública ========= */

    public function aprobar(Model $aprobable, User $actor, string $ip, ?string $justificacion = null): Model
    {
        return DB::transaction(function () use ($aprobable, $actor, $ip, $justificacion) {

            [$etapaActual, $nextEtapa] = $this->resolverFlujo($aprobable);

            $this->assertCanApprove($aprobable, $actor, $etapaActual);

            // 1) Guardar decisión
            $ap = $this->upsertDecision($aprobable, $etapaActual, 'aprobada', $actor, $ip, $justificacion);

            // 2) Avanzar el flujo del recurso
            $this->advanceModelState($aprobable, $etapaActual, $nextEtapa);

            // 3) Notificar al creador (aprobada)
            $this->notifyCreatorDecision($aprobable, $etapaActual, 'aprobada', $justificacion);

            // 4) Si hay siguiente etapa, notificar al/los aprobadores
            if ($nextEtapa) {
                $this->notifyNextApprovers($aprobable, $nextEtapa);
            }

            return $aprobable->refresh();
        });
    }

    public function rechazar(Model $aprobable, User $actor, string $ip, string $justificacion): Model
    {
        return DB::transaction(function () use ($aprobable, $actor, $ip, $justificacion) {

            [$etapaActual] = $this->resolverFlujo($aprobable);

            $this->assertCanApprove($aprobable, $actor, $etapaActual);

            // 1) Guardar decisión
            $this->upsertDecision($aprobable, $etapaActual, 'rechazada', $actor, $ip, $justificacion);

            // 2) Marcar recurso como rechazado (sin avanzar)
            $this->rejectModelState($aprobable, $etapaActual);

            // 3) Notificar creador (rechazada con justificación)
            $this->notifyCreatorDecision($aprobable, $etapaActual, 'rechazada', $justificacion);

            return $aprobable->refresh();
        });
    }

    /** ========= Núcleo ========= */

    private function upsertDecision(Model $aprobable, string $etapa, string $estado, User $actor, string $ip, ?string $justificacion): Aprobacion
    {
        return Aprobacion::query()->updateOrCreate(
            [
                'aprobable_type' => $aprobable::class,
                'aprobable_id'   => (string) $aprobable->getKey(),
                'etapa'          => $etapa,
            ],
            [
                'estado'        => $estado,
                'decidido_por'  => $actor->id,
                'decidido_en'   => now(),
                'justificacion' => $justificacion,
                'ip'            => $ip,
            ]
        );
    }

    private function assertCanApprove(Model $aprobable, User $actor, string $etapa): void
    {
        $perm = $this->permissionMap[$aprobable::class][$etapa] ?? null;

        abort_unless($perm && $actor->can($perm), 403, 'No tienes permiso para aprobar esta etapa.');
    }

    /**
     * @return array{0:string,1:?string} [etapaActual, nextEtapa]
     */
    private function resolverFlujo(Model $aprobable): array
    {
        if ($aprobable instanceof Creacion) {
            $actual = $aprobable->estado_flujo; // comite_acreditacion|consejo_facultad|consejo_academico
            $next = match ($actual) {
                'comite_acreditacion' => 'consejo_facultad',
                'consejo_facultad'    => 'consejo_academico',
                'consejo_academico'   => null,
                default               => null,
            };
            return [$actual, $next];
        }

        if ($aprobable instanceof Programacion) {
            // Depende del nivel académico del catálogo asociado a la creación
            $nivel = strtolower((string) optional(optional($aprobable->creacion)->catalogo)->nivel_academico);

            // Determinar etapa "pendiente" actual
            if ($nivel === 'pregrado') {
                $actual = $aprobable->estado_depart === 'pendiente' ? 'departamento'
                    : ($aprobable->estado_decano === 'pendiente' ? 'decano'
                    : ($aprobable->estado_vice === 'pendiente' ? 'vicerrectoria' : 'vicerrectoria'));
                $next = match ($actual) {
                    'departamento'  => 'decano',
                    'decano'        => 'vicerrectoria',
                    'vicerrectoria' => null,
                    default         => null,
                };
                return [$actual, $next];
            }

            // postgrado
            $actual = $aprobable->estado_postg === 'pendiente' ? 'postgrados'
                : ($aprobable->estado_jefe_postg === 'pendiente' ? 'jefe_postgrados'
                : ($aprobable->estado_vice === 'pendiente' ? 'vicerrectoria' : 'vicerrectoria'));

            $next = match ($actual) {
                'postgrados'      => 'jefe_postgrados',
                'jefe_postgrados' => 'vicerrectoria',
                'vicerrectoria'   => null,
                default           => null,
            };
            return [$actual, $next];
        }

        abort(500, 'Aprobable no soportado.');
    }

    private function advanceModelState(Model $aprobable, string $etapaActual, ?string $nextEtapa): void
    {
        if ($aprobable instanceof Creacion) {
            // Avanza el flujo y marca estado_creacion
            $aprobable->estado_creacion = 'en_aprobacion';

            if ($nextEtapa) {
                $aprobable->estado_flujo = $nextEtapa;
            } else {
                // última etapa aprobada
                $aprobable->estado_creacion = 'aprobada';
            }

            $aprobable->save();
            return;
        }

        if ($aprobable instanceof Programacion) {
            // Marcar aprobada la etapa actual
            match ($etapaActual) {
                'departamento'    => $aprobable->estado_depart = 'aprobada',
                'decano'          => $aprobable->estado_decano = 'aprobada',
                'postgrados'      => $aprobable->estado_postg = 'aprobada',
                'jefe_postgrados' => $aprobable->estado_jefe_postg = 'aprobada',
                'vicerrectoria'   => $aprobable->estado_vice = 'aprobada',
                default => null,
            };

            // Si ya no hay siguiente, queda aprobada la práctica
            if ($nextEtapa === null) {
                $aprobable->estado_practica = 'aprobada';
            } else {
                $aprobable->estado_practica = 'en_aprobacion';
            }

            $aprobable->save();
            return;
        }
    }

    private function rejectModelState(Model $aprobable, string $etapaActual): void
    {
        if ($aprobable instanceof Creacion) {
            $aprobable->estado_creacion = 'rechazada';
            // estado_flujo se queda en la etapa donde falló
            $aprobable->save();
            return;
        }

        if ($aprobable instanceof Programacion) {
            match ($etapaActual) {
                'departamento'    => $aprobable->estado_depart = 'rechazada',
                'decano'          => $aprobable->estado_decano = 'rechazada',
                'postgrados'      => $aprobable->estado_postg = 'rechazada',
                'jefe_postgrados' => $aprobable->estado_jefe_postg = 'rechazada',
                'vicerrectoria'   => $aprobable->estado_vice = 'rechazada',
                default => null,
            };

            $aprobable->estado_practica = 'rechazada';
            $aprobable->save();
            return;
        }
    }

    /** ========= Notificaciones ========= */

    private function notifyCreatorDecision(Model $aprobable, string $etapa, string $decision, ?string $justificacion): void
    {
        $creatorId = (int) ($aprobable->usuariocreacion ?? 0);
        if (!$creatorId) return;

        $creator = User::find($creatorId);
        if (!$creator) return;

        $titulo = $aprobable instanceof Creacion
            ? "Creación {$decision} (etapa: {$etapa})"
            : "Programación {$decision} (etapa: {$etapa})";

        $creator->notify(new ApprovalDecisionNotification(
            aprobableType: $aprobable::class,
            aprobableId: (string) $aprobable->getKey(),
            etapa: $etapa,
            decision: $decision,
            justificacion: $justificacion,
            titulo: $titulo,
            extra: []
        ));
    }

    private function notifyNextApprovers(Model $aprobable, string $nextEtapa): void
    {
        $perm = $this->permissionMap[$aprobable::class][$nextEtapa] ?? null;
        if (!$perm) return;

        $titulo = $aprobable instanceof Creacion
            ? "Creación pendiente de aprobación ({$nextEtapa})"
            : "Programación pendiente de aprobación ({$nextEtapa})";

        $users = User::permission($perm)->get();
        foreach ($users as $u) {
            $u->notify(new ApprovalPendingNotification(
                aprobableType: $aprobable::class,
                aprobableId: (string) $aprobable->getKey(),
                etapa: $nextEtapa,
                titulo: $titulo,
                extra: []
            ));
        }
    }
}
