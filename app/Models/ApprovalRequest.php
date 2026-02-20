<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\Concerns\Auditable;

class ApprovalRequest extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'approval_requests';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'approvable_type','approvable_id',
        'approval_definition_id',
        'status','current_step_order',
        'is_current',
        'requested_by',
        'closed_at',
        'meta',
    ];

    protected $casts = [
        'current_step_order' => 'integer',
        'is_current'         => 'boolean',
        'requested_by'       => 'integer',
        'closed_at'          => 'datetime',
        'meta'               => 'array',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function auditMeta(string $event, array $dirty = [], array $old = [], array $new = []): array
    {
        $meta = [
            'approval_definition_id' => (string) $this->approval_definition_id,
            'approvable_type'        => (string) $this->approvable_type,
            'approvable_id'          => (string) $this->approvable_id,
            'current_step_order'     => (int) $this->current_step_order,
            'is_current'             => (bool) $this->is_current,
        ];

        if ($event === 'updated' && array_key_exists('status', $dirty)) {
            $meta['status_from'] = $old['status'] ?? $this->getOriginal('status');
            $meta['status_to']   = $new['status'] ?? $this->status;
        }

        return $meta;
    }

    public function approvable()
    {
        return $this->morphTo();
    }

    public function definition()
    {
        return $this->belongsTo(ApprovalDefinition::class, 'approval_definition_id');
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class, 'approval_request_id')
            ->orderBy('step_order');
    }

    public function currentStep()
    {
        return $this->hasOne(ApprovalStep::class, 'approval_request_id')
            ->whereColumn('approval_steps.step_order', 'approval_requests.current_step_order');
    }
}