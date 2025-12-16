<?php

namespace App\Repositories\Catalogo;

use App\Models\Catalogo;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CatalogoRepository extends BaseRepository implements CatalogoInterface
{
    public function __construct(Catalogo $model)
    {
        parent::__construct($model);
    }

    /** @return LengthAwarePaginator|Collection */
    public function search(array $filters = [], int $perPage = 0)
    {
        $q = $this->query();
        // Búsqueda texto libre "q"
        if (!empty($filters['q'])) {
            $term = (string) $filters['q'];
            $op   = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $like = '%'.addcslashes($term, "%_\\").'%';

            $q->where(function ($qq) use ($like, $term, $op) {
                $qq->where('facultad', $op, $like)
                   ->orWhere('programa_academico', $op, $like)
                   ->orWhere('nivel_academico', $op, $like);

                if (ctype_digit($term)) {
                    $qq->orWhere('id', (int) $term);
                }
            });
        }

        // Filtros específicos
        if (!empty($filters['nivel_academico'])) {
            $q->where('nivel_academico', $filters['nivel_academico']);
        }

        if (!empty($filters['facultad'])) {
            $q->where('facultad', 'like', '%'.$filters['facultad'].'%');
        }

        if (!empty($filters['programa_academico'])) {
            $q->where('programa_academico', 'like', '%'.$filters['programa_academico'].'%');
        }

        // Sort
        if (!empty($filters['sort'])) {
            foreach (explode(',', $filters['sort']) as $part) {
                $part = trim($part);
                $dir  = str_starts_with($part, '-') ? 'desc' : 'asc';
                $col  = ltrim($part, '-');
                $q->orderBy($col, $dir);
            }
        } else {
            $q->orderBy('facultad')->orderBy('programa_academico');
        }

        if ($perPage > 0) {
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function upsertBulk(array $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach (collect($rows)->chunk(500) as $slice) {
                Catalogo::upsert(
                    $slice->map(fn($r) => collect($r)->except('__key')->all())->all(),
                    ['programa_academico', 'facultad'],
                    ['nivel_academico', 'fechamodificacion', 'usuariomodificacion', 'ipmodificacion']
                );
            }
        });
    }

    public function findByPairs(array $rows)
    {
        $q = Catalogo::query();

        foreach ($rows as $r) {
            $q->orWhere(function ($qq) use ($r) {
                $qq->where('facultad', $r['facultad'])
                   ->where('programa_academico', $r['programa_academico']);
            });
        }

        return $q->orderBy('facultad')
                 ->orderBy('programa_academico')
                 ->get();
    }

    public function deleteByIds(array $ids): int
    {
        return Catalogo::whereIn('id', $ids)->delete();
    }
}
