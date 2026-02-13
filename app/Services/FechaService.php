<?php

namespace App\Services;

use App\Exceptions\Fechas\FechaPeriodoDuplicateException;
use App\Models\Fecha;
use App\Models\User;
use App\Repositories\Fecha\FechaInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class FechaService
{
    public function __construct(private readonly FechaInterface $repo) {}

    public function search(array $filters = [], int $perPage = 0, array $appends = [])
    {
        return $perPage > 0
            ? $this->repo->paginate($filters, $perPage, $appends)
            : $this->repo->getAll($filters);
    }

    public function create(array $data, ?User $user, string $ip): Fecha
    {
        return DB::transaction(function () use ($data, $user, $ip) {

            $periodo = (string) $data['periodo'];

            if ($this->repo->existsPeriodo($periodo)) {
                throw new FechaPeriodoDuplicateException($periodo);
            }

            $now = now();
            $uid = $user?->id ?? 0;

            $payload = $data + [
                'estado'              => true,

                'fechacreacion'       => $now,
                'fechamodificacion'   => $now,
                'usuariocreacion'     => $uid,
                'usuariomodificacion' => $uid,
                'ipcreacion'          => $ip,
                'ipmodificacion'      => $ip,
            ];

            try {
                return $this->repo->create($payload)->fresh();
            } catch (QueryException $e) {
                if ($this->isUniquePeriodoViolation($e)) {
                    throw new FechaPeriodoDuplicateException($periodo);
                }
                throw $e;
            }
        });
    }

    public function update(string $id, array $data, ?User $user, string $ip): ?Fecha
    {
        return DB::transaction(function () use ($id, $data, $user, $ip) {

            if (!$this->repo->find($id)) return null;

            if (array_key_exists('periodo', $data)) {
                $periodo = (string) $data['periodo'];

                if ($this->repo->existsPeriodo($periodo, $id)) {
                    throw new FechaPeriodoDuplicateException($periodo, $id);
                }
            }

            $uid = $user?->id ?? 0;

            $payload = $data + [
                'fechamodificacion'   => now(),
                'usuariomodificacion' => $uid,
                'ipmodificacion'      => $ip,
            ];

            try {
                return $this->repo->update($id, $payload);
            } catch (QueryException $e) {
                if (array_key_exists('periodo', $data) && $this->isUniquePeriodoViolation($e)) {
                    throw new FechaPeriodoDuplicateException((string)$data['periodo'], $id);
                }
                throw $e;
            }
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

    private function isUniquePeriodoViolation(QueryException $e): bool
    {
        $msg = strtolower((string) $e->getMessage());

        if (str_contains($msg, 'fechas_periodo_unique')) return true;

        if (str_contains($msg, 'duplicate entry') && str_contains($msg, 'periodo')) return true;

        return false;
    }
}
