<?php

namespace App\Observers;

use App\Models\Audit;
use App\Support\AuditContext;
use Illuminate\Database\Eloquent\Model;

class AuditingObserver
{
    public function created(Model $model): void
    {
        if (!$this->shouldAudit($model, 'created')) return;

        $new = $this->filterValues($model, $model->getAttributes());
        $meta = $this->meta($model, 'created', [], [], $new);

        Audit::create(array_merge($this->base($model, 'created'), [
            'old_values' => null,
            'new_values' => $new,
            'meta'       => $meta ?: null,
        ]));
    }

    public function updated(Model $model): void
    {
        if (!$this->shouldAudit($model, 'updated')) return;

        $dirty = $model->getDirty();
        if (empty($dirty)) return;

        $exclude = $model->auditExclude();
        $changedKeys = array_values(array_diff(array_keys($dirty), $exclude));
        if (!$changedKeys) return;

        $old = [];
        $new = [];

        foreach ($changedKeys as $k) {
            $old[$k] = $model->getOriginal($k);
            $new[$k] = $model->getAttribute($k);
        }

        $old = $this->filterValues($model, $old);
        $new = $this->filterValues($model, $new);

        $meta = $this->meta($model, 'updated', $dirty, $old, $new);

        Audit::create(array_merge($this->base($model, 'updated'), [
            'old_values' => $old,
            'new_values' => $new,
            'meta'       => $meta ?: null,
        ]));
    }

    public function deleted(Model $model): void
    {
        if (!$this->shouldAudit($model, 'deleted')) return;

        $old = $this->filterValues($model, $model->getOriginal());
        $meta = $this->meta($model, 'deleted', [], $old, []);

        Audit::create(array_merge($this->base($model, 'deleted'), [
            'old_values' => $old,
            'new_values' => null,
            'meta'       => $meta ?: null,
        ]));
    }

    // -------------------------
    // Helpers
    // -------------------------
    protected function shouldAudit(Model $model, string $event): bool
    {
        if ($model instanceof Audit) return false;
        if (!method_exists($model, 'auditEvents') || !method_exists($model, 'auditExclude')) return false;

        return in_array($event, $model->auditEvents(), true);
    }

    protected function base(Model $model, string $event): array
    {
        return array_merge([
            'auditable_table' => $model->getTable(),
            'auditable_type'  => get_class($model),
            'auditable_id'    => (string) $model->getKey(),
            'event'           => $event,
        ], AuditContext::current());
    }

    protected function filterValues(Model $model, array $values): array
    {
        $exclude = $model->auditExclude();

        foreach ($exclude as $k) {
            unset($values[$k]);
        }

        foreach ($values as $k => $v) {
            if ($v instanceof \DateTimeInterface) {
                $values[$k] = $v->format('Y-m-d H:i:s');
            }
        }

        return $values;
    }

    protected function meta(Model $model, string $event, array $dirty, array $old, array $new): array
    {
        if (method_exists($model, 'auditMeta')) {
            try {
                $m = $model->auditMeta($event, $dirty, $old, $new);
                return is_array($m) ? $m : [];
            } catch (\Throwable $e) {
                return [];
            }
        }
        return [];
    }
}