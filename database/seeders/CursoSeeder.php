<?php

namespace Database\Seeders;

use App\Models\Curso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = [
            [
                'nombre' => 'Natación Infantil',
                'descripcion' => 'Curso de natación para niños de 5 a 10 años. Aprenderán técnicas básicas de nado y seguridad en el agua.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-03-15',
                'representante' => 'María García',
                'email_representante' => 'maria.garcia@ejemplo.com',
                'telefono_representante' => '0991234567',
                'tipo' => 'vacacional',
                'estado' => 'abierto',
                'cupo_maximo' => 20,
                'cupo_actual' => 0,
                'precio' => 75.00,
            ],
            [
                'nombre' => 'Fútbol Juvenil',
                'descripcion' => 'Entrenamiento de fútbol para jóvenes de 10 a 15 años. Incluye técnica, táctica y partidos.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-03-30',
                'representante' => 'Carlos López',
                'email_representante' => 'carlos.lopez@ejemplo.com',
                'telefono_representante' => '0987654321',
                'tipo' => 'vacacional',
                'estado' => 'abierto',
                'cupo_maximo' => 30,
                'cupo_actual' => 0,
                'precio' => 60.00,
            ],
            [
                'nombre' => 'Baloncesto',
                'descripcion' => 'Curso de baloncesto para todas las edades. Fundamentos y juego en equipo.',
                'fecha_inicio' => '2026-01-20',
                'fecha_fin' => '2026-02-28',
                'representante' => 'Ana Martínez',
                'email_representante' => 'ana.martinez@ejemplo.com',
                'telefono_representante' => '0976543210',
                'tipo' => 'permanente',
                'estado' => 'abierto',
                'cupo_maximo' => 25,
                'cupo_actual' => 0,
                'precio' => 50.00,
            ],
            [
                'nombre' => 'Gimnasia Artística',
                'descripcion' => 'Clases de gimnasia artística para niños y adolescentes.',
                'fecha_inicio' => '2026-02-15',
                'fecha_fin' => '2026-04-15',
                'representante' => 'Laura Sánchez',
                'email_representante' => 'laura.sanchez@ejemplo.com',
                'telefono_representante' => '0965432109',
                'tipo' => 'vacacional',
                'estado' => 'abierto',
                'cupo_maximo' => 15,
                'cupo_actual' => 0,
                'precio' => 80.00,
            ],
        ];

        foreach ($cursos as $curso) {
            Curso::create([
                ...$curso,
                'slug' => Str::slug($curso['nombre']),
            ]);
        }
    }
}
