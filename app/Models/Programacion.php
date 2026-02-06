<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Programacion extends Model
{
    use HasFactory;

    protected $table = 'programaciones';

    const CREATED_AT = 'fechacreacion';
    const UPDATED_AT = 'fechamodificacion';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nombre_practica',
        'descripcion',
        'lugar_de_realizacion',
        'justificacion',
        'recursos_necesarios',
        'estado_practica',
        'estado_depart',
        'estado_postg',
        'estado_decano',
        'estado_jefe_postg',
        'estado_vice',
        'fecha_inicio',
        'fecha_finalizacion',
        'requiere_transporte',
        'creacion_id',
        'usuariocreacion',
        'usuariomodificacion',
        'ipcreacion',
        'ipmodificacion',
        'numero_estudiantes',
    ];

    protected $casts = [
        'fecha_inicio'        => 'date',
        'fecha_finalizacion'  => 'date',
        'requiere_transporte' => 'boolean',
        'fechacreacion'       => 'datetime',
        'fechamodificacion'   => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if ($m->creacion && empty($m->nombre_practica)) {
                $m->nombre_practica = $m->creacion->nombre_practica;
            }
        });

        static::updating(function ($m) {
            if ($m->isDirty('creacion_id') && $m->creacion) {
                $m->nombre_practica = $m->creacion->nombre_practica;
            }
        });
    }

    /* ================== RELACIONES ================== */

    public function creacion()
    {
        return $this->belongsTo(Creacion::class, 'creacion_id');
    }

    public function participantes()
    {
        return $this->hasMany(Participante::class, 'programacion_id');
    }

    public function auxilios()
    {
        return $this->hasMany(Auxilio::class, 'programacion_id');
    }

    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'programacion_id');
    }

    public function reprogramaciones()
    {
        return $this->hasMany(Reprogramacion::class, 'programacion_id');
    }

    public function legalizaciones()
    {
        return $this->hasMany(Legalizacion::class, 'programacion_id');
    }

    public function ajustes()
    {
        return $this->hasMany(Ajuste::class, 'programacion_id');
    }

    /* ================== SCOPE DE VISIBILIDAD ================== */

    public function scopeVisibleFor(Builder $query, User $user): Builder
    {
        if ($user->hasRole('administrador') || $user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('usuariocreacion', $user->id);

            if ($user->can('programaciones.aprobar.departamento')) {
                $q->orWhere(function (Builder $qx) {
                    $qx->whereHas('creacion', function (Builder $qc) {
                            $qc->whereRaw('LOWER(nivel_academico) = ?', ['pregrado']);
                        })
                        ->where('estado_depart', 'pendiente')
                        ->where('estado_practica', '!=', 'rechazada');
                });
            }

            if ($user->can('programaciones.aprobar.postgrados')) {
                $q->orWhere(function (Builder $qx) {
                    $qx->whereHas('creacion', function (Builder $qc) {
                            $qc->whereRaw('LOWER(nivel_academico) = ?', ['postgrado']);
                        })
                        ->where('estado_postg', 'pendiente')
                        ->where('estado_practica', '!=', 'rechazada');
                });
            }

            if ($user->can('programaciones.aprobar.decano')) {
                $q->orWhere(function (Builder $qx) {
                    $qx->whereHas('creacion', function (Builder $qc) {
                            $qc->whereRaw('LOWER(nivel_academico) = ?', ['pregrado']);
                        })
                        ->where('estado_depart', 'aprobada')
                        ->where('estado_decano', 'pendiente')
                        ->where('estado_practica', '!=', 'rechazada');
                });
            }

            if ($user->can('programaciones.aprobar.jefe_postgrados')) {
                $q->orWhere(function (Builder $qx) {
                    $qx->whereHas('creacion', function (Builder $qc) {
                            $qc->whereRaw('LOWER(nivel_academico) = ?', ['postgrado']);
                        })
                        ->where('estado_postg', 'aprobada')
                        ->where('estado_jefe_postg', 'pendiente')
                        ->where('estado_practica', '!=', 'rechazada');
                });
            }

            if ($user->can('programaciones.aprobar.vicerrectoria')) {
                $q->orWhere(function (Builder $qx) {
                    $qx->where('estado_vice', 'pendiente')
                        ->where('estado_practica', '!=', 'rechazada')
                        ->where(function (Builder $q2) {
                            $q2->where(function (Builder $qq) {
                                $qq->whereHas('creacion', function (Builder $qc) {
                                        $qc->whereRaw('LOWER(nivel_academico) = ?', ['pregrado']);
                                    })
                                   ->where('estado_decano', 'aprobada');
                            })
                            ->orWhere(function (Builder $qq) {
                                $qq->whereHas('creacion', function (Builder $qc) {
                                        $qc->whereRaw('LOWER(nivel_academico) = ?', ['postgrado']);
                                    })
                                   ->where('estado_jefe_postg', 'aprobada');
                            });
                        });
                });
            }
        });
    }
}
