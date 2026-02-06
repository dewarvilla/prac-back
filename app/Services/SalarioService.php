<?php

namespace App\Services;

use App\Models\Salario;
use App\Models\User;
use App\Repositories\Salario\SalarioInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalarioService
{
    public function __construct(private readonly SalarioInterface $repo) {}

    public function search(array $filters = [], int $perPage = 0, array $appends = [])
    {
        return $perPage > 0
            ? $this->repo->paginate($filters, $perPage, $appends)
            : $this->repo->getAll($filters);
    }

    public function create(array $data, ?User $user, string $ip): Salario
    {
        $now = now();
        $uid = $user?->id ?? 0;

        $payload = $data + [
            'id'                  => (string) Str::uuid(),
            'estado'              => true,
            'fechacreacion'       => $now,
            'fechamodificacion'   => $now,
            'usuariocreacion'     => $uid,
            'usuariomodificacion' => $uid,
            'ipcreacion'          => $ip,
            'ipmodificacion'      => $ip,
        ];

        return $this->repo->create($payload)->fresh();
    }

    public function update(string $id, array $data, ?User $user, string $ip): ?Salario
    {
        $uid = $user?->id ?? 0;

        $payload = $data + [
            'fechamodificacion'   => now(),
            'usuariomodificacion' => $uid,
            'ipmodificacion'      => $ip,
        ];

        return $this->repo->update($id, $payload);
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
