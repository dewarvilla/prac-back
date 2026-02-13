<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ApprovalDefinition extends Model
{
    use HasFactory;

    protected $table = 'approval_definitions';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code','name','is_active',
        'usuariocreacion','usuariomodificacion',
        'ipcreacion','ipmodificacion',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'fechacreacion'    => 'datetime',
        'fechamodificacion'=> 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function steps()
    {
        return $this->hasMany(ApprovalDefinitionStep::class, 'approval_definition_id')
            ->orderBy('step_order');
    }
}
