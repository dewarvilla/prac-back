<?php

namespace App\Repositories\Creacion;

use App\Models\Creacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CreacionRepository implements CreacionInterface
{
    public function query(): Builder
    {
        return Creacion::query()->with('catalogo');
    }

    public function find(string $id): ?Creacion
    {
        return $this->query()->find($id); // importante
    }

    public function create(array $data): Creacion
    {
        return Creacion::create($data);
    }

    public function update(string $id, array $data): ?Creacion
    {
        $c = $this->find($id);
        if (!$c) return null;

        $c->update($data);
        return $c->refresh(); // ojo: si quieres que refresh mantenga catalogo, puedes hacer ->load('catalogo')
    }

    public function delete(string $id): bool
    {
        $c = $this->find($id);
        if (!$c) return false;

        $c->delete();
        return true;
    }

    public function deleteWhereIn(array $ids): int
    {
        return Creacion::whereIn('id', $ids)->delete();
    }

    public function getAll(array $filters = []): Collection
    {
        return $this->applyFilters($this->query(), $filters)->get();
    }

    public function paginate(array $filters = [], int $perPage = 15, array $appends = []): LengthAwarePaginator
    {
        $q = $this->applyFilters($this->query(), $filters);
        return $q->paginate($perPage)->appends($appends);
    }

    public function existsNombreInCatalogo(string $catalogoId, string $nombre, ?string $ignoreId = null): bool
    {
        $q = Creacion::where('catalogo_id', $catalogoId)
            ->where('nombre_practica', $nombre);

        if ($ignoreId) $q->where('id', '!=', $ignoreId);

        return $q->exists();
    }

    private function applyFilters(Builder $q, array $filters): Builder
    {
        if (!empty($filters['catalogo_id'])) {
            $q->where('catalogo_id', (string) $filters['catalogo_id']);
        }

        if (!empty($filters['estado_creacion'])) {
            $q->where('estado_creacion', $filters['estado_creacion']);
        }

        if (!empty($filters['estado_flujo'])) {
            $q->where('estado_flujo', $filters['estado_flujo']);
        }

        if (!empty($filters['nombre_practica'])) {
            $q->where('nombre_practica', $filters['nombre_practica']);
        }

        if (!empty($filters['q'])) {
            $term = (string) $filters['q'];
            $driver = DB::connection()->getDriverName();
            $op = $driver === 'pgsql' ? 'ilike' : 'like';
            $like = '%' . addcslashes($term, "%_\\") . '%';

            $q->where(function (Builder $qq) use ($like, $op) {
                $qq->where('nombre_practica', $op, $like)
                   ->orWhereHas('catalogo', function ($qc) use ($like, $op) {
                       $qc->where('programa_academico', $op, $like)
                          ->orWhere('facultad', $op, $like);
                   });
            });
        }

        $sort  = $filters['sort'] ?? '-id';
        $dir   = str_starts_with((string) $sort, '-') ? 'desc' : 'asc';
        $field = ltrim((string) $sort, '-');

        $sortable = [
            'id',
            'catalogo_id',
            'nombre_practica',
            'estado_creacion',
            'estado_flujo',
            'fechacreacion',
            'fechamodificacion',
        ];

        if (in_array($field, $sortable, true)) {
            $q->orderBy($field, $dir);
        } else {
            $q->orderBy('id', 'desc');
        }

        return $q;
    }
}
