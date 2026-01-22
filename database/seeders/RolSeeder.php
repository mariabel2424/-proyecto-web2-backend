<?php
namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Roles para el Sistema de Cursos Vacacionales
     * 
     * - Administrador: Gestiona todo el sistema
     * - Tutor: Padre/madre que se registra públicamente (ROL POR DEFECTO)
     * - Instructor: Profesor asignado por el admin
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador',
                'slug' => 'administrador',
                'descripcion' => 'Acceso total al sistema',
                'activo' => true
            ],
            [
                'nombre' => 'Tutor',
                'slug' => 'tutor',
                'descripcion' => 'Padre/madre que registra e inscribe a sus hijos en cursos',
                'activo' => true
            ],
            [
                'nombre' => 'Instructor',
                'slug' => 'instructor',
                'descripcion' => 'Profesor que imparte los cursos vacacionales',
                'activo' => true
            ],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(
                ['slug' => $rol['slug']],
                $rol
            );
        }
    }
}
