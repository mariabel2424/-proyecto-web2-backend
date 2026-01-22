<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modelo Usuario
 * 
 * ARQUITECTURA SIMPLIFICADA:
 * - Un usuario con rol "instructor" ES el instructor
 * - Un usuario con rol "tutor" ES el tutor
 * - No se necesitan tablas separadas para instructor/tutor
 */
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    
    protected $fillable = [
        'id_rol',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'direccion',
        'avatar',
        'password',
        'status',
        // Campos para instructores
        'especialidad',
        'certificaciones',
        // Campos para tutores
        'cedula',
        'parentesco',
        // Auditoría
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $appends = ['nombre_completo'];

    // ============ RELACIONES ============

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function inscripcionCursos()
    {
        return $this->hasMany(InscripcionCurso::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Grupos donde este usuario es instructor
     */
    public function gruposComoInstructor()
    {
        return $this->hasMany(GrupoCurso::class, 'id_instructor', 'id_usuario');
    }

    /**
     * Deportistas a cargo de este usuario (si es tutor)
     */
    public function deportistasACargo()
    {
        return $this->belongsToMany(
            Deportista::class,
            'deportista_tutores',
            'id_usuario',
            'id_deportista'
        )->withPivot('parentesco', 'es_principal', 'es_emergencia')->withTimestamps();
    }

    /**
     * Facturas de este usuario (si es tutor)
     */
    public function facturas()
    {
        return $this->hasMany(Factura::class, 'id_usuario', 'id_usuario');
    }

    // ============ ACCESSORS ============

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    // ============ MÉTODOS DE ROL ============

    public function hasPermission($permissionSlug)
    {
        if (!$this->relationLoaded('rol')) {
            $this->load('rol.permisos');
        } elseif ($this->rol && !$this->rol->relationLoaded('permisos')) {
            $this->rol->load('permisos');
        }
        
        return $this->rol && $this->rol->permisos->contains('slug', $permissionSlug);
    }

    public function isActivo()
    {
        return $this->status === 'activo';
    }

    public function isAdmin()
    {
        if (!$this->relationLoaded('rol')) {
            $this->load('rol');
        }
        return $this->rol && $this->rol->slug === 'administrador';
    }

    public function isInstructor()
    {
        if (!$this->relationLoaded('rol')) {
            $this->load('rol');
        }
        return $this->rol && $this->rol->slug === 'instructor';
    }

    public function isTutor()
    {
        if (!$this->relationLoaded('rol')) {
            $this->load('rol');
        }
        return $this->rol && $this->rol->slug === 'tutor';
    }

    // ============ SCOPES ============

    public function scopeInstructores($query)
    {
        return $query->whereHas('rol', function($q) {
            $q->where('slug', 'instructor');
        })->where('status', 'activo');
    }

    public function scopeTutores($query)
    {
        return $query->whereHas('rol', function($q) {
            $q->where('slug', 'tutor');
        })->where('status', 'activo');
    }

    public function scopeActivos($query)
    {
        return $query->where('status', 'activo');
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('apellido', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%")
              ->orWhere('cedula', 'like', "%{$termino}%");
        });
    }
}
