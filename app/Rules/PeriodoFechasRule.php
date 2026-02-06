<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule as RuleContract;

class PeriodoFechasRule implements RuleContract
{
    private ?string $periodo;
    private string $msg = 'La fecha no corresponde al rango semestral del periodo.';

    public function __construct(?string $periodo)
    {
        $this->periodo = $periodo;
    }

    public function passes($attribute, $value): bool
    {
        if (!$this->periodo || !preg_match('/^(?<anio>\d{4})-(?<sem>1|2)$/', $this->periodo, $m)) {
            $this->msg = 'El periodo debe ser de la forma YYYY-1 o YYYY-2.';
            return false;
        }

        $anio = (int) $m['anio'];
        $sem  = (int) $m['sem'];

        try {
            $fecha = Carbon::createFromFormat('Y-m-d', (string)$value)->startOfDay();
        } catch (\Exception $e) {
            $this->msg = 'La fecha debe tener formato Y-m-d.';
            return false;
        }

        $inicio = Carbon::create($anio, $sem === 1 ? 1 : 7, 1)->startOfDay();
        $fin    = $sem === 1
            ? Carbon::create($anio, 6, 30)->endOfDay()
            : Carbon::create($anio, 12, 31)->endOfDay();

        if ($fecha->lt($inicio) || $fecha->gt($fin)) {
            $this->msg = "La fecha debe estar dentro del periodo establecido {$this->periodo} ({$inicio->toDateString()} a {$fin->toDateString()}).";
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->msg;
    }
}
