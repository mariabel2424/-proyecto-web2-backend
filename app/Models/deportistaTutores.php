<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo pivote entre Deportista y Usuario (tutor)
 */
class deportistaTutores extends Model
{
    use HasFactory;

    protected $table = 'deportista_tutores';

    protected $fillable = [
        'id_deportista',
        'id_usuario',
        'parentesco',
        'es_principal',
        'es_emergencia',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'es_emergencia' => 'boolean',
    ];

    /**
     * Relación: pertenece a un deportista
     */
    public function deportista()
    {
        return $this->belongsTo(Deportista::class, 'id_deportista', 'id_deportista');
    }

    /**
     * Relación: pertenece a un usuario (tutor)
     */
    public function tutor()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
