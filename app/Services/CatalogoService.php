<?php

namespace App\Services;

use App\Repositories\Catalogo\CatalogoInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogoService
{
    public function __construct(private readonly CatalogoInterface $repo) {}

    public function search(array $filters = [], int $perPage = 0, array $appends = [])
    {
        return $perPage > 0
            ? $this->repo->paginate($filters, $perPage, $appends ?: $filters)
            : $this->repo->getAll($filters);
    }

    public function create(array $data)
    {
        return $this->repo->create($data + ['estado' => true])->fresh();
    }

    public function update(string $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
    }

    /**
     * @return array{rows:\Illuminate\Database\Eloquent\Collection, meta:array}
     */
    public function storeBulk(array $items): array
    {
        $now = now();
        $normalize = fn(?string $s) => $s === null ? null : preg_replace('/\s+/u', ' ', trim($s));

        $rows = collect($items)->map(function ($i) use ($now, $normalize) {
            $fac = $normalize($i['facultad'] ?? '');
            $pro = $normalize($i['programa_academico'] ?? '');

            return [
                'id'                 => (string) Str::uuid(),
                'nivel_academico'    => $i['nivel_academico'],
                'facultad'           => $fac,
                'programa_academico' => $pro,
                'estado'             => true,

                'created_at' => $now,
                'updated_at' => $now,

                '__key' => mb_strtolower($fac).'|'.mb_strtolower($pro),
            ];
        });

        $this->repo->upsertBulk($rows->all());
        $affected = $this->repo->findByPairs($rows->all());

        return [
            'rows' => $affected,
            'meta' => [
                'processed' => $rows->count(),
                'timestamp' => $now->toIso8601String(),
            ],
        ];
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