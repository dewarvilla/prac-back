<?php

namespace App\Repositories\Ruta;

use App\Models\Ruta;
use Illuminate\Database\Eloquent\Builder;

class RutaRepository
{
    public function query(): Builder
    {
        return Ruta::query();
    }

    public function find(int $id): ?Ruta
    {
        return Ruta::find($id);
    }

    public function create(array $data): Ruta
    {
        return Ruta::create($data);
    }

    public function update(int $id, array $data): ?Ruta
    {
        $ruta = $this->find($id);
        if (!$ruta) return null;

        $ruta->update($data);
        return $ruta->refresh();
    }

    public function delete(int $id): bool
    {
        $ruta = $this->find($id);
        if (!$ruta) return false;

        $ruta->delete();
        return true;
    }

    public function deleteWhereIn(array $ids): int
    {
        return Ruta::whereIn('id', $ids)->delete();
    }
}
