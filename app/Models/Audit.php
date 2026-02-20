<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Audit extends Model
{
    protected $table = 'audits';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'auditable_table',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'actor_id',
        'actor_email',
        'ip',
        'user_agent',
        'method',
        'url',
        'route',
        'request_id',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
