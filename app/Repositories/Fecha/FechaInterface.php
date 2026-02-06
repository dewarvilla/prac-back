<?php

namespace App\Repositories\Fecha;

use App\Models\Fecha;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface FechaInterface
{
    public function query(): Builder;

    public function find(string $id): ?Fecha;

    public function create(array $data): Fecha;

    public function update(string $id, array $data): ?Fecha;

    public function delete(string $id): bool;

    public function deleteWhereIn(array $ids): int;

    public function getAll(array $filters = []): Collection;

    public function paginate(array $filters = [], int $perPage = 15, array $appends = []): LengthAwarePaginator;

    public function existsPeriodo(string $periodo, ?string $ignoreId = null): bool;
}
