<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\Concerns\Auditable;

class ApprovalDefinition extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'approval_definitions';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code','name','is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
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
