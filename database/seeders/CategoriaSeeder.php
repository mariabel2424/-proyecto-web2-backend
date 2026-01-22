<?php
namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Categorías por edad para Cursos Vacacionales
     * 
     * Las categorías ayudan a organizar a los participantes
     * por grupos de edad para los cursos.
     */
    public function run(): void
    {
        $categorias = [
            // Categorías por edad (mixtas)
            [
                'nombre' => 'Pequeños (4-6 años)',
                'edad_minima' => 4,
                'edad_maxima' => 6,
                'descripcion' => 'Niños en edad preescolar'
            ],
            [
                'nombre' => 'Infantil A (7-9 años)',
                'edad_minima' => 7,
                'edad_maxima' => 9,
                'descripcion' => 'Niños en primeros años de primaria'
            ],
            [
                'nombre' => 'Infantil B (10-12 años)',
                'edad_minima' => 10,
                'edad_maxima' => 12,
                'descripcion' => 'Niños en últimos años de primaria'
            ],
            [
                'nombre' => 'Juvenil (13-15 años)',
                'edad_minima' => 13,
                'edad_maxima' => 15,
                'descripcion' => 'Adolescentes en secundaria'
            ],
            [
                'nombre' => 'Jóvenes (16-18 años)',
                'edad_minima' => 16,
                'edad_maxima' => 18,
                'descripcion' => 'Jóvenes en bachillerato'
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}
