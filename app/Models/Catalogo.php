<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\Auditable;

class Catalogo extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'catalogos';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'estado',
        'nivel_academico',
        'facultad',
        'programa_academico',
    ];

    protected $casts = [
        'estado'     => 'boolean',
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

    public function creaciones()
    {
        return $this->hasMany(Creacion::class, 'catalogo_id');
    }
}