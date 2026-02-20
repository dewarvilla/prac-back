<?php

namespace App\Services;

use App\Exceptions\Creaciones\CreacionDuplicateInCatalogoException;
use App\Exceptions\Creaciones\CreacionNotEditableException;
use App\Models\Catalogo;
use App\Models\Creacion;
use App\Models\ApprovalDefinition;
use App\Models\ApprovalDefinitionStep;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Repositories\Creacion\CreacionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreacionService
{
    public function __construct(private readonly CreacionInterface $repo) {}

    public function search(array $filters, int $perPage = 0, array $appends = [])
    {
        return $perPage > 0
            ? $this->repo->paginate($filters, $perPage, $appends)
            : $this->repo->getAll($filters);
    }

    public function create(array $data): Creacion
    {
        return DB::transaction(function () use ($data) {

            $catId  = (string) $data['catalogo_id'];
            $nombre = (string) $data['nombre_practica'];

            $cat = Catalogo::findOrFail($catId);

            if ($this->repo->existsNombreInCatalogo((string) $cat->id, $nombre)) {
                throw new CreacionDuplicateInCatalogoException((string) $cat->id, $nombre);
            }

            $payload = $data + [
                'estado_creacion' => 'en_aprobacion',
            ];

            try {
                $creacion = $this->repo->create($payload)->fresh();

                // ---- Crear flujo de aprobación ----
                $def = ApprovalDefinition::query()
                    ->where('code', 'CREACION_PRACTICA')
                    ->where('is_active', true)
                    ->firstOrFail();

                $already = ApprovalRequest::query()
                    ->where('approvable_type', Creacion::class)
                    ->where('approvable_id', (string) $creacion->id)
                    ->where('status', 'pending')
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->exists();

                if (!$already) {
                    $ar = ApprovalRequest::create([
                        'approvable_type'        => Creacion::class,
                        'approvable_id'          => (string) $creacion->id,
                        'approval_definition_id' => (string) $def->id,
                        'status'                 => 'pending',
                        'current_step_order'     => 1,
                        'is_current'             => true,
                        'requested_by'           => Auth::id(),
                    ]);

                    $defSteps = ApprovalDefinitionStep::query()
                        ->where('approval_definition_id', $def->id)
                        ->orderBy('step_order')
                        ->get();

                    foreach ($defSteps as $s) {
                        ApprovalStep::create([
                            'approval_request_id' => (string) $ar->id,
                            'step_order'          => (int) $s->step_order,
                            'role_key'            => (string) $s->role_key,
                            'status'              => 'pending',
                        ]);
                    }
                }

                return $creacion->load([
                    'catalogo',
                    'currentApprovalRequest.definition',
                    'currentApprovalRequest.steps',
                ]);

            } catch (QueryException $e) {
                if ($this->isCreacionUniqueViolation($e)) {
                    throw new CreacionDuplicateInCatalogoException((string) $cat->id, $nombre);
                }
                throw $e;
            }
        });
    }

    public function update(string $id, array $data): ?Creacion
    {
        return DB::transaction(function () use ($id, $data) {

            $current = $this->repo->find($id);
            if (!$current) return null;

            if (!in_array($current->estado_creacion, ['rechazada'], true)) {
                throw new CreacionNotEditableException($current->estado_creacion);
            }

            $catalogoId = (string) ($data['catalogo_id'] ?? $current->catalogo_id);
            $nombre     = (string) ($data['nombre_practica'] ?? $current->nombre_practica);

            if (array_key_exists('catalogo_id', $data) || array_key_exists('nombre_practica', $data)) {
                if ($this->repo->existsNombreInCatalogo($catalogoId, $nombre, (string) $current->id)) {
                    throw new CreacionDuplicateInCatalogoException($catalogoId, $nombre, (string) $current->id);
                }
            }

            try {
                $updated = $this->repo->update($id, $data);

                return $updated?->load([
                    'catalogo',
                    'currentApprovalRequest.definition',
                    'currentApprovalRequest.steps',
                ]);

            } catch (QueryException $e) {
                if ($this->isCreacionUniqueViolation($e)) {
                    throw new CreacionDuplicateInCatalogoException($catalogoId, $nombre, (string) $current->id);
                }
                throw $e;
            }
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $c = $this->repo->find($id);
            if (!$c) return false;

            if (!in_array($c->estado_creacion, ['rechazada'], true)) {
                throw new CreacionNotEditableException($c->estado_creacion);
            }

            return $this->repo->delete($id);
        });
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

    private function isCreacionUniqueViolation(QueryException $e): bool
    {
        $msg = strtolower($e->getMessage());
        if (str_contains($msg, 'creaciones_catalogo_nombre_unique')) return true;

        $sqlState   = $e->errorInfo[0] ?? null; // pgsql: 23505
        $driverCode = $e->errorInfo[1] ?? null; // mysql: 1062

        return $sqlState === '23505' || $driverCode === 1062;
    }
}