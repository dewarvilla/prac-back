<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salario extends Model
{
    use HasFactory;

    protected $table = 'salarios';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
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
}
