<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\Catalogo;
use App\Models\Creacion;
use App\Repositories\Creacion\CreacionInterface;
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

            $cat = Catalogo::findOrFail($data['catalogo_id']);

            if ($this->repo->existsNombreInCatalogo((string)$cat->id, (string)$data['nombre_practica'])) {
                throw new ConflictException('Ya existe una creación con ese nombre en el catálogo indicado.');
            }

            $now = now();

            $payload = $data + [
                'facultad'           => $cat->facultad,
                'programa_academico' => $cat->programa_academico,
                'nivel_academico'    => $cat->nivel_academico ?? null,

                'estado_creacion' => 'en_aprobacion',
                'estado_flujo'    => 'comite_acreditacion',

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
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains(strtolower($e->getMessage()), 'creaciones_catalogo_nombre_unique')) {
                    throw new ConflictException('Ya existe una creación con ese nombre en el catálogo indicado.');
                }
                throw $e;
            }
        });
    }

    public function update(string $id, array $data, $user, string $ip): ?Creacion
    {
        return DB::transaction(function () use ($id, $data, $user, $ip) {

            $current = $this->repo->find($id);
            if (!$current) return null;

            $catalogoId = (string) ($data['catalogo_id'] ?? $current->catalogo_id);
            $nombre     = (string) ($data['nombre_practica'] ?? $current->nombre_practica);

            if (isset($data['catalogo_id']) || isset($data['nombre_practica'])) {
                if ($this->repo->existsNombreInCatalogo($catalogoId, $nombre, $current->id)) {
                    throw new ConflictException('Ya existe otra creación con ese nombre en el catálogo indicado.');
                }
            }

            if (isset($data['catalogo_id'])) {
                $cat = Catalogo::findOrFail($data['catalogo_id']);
                $data['facultad']           = $cat->facultad;
                $data['programa_academico'] = $cat->programa_academico;
                $data['nivel_academico']    = $cat->nivel_academico ?? null;
            }

            $payload = $data + [
                'fechamodificacion'   => now(),
                'usuariomodificacion' => $user?->id ?? 0,
                'ipmodificacion'      => $ip,
            ];

            return $this->repo->update($id, $payload);
        });
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
    }

    public function destroyBulk(array $ids): array
    {
        // ⚠️ tu request convierte ids a int, pero tu ID es UUID (string)
        // Si tu tabla ya es UUID, cambia BulkDeleteCreacionRequest para NO castear a int.
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
