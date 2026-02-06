<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Aprobacion extends Model
{
    protected $table = 'aprobaciones';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'aprobable_type',
        'aprobable_id',
        'etapa',
        'estado',
        'decidido_por',
        'decidido_en',
        'justificacion',
        'ip',
        'meta',
    ];

    protected $casts = [
        'decidido_en' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Aprobacion $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function aprobable(): MorphTo
    {
        return $this->morphTo();
    }
}
