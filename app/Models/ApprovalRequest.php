<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $table = 'approval_requests';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'approvable_type','approvable_id',
        'approval_definition_id',
        'status','current_step_order',
        'active_key',
        'usuariocreacion','usuariomodificacion',
        'ipcreacion','ipmodificacion',
    ];

    protected $casts = [
        'current_step_order' => 'integer',
        'active_key'         => 'integer',
        'fechacreacion'      => 'datetime',
        'fechamodificacion'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
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
            ->where('step_order', $this->current_step_order);
    }

    public function scopeActive($q)
    {
        return $q->where('active_key', 1);
    }
}
