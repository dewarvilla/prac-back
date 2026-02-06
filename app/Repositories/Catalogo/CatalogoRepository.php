<?php

namespace App\Repositories\Catalogo;

use App\Models\Catalogo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CatalogoRepository implements CatalogoInterface
{
    public function query(): Builder
    {
        return Catalogo::query();
    }

    public function find(string $id): ?Catalogo
    {
        return Catalogo::find($id);
    }

    public function create(array $data): Catalogo
    {
        return Catalogo::create($data);
    }

    public function update(string $id, array $data): ?Catalogo
    {
        $c = $this->find($id);
        if (!$c) return null;

        $c->update($data);
        return $c->refresh();
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
        return Catalogo::whereIn('id', $ids)->delete();
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

    public function existsPair(string $facultad, string $programaAcademico, ?string $ignoreId = null): bool
    {
        $q = Catalogo::query()
            ->where('facultad', $facultad)
            ->where('programa_academico', $programaAcademico);

        if ($ignoreId) $q->where('id', '!=', $ignoreId);

        return $q->exists();
    }

    public function upsertBulk(array $rows): void
    {
        $rows = array_map(function ($r) {
            unset($r['__key']);
            return $r;
        }, $rows);

        Catalogo::upsert(
            $rows,
            ['programa_academico', 'facultad'],
            [
                'nivel_academico',
                'estado',
                'fechamodificacion',
                'usuariomodificacion',
                'ipmodificacion',
            ]
        );
    }

    public function findByPairs(array $rows): Collection
    {
        $q = Catalogo::query();

        foreach ($rows as $r) {
            $q->orWhere(function ($qq) use ($r) {
                $qq->where('facultad', $r['facultad'])
                   ->where('programa_academico', $r['programa_academico']);
            });
        }

        return $q->orderBy('facultad')->orderBy('programa_academico')->get();
    }

    private function applyFilters(Builder $q, array $filters): Builder
    {
        // q libre
        if (!empty($filters['q'])) {
            $term = (string) $filters['q'];
            $driver = DB::connection()->getDriverName();
            $op = $driver === 'pgsql' ? 'ilike' : 'like';
            $like = '%'.addcslashes($term, "%_\\").'%';

            $q->where(function (Builder $qq) use ($like, $op) {
                $qq->where('facultad', $op, $like)
                   ->orWhere('programa_academico', $op, $like)
                   ->orWhere('nivel_academico', $op, $like);
            });
        }

        if (!empty($filters['nivel_academico'])) {
            $q->where('nivel_academico', $filters['nivel_academico']);
        }

        if (!empty($filters['facultad'])) {
            $q->where('facultad', 'like', '%'.$filters['facultad'].'%');
        }

        if (!empty($filters['programa_academico'])) {
            $q->where('programa_academico', 'like', '%'.$filters['programa_academico'].'%');
        }

        // lk (si tú lo estás usando como “like” explícito)
        if (!empty($filters['facultad.lk'])) {
            $q->where('facultad', 'like', '%'.$filters['facultad.lk'].'%');
        }
        if (!empty($filters['programa_academico.lk'])) {
            $q->where('programa_academico', 'like', '%'.$filters['programa_academico.lk'].'%');
        }
        if (!empty($filters['nivel_academico.lk'])) {
            $q->where('nivel_academico', 'like', '%'.$filters['nivel_academico.lk'].'%');
        }

        // sort normalizado
        $sort = $filters['sort'] ?? 'facultad,programa_academico';
        foreach (explode(',', (string)$sort) as $part) {
            $part = trim($part);
            if ($part === '') continue;

            $dir = str_starts_with($part, '-') ? 'desc' : 'asc';
            $col = ltrim($part, '-');

            // whitelist simple para evitar orderBy de columnas raras
            $allowed = ['id','facultad','programa_academico','nivel_academico','estado'];
            if (!in_array($col, $allowed, true)) continue;

            $q->orderBy($col, $dir);
        }

        return $q;
    }
}
