<?php

namespace App\Repositories\Catalogo;

use App\Models\Catalogo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CatalogoInterface
{
    public function query(): Builder;

    public function find(string $id): ?Catalogo;

    public function create(array $data): Catalogo;

    public function update(string $id, array $data): ?Catalogo;

    public function delete(string $id): bool;

    public function deleteWhereIn(array $ids): int;

    public function getAll(array $filters = []): Collection;

    public function paginate(array $filters = [], int $perPage = 15, array $appends = []): LengthAwarePaginator;

    public function existsPair(string $facultad, string $programaAcademico, ?string $ignoreId = null): bool;

    /**
     * Upsert masivo por (facultad, programa_academico)
     * Debe devolver ids afectados si lo quieres, pero en tu controller basta con traerlos luego.
     */
    public function upsertBulk(array $rows): void;

    public function findByPairs(array $rows): Collection;
}
