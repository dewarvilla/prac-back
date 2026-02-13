<?php

namespace App\Services;

use App\Exceptions\Creaciones\CreacionDuplicateInCatalogoException;
use App\Models\Catalogo;
use App\Models\Creacion;
use App\Repositories\Creacion\CreacionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreacionService
{
    public function __construct(private readonly CreacionInterface $repo)
    {
    }

    public function search(array $filters, int $perPage = 0, array $appends = [])
    {
        return $perPage > 0
            ? $this->repo->paginate($filters, $perPage, $appends)
            : $this->repo->getAll($filters);
    }

    public function create(array $data, $user, string $ip): Creacion
    {
        return DB::transaction(function () use ($data, $user, $ip) {

            $catId  = (string) $data['catalogo_id'];
            $nombre = (string) $data['nombre_practica'];

            $cat = Catalogo::findOrFail($catId);

            if ($this->repo->existsNombreInCatalogo((string)$cat->id, $nombre)) {
                throw new CreacionDuplicateInCatalogoException((string)$cat->id, $nombre);
            }

            $now = now();

            $payload = $data + [
                'estado_creacion' => 'en_aprobacion',

                // auditoría
                'fechacreacion'       => $now,
                'fechamodificacion'   => $now,
                'usuariocreacion'     => $user?->id ?? 0,
                'usuariomodificacion' => $user?->id ?? 0,
                'ipcreacion'          => $ip,
                'ipmodificacion'      => $ip,
            ];

            try {
                return $this->repo->create($payload)->fresh();
            } catch (QueryException $e) {
                if (str_contains(strtolower($e->getMessage()), 'creaciones_catalogo_nombre_unique')) {
                    throw new CreacionDuplicateInCatalogoException((string)$cat->id, $nombre);
                }
                throw $e;
            }
        });
    }

    public function update(string $id, array $data, $user, string $ip): ?Creacion
    {
        return DB::transaction(function () use ($id, $data, $user, $ip) {

            $current = $this->repo->find($id);
            if (! $current) return null;

            $catalogoId = (string) ($data['catalogo_id'] ?? $current->catalogo_id);
            $nombre     = (string) ($data['nombre_practica'] ?? $current->nombre_practica);

            if (isset($data['catalogo_id']) || isset($data['nombre_practica'])) {
                if ($this->repo->existsNombreInCatalogo($catalogoId, $nombre, (string)$current->id)) {
                    throw new CreacionDuplicateInCatalogoException($catalogoId, $nombre, (string)$current->id);
                }
            }

            $payload = $data + [
                'fechamodificacion'   => now(),
                'usuariomodificacion' => $user?->id ?? 0,
                'ipmodificacion'      => $ip,
            ];

            try {
                return $this->repo->update($id, $payload);
            } catch (QueryException $e) {
                if (str_contains(strtolower($e->getMessage()), 'creaciones_catalogo_nombre_unique')) {
                    throw new CreacionDuplicateInCatalogoException($catalogoId, $nombre, (string)$current->id);
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
}
