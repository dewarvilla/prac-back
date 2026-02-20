<?php

namespace App\Models\Concerns;

use App\Models\Audit;
use App\Observers\AuditingObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditingObserver::class);
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable')
            ->orderByDesc('created_at');
    }

    public function auditExclude(): array
    {
        $base = [
            'created_at', 'updated_at', 'deleted_at',
        ];

        if (property_exists($this, 'auditExclude') && is_array($this->auditExclude)) {
            return array_values(array_unique(array_merge($base, $this->auditExclude)));
        }

        return $base;
    }

    public function auditEvents(): array
    {
        if (property_exists($this, 'auditEvents') && is_array($this->auditEvents)) {
            return $this->auditEvents;
        }

        return ['created', 'updated', 'deleted'];
    }
    public function auditMeta(string $event, array $dirty = [], array $old = [], array $new = []): array
    {
        return [];
    }
}