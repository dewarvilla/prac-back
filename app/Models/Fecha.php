<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Concerns\Auditable;

class Fecha extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'fechas';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'estado',
        'periodo',
        'fecha_apertura_preg',
        'fecha_cierre_docente_preg',
        'fecha_cierre_jefe_depart',
        'fecha_cierre_decano',
        'fecha_apertura_postg',
        'fecha_cierre_docente_postg',
        'fecha_cierre_coordinador_postg',
        'fecha_cierre_jefe_postg',
    ];

    protected $casts = [
        'estado'                         => 'boolean',
        'periodo'                        => 'string',
        'fecha_apertura_preg'            => 'date',
        'fecha_cierre_docente_preg'      => 'date',
        'fecha_cierre_jefe_depart'       => 'date',
        'fecha_cierre_decano'            => 'date',
        'fecha_apertura_postg'           => 'date',
        'fecha_cierre_docente_postg'     => 'date',
        'fecha_cierre_coordinador_postg' => 'date',
        'fecha_cierre_jefe_postg'        => 'date',
        'created_at'                     => 'datetime',
        'updated_at'                     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function ventanaDocente(string $nivel): array
    {
        if ($nivel === 'pregrado') return [$this->fecha_apertura_preg, $this->fecha_cierre_docente_preg];
        if ($nivel === 'postgrado') return [$this->fecha_apertura_postg, $this->fecha_cierre_docente_postg];
        return [null, null];
    }

    public static function dentro(?Carbon $inicio, ?Carbon $fin, Carbon $fecha): bool
    {
        if (!$inicio || !$fin) return false;
        return $fecha->gte($inicio) && $fecha->lte($fin);
    }
}
