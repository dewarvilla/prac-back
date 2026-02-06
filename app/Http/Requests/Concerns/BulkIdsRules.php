<?php

namespace App\Http\Requests\Concerns;

trait BulkIdsRules
{
    protected function bulkIdsRules(string $table, string $type = 'uuid'): array
    {
        $idRules = $type === 'int'
            ? ['integer','distinct','min:1',"exists:{$table},id"]
            : ['string','uuid','distinct',"exists:{$table},id"];

        return [
            'ids'   => ['required','array','min:1','max:1000'],
            'ids.*' => $idRules,
        ];
    }

    protected function bulkIdsMessages(): array
    {
        return ['ids.required' => 'Debes enviar al menos un id.'];
    }
}
