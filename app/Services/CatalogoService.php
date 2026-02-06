<?php

namespace App\Services;

use App\Models\Catalogo;
use App\Models\User;
use App\Repositories\Catalogo\CatalogoInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogoService
{
    public function __construct(private readonly CatalogoInterface $repo)
    {
    }

    public function search(array $filters = [], int $perPage = 0)
    {
        return $perPage > 0
            ? $this->repo->paginate($filters, $perPage, $filters)
            : $this->repo->getAll($filters);
    }

    public function create(array $data, ?User $user, string $ip): Catalogo
    {
        $now = now();
        $uid = $user?->id ?? 0;

        $payload = $data + [
            'estado'              => true,
            'fechacreacion'       => $now,
            'fechamodificacion'   => $now,
            'usuariocreacion'     => $uid,
            'usuariomodificacion' => $uid,
            'ipcreacion'          => $ip,
            'ipmodificacion'      => $ip,
        ];

        return $this->repo->create($payload)->fresh();
    }

    public function update(string $id, array $data, ?User $user, string $ip): ?Catalogo
    {
        $uid = $user?->id ?? 0;

        $payload = $data + [
            'fechamodificacion'   => now(),
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
     * @return array{rows:\Illuminate\Database\Eloquent\Collection, meta:array}
     */
    public function storeBulk(array $items, ?User $user, string $ip): array
    {
        $now = now();
        $uid = $user?->id ?? 0;

        $normalize = fn(?string $s) => $s === null ? null : preg_replace('/\s+/u', ' ', trim($s));

        $rows = collect($items)->map(function ($i) use ($now, $uid, $ip, $normalize) {
            $fac = $normalize($i['facultad'] ?? '');
            $pro = $normalize($i['programa_academico'] ?? '');

            return [
                'id'                 => (string) Str::uuid(),
                'nivel_academico'    => $i['nivel_academico'],
                'facultad'           => $fac,
                'programa_academico' => $pro,
                'estado'             => true,

                'fechacreacion'       => $now,
                'usuariocreacion'     => $uid,
                'ipcreacion'          => $ip,
                'fechamodificacion'   => $now,
                'usuariomodificacion' => $uid,
                'ipmodificacion'      => $ip,

                '__key'              => mb_strtolower($fac).'|'.mb_strtolower($pro),
            ];
        });

        // detectar cuáles ya existían
        $existentes = Catalogo::query()
            ->where(function ($q) use ($rows) {
                foreach ($rows->pluck('__key')->unique() as $key) {
                    [$facKey, $proKey] = explode('|', $key, 2);
                    $q->orWhere(function ($qq) use ($facKey, $proKey) {
                        $qq->whereRaw('LOWER(facultad) = ?', [$facKey])
                           ->whereRaw('LOWER(programa_academico) = ?', [$proKey]);
                    });
                }
            })
            ->get()
            ->mapWithKeys(fn($c) => [mb_strtolower($c->facultad).'|'.mb_strtolower($c->programa_academico) => true]);

        $marcas = $rows->map(fn($r) => [
            'key'      => $r['__key'],
            'existing' => $existentes->has($r['__key']),
        ]);

        $this->repo->upsertBulk($rows->all());

        $affected = $this->repo->findByPairs($rows->all());

        return [
            'rows' => $affected,
            'meta' => [
                'processed' => $rows->count(),
                'created'   => $marcas->where('existing', false)->count(),
                'updated'   => $marcas->where('existing', true)->count(),
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
