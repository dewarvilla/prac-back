<?php

namespace App\Repositories\Salario;

use App\Models\Salario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalarioRepository implements SalarioInterface
{
    public function query(): Builder
    {
        return Salario::query();
    }

    public function find(string $id): ?Salario
    {
        return $this->query()->find($id);
    }

    public function create(array $data): Salario
    {
        return Salario::create($data);
    }

    public function update(string $id, array $data): ?Salario
    {
        $s = $this->find($id);
        if (!$s) return null;

        $s->update($data);
        return $s->refresh();
    }

    public function delete(string $id): bool
    {
        $s = $this->find($id);
        if (!$s) return false;

        $s->delete();
        return true;
    }

    public function deleteWhereIn(array $ids): int
    {
        return Salario::whereIn('id', $ids)->delete();
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

    public function existsAnio(int $anio, ?string $ignoreId = null): bool
    {
        $q = Salario::query()->where('anio', $anio);
        if ($ignoreId) $q->where('id', '!=', $ignoreId);
        return $q->exists();
    }

    private function applyFilters(Builder $q, array $filters): Builder
    {
        // q libre
        if (!empty($filters['q'])) {
            $term   = (string) $filters['q'];
            $driver = DB::connection()->getDriverName();
            $op     = $driver === 'pgsql' ? 'ilike' : 'like';
            $like   = '%'.addcslashes($term, "%_\\").'%';

            $q->where(function (Builder $qq) use ($like, $op, $term) {
                $qq->where('anio', $op, $like)
                   ->orWhere('valor', $op, $like)
                   ->orWhere('id', $op, $like);

                // búsqueda exacta por año si vienen números
                if (ctype_digit($term)) {
                    $qq->orWhere('anio', (int) $term);
                }
            });
        }

        if (!empty($filters['anio'])) {
            $q->where('anio', (int) $filters['anio']);
        }

        if (!empty($filters['valor'])) {
            $q->where('valor', $filters['valor']);
        }

        // sort multi
        $sort = $filters['sort'] ?? '-anio';
        $allowed = ['id','anio','valor','fechacreacion','fechamodificacion'];

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
