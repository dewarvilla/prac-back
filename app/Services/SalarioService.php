<?php

namespace App\Services;

use App\Exceptions\Salarios\SalarioAnioDuplicateException;
use App\Models\Salario;
use App\Models\User;
use App\Repositories\Salario\SalarioInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data, $user, $ip) {

            $anio = (int) $data['anio'];

            if ($this->repo->existsAnio($anio)) {
                throw new SalarioAnioDuplicateException($anio);
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
                // carrera: otro request creó el mismo año en paralelo
                if ($this->isUniqueAnioViolation($e)) {
                    throw new SalarioAnioDuplicateException($anio);
                }
                throw $e;
            }
        });
    }

    public function update(string $id, array $data, ?User $user, string $ip): ?Salario
    {
        return DB::transaction(function () use ($id, $data, $user, $ip) {

            if (!$this->repo->find($id)) return null;

            if (array_key_exists('anio', $data)) {
                $anio = (int) $data['anio'];

                if ($this->repo->existsAnio($anio, $id)) {
                    throw new SalarioAnioDuplicateException($anio, $id);
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
                if (array_key_exists('anio', $data) && $this->isUniqueAnioViolation($e)) {
                    throw new SalarioAnioDuplicateException((int)$data['anio'], $id);
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

    private function isUniqueAnioViolation(QueryException $e): bool
    {
        $msg = strtolower((string) $e->getMessage());

        // nombre típico del constraint en Laravel: salarios_anio_unique
        if (str_contains($msg, 'salarios_anio_unique')) return true;

        // fallback genérico
        if (str_contains($msg, 'duplicate entry') && str_contains($msg, 'anio')) return true;

        return false;
    }
}
