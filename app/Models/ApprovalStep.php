<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $table = 'approval_steps';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'approval_request_id','step_order','role_key',
        'status','acted_by','acted_at','comment',
        'usuariocreacion','usuariomodificacion',
        'ipcreacion','ipmodificacion',
    ];

    protected $casts = [
        'step_order'        => 'integer',
        'acted_by'          => 'integer',
        'acted_at'          => 'datetime',
        'fechacreacion'     => 'datetime',
        'fechamodificacion' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}
