<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ApprovalDefinitionStep extends Model
{
    use HasFactory;

    protected $table = 'approval_definition_steps';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'approval_definition_id','step_order','role_key',
        'requires_comment_on_reject','sla_days',
        'usuariocreacion','usuariomodificacion',
        'ipcreacion','ipmodificacion',
    ];

    protected $casts = [
        'step_order'                 => 'integer',
        'requires_comment_on_reject' => 'boolean',
        'sla_days'                   => 'integer',
        'fechacreacion'              => 'datetime',
        'fechamodificacion'          => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function definition()
    {
        return $this->belongsTo(ApprovalDefinition::class, 'approval_definition_id');
    }
}
