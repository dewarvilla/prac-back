<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Programacion extends Model
{
    use HasFactory;

    protected $table = 'programaciones';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'creacion_id',
        'nombre_practica',
        'descripcion',
        'requiere_transporte',
        'lugar_de_realizacion',
        'justificacion',
        'recursos_necesarios',
        'numero_estudiantes',
        'nivel_formacion',
        'estado_practica',
        'fecha_inicio',
        'fecha_finalizacion',

        // Auditoría
        'usuariocreacion',
        'usuariomodificacion',
        'ipcreacion',
        'ipmodificacion',
    ];

    protected $casts = [
        'fecha_inicio'        => 'date',
        'fecha_finalizacion'  => 'date',
        'requiere_transporte' => 'boolean',
        'fechacreacion'       => 'datetime',
        'fechamodificacion'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Programacion $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function creacion()
    {
        return $this->belongsTo(Creacion::class, 'creacion_id');
    }

    public function participantes()
    {
        return $this->hasMany(Participante::class, 'programacion_id');
    }

    public function auxilios()
    {
        return $this->hasMany(Auxilio::class, 'programacion_id');
    }

    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'programacion_id');
    }

    public function reprogramaciones()
    {
        return $this->hasMany(Reprogramacion::class, 'programacion_id');
    }

    public function legalizaciones()
    {
        return $this->hasMany(Legalizacion::class, 'programacion_id');
    }

    public function ajustes()
    {
        return $this->hasMany(Ajuste::class, 'programacion_id');
    }
}
