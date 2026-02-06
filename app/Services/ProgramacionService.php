<?php

namespace App\Services;

use App\Models\Creacion;
use App\Models\Programacion;
use App\Models\User;
use App\Repositories\Programacion\ProgramacionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgramacionService
{
    public function __construct(
        private readonly ProgramacionInterface $repo,
    ) {}

    public function search(User $user, array $filters = [], int $perPage = 0, array $appends = [])
    {
        return $perPage > 0
            ? $this->repo->paginateVisible($user, $filters, $perPage, $appends)
            : $this->repo->getAllVisible($user, $filters);
    }

    public function create(array $data, ?User $user, string $ip): Programacion
    {
        $now = now();
        $uid = $user?->id ?? 0;

        $creacion = Creacion::query()->findOrFail($data['creacion_id']);

        $payload = $data + [
            'id'                  => (string) Str::uuid(),
            'nombre_practica'     => $creacion->nombre_practica,

            'estado_practica'     => 'en_aprobacion',
            'estado_depart'       => 'pendiente',
            'estado_postg'        => 'pendiente',
            'estado_decano'       => 'pendiente',
            'estado_jefe_postg'   => 'pendiente',
            'estado_vice'         => 'pendiente',

            'fechacreacion'       => $now,
            'fechamodificacion'   => $now,
            'usuariocreacion'     => $uid,
            'usuariomodificacion' => $uid,
            'ipcreacion'          => $ip,
            'ipmodificacion'      => $ip,
        ];

        return DB::transaction(function () use ($payload) {
            $programacion = $this->repo->create($payload)->fresh();
            $this->firstNotifier->notifyFirstApprover($programacion);
            return $programacion;
        });
    }

    public function update(Programacion $programacion, array $data, ?User $user, string $ip): Programacion
    {
        $uid = $user?->id ?? 0;

        $wasRejected      = $programacion->estado_practica === 'rechazada';
        $esDocenteCreador = $user && $user->id === $programacion->usuariocreacion;
        $esAdmin          = $user && ($user->hasRole('admin') || $user->hasRole('administrador') || $user->hasRole('super_admin'));

        $payload = $data + [
            'fechamodificacion'   => now(),
            'usuariomodificacion' => $uid,
            'ipmodificacion'      => $ip,
        ];

        // Si cambian creacion_id, refrescar nombre_practica
        if (array_key_exists('creacion_id', $payload)) {
            $creacion = Creacion::query()->findOrFail($payload['creacion_id']);
            $payload['nombre_practica'] = $creacion->nombre_practica;
        }

        return DB::transaction(function () use ($programacion, $payload, $wasRejected, $esDocenteCreador, $esAdmin) {

            if ($wasRejected && ($esDocenteCreador || $esAdmin)) {
                $payload['estado_practica']   = 'en_aprobacion';
                $payload['estado_depart']     = 'pendiente';
                $payload['estado_postg']      = 'pendiente';
                $payload['estado_decano']     = 'pendiente';
                $payload['estado_jefe_postg'] = 'pendiente';
                $payload['estado_vice']       = 'pendiente';

                $updated = $this->repo->update($programacion->id, $payload);
                abort_if(!$updated, 404);

                $fresh = $updated->fresh();
                $this->firstNotifier->notifyFirstApprover($fresh);
                return $fresh;
            }

            $updated = $this->repo->update($programacion->id, $payload);
            abort_if(!$updated, 404);
            return $updated;
        });
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
    }

    public function destroyBulk(array $ids): array
    {
        $ids = array_values(array_unique(array_map('strval', $ids)));

        return DB::transaction(function () use ($ids) {
            $deleted = $this->repo->deleteWhereIn($ids);

            return [
                'requested' => count($ids),
                'deleted'   => (int) $deleted,
            ];
        });
    }
}
