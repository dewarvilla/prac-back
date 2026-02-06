<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Catalogo extends Model
{
    use HasFactory;

    protected $table = 'catalogos';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'estado',
        'nivel_academico',
        'facultad',
        'programa_academico',
        'usuariocreacion',
        'usuariomodificacion',
        'ipcreacion',
        'ipmodificacion',
    ];

    protected $casts = [
        'estado'            => 'boolean',
        'fechacreacion'     => 'datetime',
        'fechamodificacion' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Catalogo $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function creaciones()
    {
        return $this->hasMany(Creacion::class, 'catalogo_id');
    }
}
