<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\Auditable;

class Salario extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'salarios';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'estado',
        'anio',
        'valor',
    ];

    protected $casts = [
        'estado'     => 'boolean',
        'anio'       => 'integer',
        'valor'      => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
