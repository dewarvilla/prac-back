<?php

namespace App\Services;

use App\Repositories\Catalogo\CatalogoInterface;
use App\Models\Catalogo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CatalogoService
{
    public function __construct(private CatalogoInterface $repo)
    {
    }

    /** @return LengthAwarePaginator|Collection */
    public function search(array $filters = [], int $perPage = 0)
    {
        return $this->repo->search($filters, $perPage);
    }

    public function create(array $data, ?User $user, string $ip): Catalogo
    {
        $now = now();
        $uid = $user?->id ?? 0;

        $payload = $data + [
            'fechacreacion'       => $now,
            'fechamodificacion'   => $now,
            'usuariocreacion'     => $uid,
            'usuariomodificacion' => $uid,
            'ipcreacion'          => $ip,
            'ipmodificacion'      => $ip,
        ];

        return $this->repo->create($payload);
    }

    public function update(string $id, array $data, ?User $user, string $ip): ?Catalogo
    {
        $now = now();
        $uid = $user?->id ?? 0;

        $payload = $data + [
            'fechamodificacion'   => $now,
            'usuariomodificacion' => $uid,
            'ipmodificacion'      => $ip,
        ];

        return $this->repo->update($id, $payload);
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
    }

    /**
     * @return array{rows: \Illuminate\Support\Collection, meta: array}
     */
    public function storeBulk(array $items, ?User $user, string $ip): array
    {
        $now = now();
        $uid = $user?->id ?? 0;

        $normalize = function (string $s): string {
            return preg_replace('/\s+/u', ' ', trim($s));
        };

        $rows = collect($items)->map(function ($i) use ($now, $uid, $ip, $normalize) {
            $fac = $normalize($i['facultad']);
            $pro = $normalize($i['programa_academico']);

            return [
                'nivel_academico'     => $i['nivel_academico'],
                'facultad'            => $fac,
                'programa_academico'  => $pro,
                'fechacreacion'       => $now,
                'usuariocreacion'     => $uid,
                'ipcreacion'          => $ip,
                'fechamodificacion'   => $now,
                'usuariomodificacion' => $uid,
                'ipmodificacion'      => $ip,
                '__key'               => mb_strtolower($fac).'|'.mb_strtolower($pro),
            ];
        });

        $existentes = Catalogo::query()
            ->where(function ($q) use ($rows) {
                $pairs = $rows->pluck('__key')->unique()->values();
                foreach ($pairs as $key) {
                    [$facKey, $proKey] = explode('|', $key, 2);
                    $q->orWhere(function ($qq) use ($facKey, $proKey) {
                        $qq->whereRaw('LOWER(facultad) = ?', [$facKey])
                           ->whereRaw('LOWER(programa_academico) = ?', [$proKey]);
                    });
                }
            })
            ->get()
            ->mapWithKeys(function ($c) {
                return [ mb_strtolower($c->facultad).'|'.mb_strtolower($c->programa_academico) => true ];
            });

        $marcas = $rows->map(fn($r) => [
            'key'      => $r['__key'],
            'existing' => $existentes->has($r['__key']),
        ]);
        $this->repo->upsertBulk($rows->all());

        // obtener los registros afectados
        $affected = $this->repo->findByPairs($rows->all());

        $created   = $marcas->where('existing', false)->count();
        $updated   = $marcas->where('existing', true)->count();
        $processed = $rows->count();

        return [
            'rows' => $affected,
            'meta' => [
                'processed' => $processed,
                'created'   => $created,
                'updated'   => $updated,
                'timestamp' => $now->toIso8601String(),
            ],
        ];
    }

    public function destroyBulk(array $ids): array
    {
        $ids = array_values(array_unique($ids));
        $deleted = $this->repo->deleteByIds($ids);

        return [
            'requested' => count($ids),
            'deleted'   => (int) $deleted,
        ];
    }
}
