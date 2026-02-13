<?php

namespace App\Services;

use App\Exceptions\Programaciones\ProgramacionDuplicateNombreFechasException;
use App\Exceptions\Programaciones\ProgramacionNivelFormacionNoResueltoException;
use App\Models\Creacion;
use App\Models\Programacion;
use App\Models\User;
use App\Repositories\Programacion\ProgramacionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProgramacionService
{
    public function __construct(
        private readonly ProgramacionInterface $repo,
    ) {}

    public function search(User $user, array $filters = [], int $perPage = 0, array $appends = [])
    {
        return $perPage > 0
            ? $this->repo->paginateVisible($user, $filters, $perPage, $appends)
            : $this->repo->getAllVisible($user, $filters);
    }

    public function create(array $data, ?User $user, string $ip): Programacion
    {
        return DB::transaction(function () use ($data, $user, $ip) {

            $now = now();
            $uid = $user?->id ?? 0;

            $creacion = Creacion::query()
                ->with('catalogo')
                ->findOrFail((string) $data['creacion_id']);

            $nombre = (string) $creacion->nombre_practica;

            $nivelAcademico = $creacion->catalogo?->nivel_academico;
            $nivel = $this->resolveNivelFormacion($nivelAcademico); // pregrado|posgrado

            $fi = (string) $data['fecha_inicio'];
            $ff = (string) $data['fecha_finalizacion'];

            if ($this->repo->existsNombreFechas($nombre, $fi, $ff)) {
                throw new ProgramacionDuplicateNombreFechasException($nombre, $fi, $ff);
            }

            $payload = $data + [
                'nombre_practica'   => $nombre,
                'nivel_formacion'   => $nivel,
                'estado_practica'   => 'en_aprobacion',

                // Auditoría
                'fechacreacion'       => $now,
                'fechamodificacion'   => $now,
                'usuariocreacion'     => $uid,
                'usuariomodificacion' => $uid,
                'ipcreacion'          => $ip,
                'ipmodificacion'      => $ip,
            ];

            try {
                return $this->repo->create($payload)->fresh();
            } catch (QueryException $e) {
                if (str_contains(strtolower($e->getMessage()), 'programaciones_nombre_fechas_unique')) {
                    throw new ProgramacionDuplicateNombreFechasException($nombre, $fi, $ff);
                }
                throw $e;
            }
        });
    }

    public function update(Programacion $programacion, array $data, ?User $user, string $ip): Programacion
    {
        return DB::transaction(function () use ($programacion, $data, $user, $ip) {

            $uid = $user?->id ?? 0;

            // Si cambia creacion_id, recalcular nombre_practica y nivel_formacion
            $nombre = $programacion->nombre_practica;
            $nivel  = $programacion->nivel_formacion;

            if (array_key_exists('creacion_id', $data)) {
                $creacion = Creacion::query()
                    ->with('catalogo')
                    ->findOrFail((string) $data['creacion_id']);

                $nombre = (string) $creacion->nombre_practica;

                $nivelAcademico = $creacion->catalogo?->nivel_academico;
                $nivel = $this->resolveNivelFormacion($nivelAcademico);
            }

            $fi = (string) ($data['fecha_inicio'] ?? $programacion->fecha_inicio?->format('Y-m-d'));
            $ff = (string) ($data['fecha_finalizacion'] ?? $programacion->fecha_finalizacion?->format('Y-m-d'));

            $touchUnique = array_key_exists('fecha_inicio', $data)
                || array_key_exists('fecha_finalizacion', $data)
                || array_key_exists('creacion_id', $data);

            if ($touchUnique && $this->repo->existsNombreFechas($nombre, $fi, $ff, (string)$programacion->id)) {
                throw new ProgramacionDuplicateNombreFechasException($nombre, $fi, $ff, (string)$programacion->id);
            }

            $payload = $data + [
                'nombre_practica'     => $nombre,
                'nivel_formacion'     => $nivel,

                'fechamodificacion'   => now(),
                'usuariomodificacion' => $uid,
                'ipmodificacion'      => $ip,
            ];

            try {
                $updated = $this->repo->update((string)$programacion->id, $payload);
                abort_if(!$updated, 404);
                return $updated;
            } catch (QueryException $e) {
                if (str_contains(strtolower($e->getMessage()), 'programaciones_nombre_fechas_unique')) {
                    throw new ProgramacionDuplicateNombreFechasException($nombre, $fi, $ff, (string)$programacion->id);
                }
                throw $e;
            }
        });
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
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

    private function resolveNivelFormacion(?string $nivelAcademico): string
    {
        $v = mb_strtolower(trim((string) $nivelAcademico));

        if ($v === 'pregrado') return 'pregrado';
        if (in_array($v, ['postgrado', 'posgrado'], true)) return 'posgrado';

        throw new ProgramacionNivelFormacionNoResueltoException($nivelAcademico);
    }
}
