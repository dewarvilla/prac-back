<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Salario extends Model
{
    use HasFactory;

    protected $table = 'salarios';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'estado',
        'anio',
        'valor',
        'usuariocreacion',
        'usuariomodificacion',
        'ipcreacion',
        'ipmodificacion',
    ];

    protected $casts = [
        'estado'            => 'boolean',
        'anio'              => 'integer',
        'valor'             => 'decimal:2',
        'fechacreacion'     => 'datetime',
        'fechamodificacion' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
