<?php

namespace App\Http\Requests\Concerns;

trait TrimsStrings
{
    /**
     * Trim + colapsa espacios + devuelve null si queda vacío.
     */
    protected function normText($v): ?string
    {
        if ($v === null) return null;

        $v = preg_replace('/\s+/u', ' ', trim((string) $v));
        return $v === '' ? null : $v;
    }

    /**
     * Variante estricta: siempre string (por si alguna vez la quieres).
     */
    protected function trimString(?string $v): ?string
    {
        if ($v === null) return null;
        return preg_replace('/\s+/u', ' ', trim($v));
    }
}
