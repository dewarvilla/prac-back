<?php

namespace App\Repositories\Salario;

use App\Models\Salario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface SalarioInterface
{
    public function query(): Builder;

    public function find(string $id): ?Salario;

    public function create(array $data): Salario;

    public function update(string $id, array $data): ?Salario;

    public function delete(string $id): bool;

    public function deleteWhereIn(array $ids): int;

    public function getAll(array $filters = []): Collection;

    public function paginate(array $filters = [], int $perPage = 15, array $appends = []): LengthAwarePaginator;

    public function existsAnio(int $anio, ?string $ignoreId = null): bool;
}
