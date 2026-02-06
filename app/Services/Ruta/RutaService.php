<?php

namespace App\Services\Ruta;

use App\Models\Ruta;
use App\Repositories\Ruta\RutaRepository;
use Illuminate\Support\Facades\DB;

class RutaService
{
    public function __construct(private readonly RutaRepository $repo) {}

    public function search(array $filters, int $perPage = 0, array $query = [])
    {
        $q = $this->repo->query();

        return $perPage > 0
            ? $q->paginate($perPage)->appends($query)
            : $q->get();
    }

    public function create(array $data, $user, string $ip): Ruta
    {
        $now = now();

        $payload = $data + [
            'fechacreacion'       => $now,
            'fechamodificacion'   => $now,
            'usuariocreacion'     => $user?->id ?? 0,
            'usuariomodificacion' => $user?->id ?? 0,
            'ipcreacion'          => $ip,
            'ipmodificacion'      => $ip,
        ];

        return $this->repo->create($payload)->fresh();
    }

    public function update(int $id, array $data, $user, string $ip): ?Ruta
    {
        $payload = $data + [
            'fechamodificacion'   => now(),
            'usuariomodificacion' => $user?->id ?? 0,
            'ipmodificacion'      => $ip,
        ];

        return $this->repo->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }

    public function destroyBulk(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        return DB::transaction(function () use ($ids) {
            $deleted = $this->repo->deleteWhereIn($ids);

            return [
                'requested' => count($ids),
                'deleted'   => (int) $deleted,
            ];
        });
    }
}
