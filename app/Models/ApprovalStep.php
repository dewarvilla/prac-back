<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\Concerns\Auditable;

class ApprovalStep extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'approval_steps';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'approval_request_id','step_order','role_key',
        'status','acted_by','acted_at','comment',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'acted_by'   => 'integer',
        'acted_at'   => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $auditEvents = ['updated'];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function auditMeta(string $event, array $dirty = [], array $old = [], array $new = []): array
    {
        $meta = [
            'approval_request_id' => (string) $this->approval_request_id,
            'step_order'          => (int) $this->step_order,
            'role_key'            => (string) $this->role_key,
        ];

        if ($event === 'updated' && array_key_exists('status', $dirty)) {
            $meta['status_from'] = $old['status'] ?? $this->getOriginal('status');
            $meta['status_to']   = $new['status'] ?? $this->status;
        }

        return $meta;
    }

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}