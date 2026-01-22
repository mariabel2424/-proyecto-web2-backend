<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';
    protected $primaryKey = 'id_asistencia';

    protected $fillable = [
        'id_grupo',
        'id_deportista',
        'id_instructor',
        'fecha',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relaciones
    public function grupo()
    {
        return $this->belongsTo(GrupoCurso::class, 'id_grupo', 'id_grupo');
    }

    public function deportista()
    {
        return $this->belongsTo(Deportista::class, 'id_deportista', 'id_deportista');
    }

    public function instructor()
    {
        return $this->belongsTo(Usuario::class, 'id_instructor', 'id_usuario');
    }

    // Scopes
    public function scopeDelGrupo($query, $idGrupo)
    {
        return $query->where('id_grupo', $idGrupo);
    }

    public function scopeDeFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function scopePresentes($query)
    {
        return $query->where('estado', 'presente');
    }

    public function scopeAusentes($query)
    {
        return $query->where('estado', 'ausente');
    }
}
