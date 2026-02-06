<?php

namespace App\Repositories\Programacion;

use App\Models\Programacion;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProgramacionRepository implements ProgramacionInterface
{
    public function baseVisibleQuery(User $user): Builder
    {
        return Programacion::visibleFor($user);
    }

    public function findVisible(string $id, User $user): ?Programacion
    {
        return $this->baseVisibleQuery($user)->where('id', $id)->first();
    }

    public function create(array $data): Programacion
    {
        return Programacion::create($data);
    }

    public function update(string $id, array $data): ?Programacion
    {
        $p = Programacion::query()->find($id);
        if (!$p) return null;

        $p->update($data);
        return $p->refresh();
    }

    public function delete(string $id): bool
    {
        $p = Programacion::query()->find($id);
        if (!$p) return false;

        $p->delete();
        return true;
    }

    public function deleteWhereIn(array $ids): int
    {
        return Programacion::whereIn('id', $ids)->delete();
    }

    public function getAllVisible(User $user, array $filters = []): Collection
    {
        return $this->applyFilters($this->baseVisibleQuery($user), $filters)->get();
    }

    public function paginateVisible(User $user, array $filters = [], int $perPage = 15, array $appends = []): LengthAwarePaginator
    {
        $q = $this->applyFilters($this->baseVisibleQuery($user), $filters);
        return $q->paginate($perPage)->appends($appends);
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
                $qq->where('nombre_practica', $op, $like)
                  ->orWhere('lugar_de_realizacion', $op, $like)
                  ->orWhere('estado_practica', $op, $like)
                  ->orWhere('estado_depart', $op, $like)
                  ->orWhere('estado_postg', $op, $like)
                  ->orWhere('estado_decano', $op, $like)
                  ->orWhere('estado_jefe_postg', $op, $like)
                  ->orWhere('estado_vice', $op, $like)
                  ->orWhere('fecha_inicio', $op, $like)
                  ->orWhere('fecha_finalizacion', $op, $like)
                  ->orWhere('numero_estudiantes', $op, $like)
                  ->orWhere('id', $op, $like);

                $low = mb_strtolower($term);
                if (in_array($low, ['si','sí','true','1','no','false','0'], true)) {
                    $val = in_array($low, ['si','sí','true','1'], true) ? 1 : 0;
                    $qq->orWhere('requiere_transporte', $val);
                }
            });
        }

        // filtros específicos
        if (!empty($filters['nombre_practica'])) {
            $q->where('nombre_practica', 'like', '%'.$filters['nombre_practica'].'%');
        }

        if (!empty($filters['creacion_id'])) {
            $q->where('creacion_id', (string) $filters['creacion_id']);
        }

        if (array_key_exists('requiere_transporte', $filters) && $filters['requiere_transporte'] !== null) {
            $q->where('requiere_transporte', (bool) $filters['requiere_transporte']);
        }

        if (!empty($filters['estado_practica'])) {
            $q->where('estado_practica', $filters['estado_practica']);
        }

        if (!empty($filters['fecha_inicio'])) {
            $q->whereDate('fecha_inicio', '>=', $filters['fecha_inicio']);
        }

        if (!empty($filters['fecha_finalizacion'])) {
            $q->whereDate('fecha_finalizacion', '<=', $filters['fecha_finalizacion']);
        }

        // sort multi (whitelist)
        $sort = $filters['sort'] ?? '-fechacreacion';
        $allowed = [
            'id','nombre_practica','fecha_inicio','fecha_finalizacion','estado_practica','fechacreacion','fechamodificacion'
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
