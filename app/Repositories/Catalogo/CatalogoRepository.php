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
        return $this->query()->find($id);
    }

    public function create(array $data): Catalogo
    {
        return Catalogo::create($data);
    }

    public function update(string $id, array $data): ?Catalogo
    {
        $c = Catalogo::query()->find($id);
        if (!$c) return null;

        $c->update($data);
        return $this->find($id);
    }

    public function delete(string $id): bool
    {
        $c = Catalogo::query()->find($id);
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
        $fac = mb_strtolower(trim($facultad));
        $pro = mb_strtolower(trim($programaAcademico));

        $q = Catalogo::query()
            ->whereRaw('LOWER(facultad) = ?', [$fac])
            ->whereRaw('LOWER(programa_academico) = ?', [$pro]);

        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->exists();
    }

    public function upsertBulk(array $rows): void
    {
        $now = now();

        $rows = array_map(function ($r) use ($now) {
            unset($r['__key']);
            $r['updated_at'] = $r['updated_at'] ?? $now;
            $r['created_at'] = $r['created_at'] ?? $now;
            return $r;
        }, $rows);

        Catalogo::upsert(
            $rows,
            ['programa_academico', 'facultad'],
            ['nivel_academico', 'estado', 'updated_at']
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
        if (!empty($filters['nivel_academico'])) {
            $q->where('nivel_academico', $filters['nivel_academico']);
        }

        if (!empty($filters['facultad'])) {
            $q->where('facultad', 'like', '%'.$filters['facultad'].'%');
        }

        if (!empty($filters['programa_academico'])) {
            $q->where('programa_academico', 'like', '%'.$filters['programa_academico'].'%');
        }

        if (array_key_exists('estado', $filters) && $filters['estado'] !== null && $filters['estado'] !== '') {
            $estado = filter_var($filters['estado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($estado !== null) $q->where('estado', $estado);
        }

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

        // ---- SORT----
        $sort  = $filters['sort'] ?? 'facultad';
        $dir   = str_starts_with((string) $sort, '-') ? 'desc' : 'asc';
        $field = ltrim((string) $sort, '-');

        $sortable = [
            'id',
            'nivel_academico',
            'facultad',
            'programa_academico',
            'estado',
            'created_at',
            'updated_at',
        ];

        if (in_array($field, $sortable, true)) {
            $q->orderBy($field, $dir);
        } else {
            $q->orderBy('facultad', 'asc');
        }

        return $q;
    }
}