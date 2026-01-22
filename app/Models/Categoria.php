<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Categoria
 * 
 * Categorías por edad para organizar a los participantes
 * en los cursos vacacionales.
 */
class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    
    protected $fillable = [
        'nombre',
        'edad_minima',
        'edad_maxima',
        'descripcion',
    ];

    protected $casts = [
        'edad_minima' => 'integer',
        'edad_maxima' => 'integer'
    ];

    // ============ RELACIONES ============

    /**
     * Participantes en esta categoría
     */
    public function deportistas()
    {
        return $this->hasMany(Deportista::class, 'id_categoria', 'id_categoria');
    }

    // ============ MÉTODOS ============

    /**
     * Verifica si una edad está dentro del rango de esta categoría
     */
    public function edadEnRango(int $edad): bool
    {
        return $edad >= $this->edad_minima && $edad <= $this->edad_maxima;
    }

    /**
     * Obtiene el rango de edad como texto
     */
    public function getRangoEdadAttribute(): string
    {
        return "{$this->edad_minima} - {$this->edad_maxima} años";
    }
}
