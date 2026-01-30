<?php

namespace App\Repositories\Creacion;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CreacionInterface extends RepositoryInterface
{
    /** @return LengthAwarePaginator|Collection */
    public function search(array $filters = [], int $perPage = 0);

    public function upsertBulk(array $rows): void;

    public function findByPairs(array $rows);

    public function deleteByIds(array $ids): int;
}
