<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Deportista (Participante)
 * 
 * Representa a los niños/jóvenes que participan en los cursos vacacionales.
 * Son registrados por sus tutores (padres/representantes).
 */
class Deportista extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'deportistas';
    protected $primaryKey = 'id_deportista';

    protected $fillable = [
        'id_categoria',
        'nombres',
        'apellidos',
        'cedula',
        'fecha_nacimiento',
        'genero',
        'foto',
        'direccion',
        'telefono',
        'email',
        'peso',
        'altura',
        'tipo_sangre',
        'alergias',
        'enfermedades',
        'medicamentos',
        'contacto_emergencia',
        'telefono_emergencia',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'altura' => 'decimal:2',
        'peso' => 'decimal:2',
    ];

    protected $appends = ['nombre_completo', 'edad'];

    protected $attributes = [
        'estado' => 'activo'
    ];

    // ============ RELACIONES ============

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function tutores()
    {
        return $this->belongsToMany(Usuario::class, 'deportista_tutores', 'id_deportista', 'id_usuario')
                    ->withPivot('parentesco', 'es_principal', 'es_emergencia')
                    ->withTimestamps();
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionCurso::class, 'id_deportista', 'id_deportista');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'created_by', 'id_usuario');
    }

    // ============ SCOPES ============

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeInactivos($query)
    {
        return $query->where('estado', 'inactivo');
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('id_categoria', $categoriaId);
    }

    // ============ ACCESSORS ============

    public function getNombreCompletoAttribute()
    {
        return $this->nombres . ' ' . $this->apellidos;
    }

    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return null;
    }
}
