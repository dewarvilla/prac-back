<?php

namespace App\Repositories\Programacion;

use App\Models\Programacion;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface ProgramacionInterface
{
    public function baseVisibleQuery(User $user): Builder;

    public function findVisible(string $id, User $user): ?Programacion;

    public function create(array $data): Programacion;

    public function update(string $id, array $data): ?Programacion;

    public function delete(string $id): bool;

    public function deleteWhereIn(array $ids): int;

    public function getAllVisible(User $user, array $filters = []): Collection;

    public function paginateVisible(User $user, array $filters = [], int $perPage = 15, array $appends = []): LengthAwarePaginator;
}
