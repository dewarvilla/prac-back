<?php

namespace App\Repositories\Creacion;

use App\Models\Creacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CreacionInterface
{
    public function query(): Builder;

    public function find(string $id): ?Creacion;

    public function create(array $data): Creacion;

    public function update(string $id, array $data): ?Creacion;

    public function delete(string $id): bool;

    public function deleteWhereIn(array $ids): int;

    public function getAll(array $filters = []): Collection;

    public function paginate(array $filters = [], int $perPage = 15, array $appends = []): LengthAwarePaginator;

    public function existsNombreInCatalogo(string $catalogoId, string $nombre, ?string $ignoreId = null): bool;
}
