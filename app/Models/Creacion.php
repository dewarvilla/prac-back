<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Concerns\Auditable;

class Creacion extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'creaciones';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'catalogo_id',
        'nombre_practica',
        'recursos_necesarios',
        'justificacion',
        'estado_creacion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function setNombrePracticaAttribute($value): void
    {
        $v = is_string($value) ? preg_replace('/\s+/', ' ', trim($value)) : $value;
        $this->attributes['nombre_practica'] = $v;
    }

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'catalogo_id');
    }

    public function programaciones()
    {
        return $this->hasMany(Programacion::class, 'creacion_id');
    }

    public function currentApprovalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable')
            ->where('is_current', true)
            ->where('status', 'pending')
            ->whereHas('definition', fn($q) => $q->where('code', 'CREACION_PRACTICA'));
    }
}