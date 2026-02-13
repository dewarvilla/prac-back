<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Creacion extends Model
{
    use HasFactory;

    protected $table = 'creaciones';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'catalogo_id',
        'nombre_practica',
        'recursos_necesarios',
        'justificacion',
        'estado_creacion',
        'usuariocreacion',
        'usuariomodificacion',
        'ipcreacion',
        'ipmodificacion',
    ];

    protected $casts = [
        'fechacreacion'     => 'datetime',
        'fechamodificacion' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Creacion $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected function setNombrePracticaAttribute($value)
    {
        $v = is_string($value) ? preg_replace('/\s+/', ' ', trim($value)) : $value;
        $this->attributes['nombre_practica'] = $v;
    }

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'catalogo_id');
    }

    public function programaciones()
    {
        return $this->hasMany(Programacion::class, 'creacion_id');
    }

    // (Opcional) Cuando ya tengas approvals:
    // public function approvalRequest()
    // {
    //     return $this->morphOne(\App\Models\ApprovalRequest::class, 'approvable')
    //         ->where('is_active', true);
    // }
}
