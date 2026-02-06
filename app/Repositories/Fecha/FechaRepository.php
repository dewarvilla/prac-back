<?php

namespace App\Repositories\Fecha;

use App\Models\Fecha;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FechaRepository implements FechaInterface
{
    public function query(): Builder
    {
        return Fecha::query();
    }

    public function find(string $id): ?Fecha
    {
        return $this->query()->find($id);
    }

    public function create(array $data): Fecha
    {
        return Fecha::create($data);
    }

    public function update(string $id, array $data): ?Fecha
    {
        $f = $this->find($id);
        if (!$f) return null;

        $f->update($data);
        return $f->refresh();
    }

    public function delete(string $id): bool
    {
        $f = $this->find($id);
        if (!$f) return false;

        $f->delete();
        return true;
    }

    public function deleteWhereIn(array $ids): int
    {
        return Fecha::whereIn('id', $ids)->delete();
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

    public function existsPeriodo(string $periodo, ?string $ignoreId = null): bool
    {
        $q = Fecha::query()->where('periodo', $periodo);
        if ($ignoreId) $q->where('id', '!=', $ignoreId);
        return $q->exists();
    }

    private function applyFilters(Builder $q, array $filters): Builder
    {
        if (!empty($filters['periodo'])) {
            $q->where('periodo', $filters['periodo']);
        }

        if (!empty($filters['periodo.lk'])) {
            $q->where('periodo', 'like', '%'.$filters['periodo.lk'].'%');
        }

        // búsqueda libre
        if (!empty($filters['q'])) {
            $term   = (string) $filters['q'];
            $driver = DB::connection()->getDriverName();
            $op     = $driver === 'pgsql' ? 'ilike' : 'like';
            $like   = '%'.addcslashes($term, "%_\\").'%';

            $q->where(function (Builder $qq) use ($like, $op) {
                $qq->where('periodo', $op, $like)
                   ->orWhere('fecha_apertura_preg', $op, $like)
                   ->orWhere('fecha_cierre_docente_preg', $op, $like)
                   ->orWhere('fecha_apertura_postg', $op, $like)
                   ->orWhere('fecha_cierre_docente_postg', $op, $like);
            });
        }

        // sort multi (como catalogo)
        $sort = $filters['sort'] ?? '-fechacreacion';
        $allowed = [
            'id','periodo',
            'fecha_apertura_preg','fecha_cierre_docente_preg',
            'fecha_apertura_postg','fecha_cierre_docente_postg',
            'fechacreacion','fechamodificacion',
        ];

        foreach (explode(',', (string)$sort) as $part) {
            $part = trim($part);
            if ($part === '') continue;

            $dir = str_starts_with($part, '-') ? 'desc' : 'asc';
            $col = ltrim($part, '-');

            if (!in_array($col, $allowed, true)) continue;
            $q->orderBy($col, $dir);
        }

        return $q;
    }
}
