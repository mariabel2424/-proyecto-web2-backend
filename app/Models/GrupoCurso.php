<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrupoCurso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grupos_curso';
    protected $primaryKey = 'id_grupo';
    
    protected $fillable = [
        'id_curso',
        'nombre',
        'cupo_maximo',
        'cupo_actual',
        'hora_inicio',
        'hora_fin',
        'dias_semana',
        'estado',
        'id_instructor', // Usuario con rol instructor
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'cupo_maximo' => 'integer',
        'cupo_actual' => 'integer'
    ];

    protected $appends = ['dias_semana_nombres', 'cupos_disponibles'];

    // Relaciones
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionCurso::class, 'id_grupo', 'id_grupo');
    }

    public function deportistas()
    {
        return $this->hasManyThrough(
            Deportista::class,
            InscripcionCurso::class,
            'id_grupo',
            'id_deportista',
            'id_grupo',
            'id_deportista'
        );
    }

    /**
     * Instructor asignado a este grupo (Usuario con rol instructor)
     */
    public function instructor()
    {
        return $this->belongsTo(Usuario::class, 'id_instructor', 'id_usuario');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_grupo', 'id_grupo');
    }

    // Métodos auxiliares
    public function getCuposDisponiblesAttribute()
    {
        return $this->cupo_maximo - $this->cupo_actual;
    }

    public function tieneCupoDisponible()
    {
        return $this->cupo_actual < $this->cupo_maximo;
    }

    public function isCompleto()
    {
        return $this->estado === 'completo' || $this->cupo_actual >= $this->cupo_maximo;
    }

    public function isActivo()
    {
        return $this->estado === 'activo';
    }

    public function incrementarCupo()
    {
        $this->increment('cupo_actual');
        
        if ($this->cupo_actual >= $this->cupo_maximo) {
            $this->update(['estado' => 'completo']);
        }
    }

    public function decrementarCupo()
    {
        if ($this->cupo_actual > 0) {
            $this->decrement('cupo_actual');
            
            if ($this->estado === 'completo') {
                $this->update(['estado' => 'activo']);
            }
        }
    }

    public function getDiasSemanaNombresAttribute()
    {
        if (empty($this->dias_semana)) {
            return 'No hay horarios';
        }

        $dias = [
            '1' => 'Lunes',
            '2' => 'Martes',
            '3' => 'Miércoles',
            '4' => 'Jueves',
            '5' => 'Viernes',
            '6' => 'Sábado',
            '7' => 'Domingo',
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miércoles' => 'Miércoles',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sábado' => 'Sábado',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];

        return collect($this->dias_semana)->map(function($dia) use ($dias) {
            $diaStr = strtolower(strval($dia));
            return $dias[$diaStr] ?? $dias[strval($dia)] ?? ucfirst($dia);
        })->implode(', ');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeConCupoDisponible($query)
    {
        return $query->whereRaw('cupo_actual < cupo_maximo');
    }

    public function scopeDelCurso($query, $idCurso)
    {
        return $query->where('id_curso', $idCurso);
    }
}