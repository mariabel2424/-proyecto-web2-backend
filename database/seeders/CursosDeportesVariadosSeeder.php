<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\GrupoCurso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CursosDeportesVariadosSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = [
            // NATACIÓN
            [
                'nombre' => 'Natación Infantil - Escuela Acuática Cevallos',
                'descripcion' => 'Iniciación a la natación para niños de 4 a 7 años. Técnicas básicas y seguridad en el agua.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Óscar Cevallos Mendoza',
                'email_representante' => 'oscar.cevallos@natacion.com',
                'telefono_representante' => '0991234567',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 30,
                'cupo_actual' => 0,
                'precio' => 55.00,
                'grupos' => [
                    ['nombre' => 'Grupo A - Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '09:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo B - Tarde', 'hora_inicio' => '15:00', 'hora_fin' => '16:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Natación Competitiva - Academia Galo Delgado',
                'descripcion' => 'Entrenamiento avanzado de natación. Estilos olímpicos y preparación competitiva.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Galo Delgado Rodríguez',
                'email_representante' => 'galo.delgado@natacion.com',
                'telefono_representante' => '0992345678',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 20,
                'cupo_actual' => 0,
                'precio' => 75.00,
                'grupos' => [
                    ['nombre' => 'Grupo Elite - Mañana', 'hora_inicio' => '06:30', 'hora_fin' => '07:30', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '17:00', 'hora_fin' => '18:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],

            // BALONCESTO
            [
                'nombre' => 'Baloncesto Base - Cantera Julio Arizaga',
                'descripcion' => 'Formación básica en baloncesto. Fundamentos, pases y juego en equipo.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Julio Arizaga Zambrano',
                'email_representante' => 'julio.arizaga@baloncesto.com',
                'telefono_representante' => '0993456789',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 28,
                'cupo_actual' => 0,
                'precio' => 60.00,
                'grupos' => [
                    ['nombre' => 'Grupo Infantil - Mañana', 'hora_inicio' => '09:00', 'hora_fin' => '10:30', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Juvenil - Tarde', 'hora_inicio' => '16:00', 'hora_fin' => '17:30', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Baloncesto Competitivo - Escuela Ramón Arias',
                'descripcion' => 'Preparación competitiva en baloncesto. Tácticas avanzadas y juego de equipo.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Ramón Arias Montoya',
                'email_representante' => 'ramon.arias@baloncesto.com',
                'telefono_representante' => '0994567890',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 24,
                'cupo_actual' => 0,
                'precio' => 70.00,
                'grupos' => [
                    ['nombre' => 'Grupo A - Mañana', 'hora_inicio' => '07:00', 'hora_fin' => '08:30', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo B - Tarde', 'hora_inicio' => '17:30', 'hora_fin' => '19:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],

            // VOLEIBOL
            [
                'nombre' => 'Voleibol Infantil - Academia Víctor Pincay',
                'descripcion' => 'Iniciación al voleibol. Técnica de pase, saque y remate básico.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Víctor Pincay Salinas',
                'email_representante' => 'victor.pincay@voleibol.com',
                'telefono_representante' => '0995678901',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 26,
                'cupo_actual' => 0,
                'precio' => 50.00,
                'grupos' => [
                    ['nombre' => 'Grupo Femenino - Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '09:30', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Mixto - Tarde', 'hora_inicio' => '15:30', 'hora_fin' => '17:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Voleibol Competitivo - Cantera Efrén Reyes',
                'descripcion' => 'Entrenamiento avanzado de voleibol. Tácticas de juego y competencia.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Efrén Reyes Vélez',
                'email_representante' => 'efren.reyes@voleibol.com',
                'telefono_representante' => '0996789012',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 22,
                'cupo_actual' => 0,
                'precio' => 65.00,
                'grupos' => [
                    ['nombre' => 'Grupo A - Mañana', 'hora_inicio' => '06:30', 'hora_fin' => '08:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo B - Tarde', 'hora_inicio' => '17:00', 'hora_fin' => '18:30', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],

            // TENIS
            [
                'nombre' => 'Tenis Base - Escuela Rigoberto Cedeño',
                'descripcion' => 'Iniciación al tenis. Técnica de golpes básicos y movimiento en cancha.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Rigoberto Cedeño Flores',
                'email_representante' => 'rigoberto.cedeno@tenis.com',
                'telefono_representante' => '0997890123',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 16,
                'cupo_actual' => 0,
                'precio' => 80.00,
                'grupos' => [
                    ['nombre' => 'Grupo Principiante - Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '09:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Intermedio - Tarde', 'hora_inicio' => '16:00', 'hora_fin' => '17:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Tenis Avanzado - Academia Aurelio Rodríguez',
                'descripcion' => 'Entrenamiento avanzado de tenis. Estrategia y competencia.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Aurelio Rodríguez Sánchez',
                'email_representante' => 'aurelio.rodriguez@tenis.com',
                'telefono_representante' => '0998901234',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 12,
                'cupo_actual' => 0,
                'precio' => 95.00,
                'grupos' => [
                    ['nombre' => 'Grupo Elite - Mañana', 'hora_inicio' => '07:00', 'hora_fin' => '08:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Competitivo - Tarde', 'hora_inicio' => '17:30', 'hora_fin' => '18:30', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],

            // ATLETISMO
            [
                'nombre' => 'Atletismo Base - Cantera Fausto Gruezo',
                'descripcion' => 'Iniciación al atletismo. Carreras, saltos y lanzamientos básicos.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Fausto Gruezo Moreno',
                'email_representante' => 'fausto.gruezo@atletismo.com',
                'telefono_representante' => '0999012345',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 32,
                'cupo_actual' => 0,
                'precio' => 45.00,
                'grupos' => [
                    ['nombre' => 'Grupo Infantil - Mañana', 'hora_inicio' => '07:00', 'hora_fin' => '08:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Juvenil - Tarde', 'hora_inicio' => '16:00', 'hora_fin' => '17:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Atletismo Competitivo - Escuela Jaime Ayoví',
                'descripcion' => 'Entrenamiento competitivo de atletismo. Especialización en disciplinas.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Jaime Ayoví Quiñonez',
                'email_representante' => 'jaime.ayovi@atletismo.com',
                'telefono_representante' => '0990123456',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 24,
                'cupo_actual' => 0,
                'precio' => 60.00,
                'grupos' => [
                    ['nombre' => 'Grupo Velocidad - Mañana', 'hora_inicio' => '06:00', 'hora_fin' => '07:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Resistencia - Tarde', 'hora_inicio' => '17:00', 'hora_fin' => '18:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],

            // ARTES MARCIALES
            [
                'nombre' => 'Karate Infantil - Dojo Wilman Vélez',
                'descripcion' => 'Iniciación al karate. Técnica, disciplina y defensa personal.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Wilman Vélez Ponce',
                'email_representante' => 'wilman.velez@karate.com',
                'telefono_representante' => '0991234568',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 25,
                'cupo_actual' => 0,
                'precio' => 50.00,
                'grupos' => [
                    ['nombre' => 'Grupo Blanco - Mañana', 'hora_inicio' => '09:00', 'hora_fin' => '10:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Amarillo - Tarde', 'hora_inicio' => '15:00', 'hora_fin' => '16:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Taekwondo Competitivo - Academia Patricio Montero',
                'descripcion' => 'Entrenamiento avanzado de taekwondo. Técnica de patadas y competencia.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Patricio Montero Salazar',
                'email_representante' => 'patricio.montero@taekwondo.com',
                'telefono_representante' => '0992345679',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 20,
                'cupo_actual' => 0,
                'precio' => 65.00,
                'grupos' => [
                    ['nombre' => 'Grupo Blanco - Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '09:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '17:00', 'hora_fin' => '18:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Judo Base - Escuela Margarita Cevallos',
                'descripcion' => 'Iniciación al judo. Técnicas de proyección y defensa.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Margarita Cevallos Zambrano',
                'email_representante' => 'margarita.cevallos@judo.com',
                'telefono_representante' => '0993456790',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 18,
                'cupo_actual' => 0,
                'precio' => 55.00,
                'grupos' => [
                    ['nombre' => 'Grupo Infantil - Mañana', 'hora_inicio' => '09:30', 'hora_fin' => '10:30', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Juvenil - Tarde', 'hora_inicio' => '16:30', 'hora_fin' => '17:30', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],

            // GIMNASIA
            [
                'nombre' => 'Gimnasia Artística - Academia Rodrigo Pincay',
                'descripcion' => 'Formación en gimnasia artística. Flexibilidad, fuerza y coordinación.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Rodrigo Pincay Flores',
                'email_representante' => 'rodrigo.pincay@gimnasia.com',
                'telefono_representante' => '0994567891',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 20,
                'cupo_actual' => 0,
                'precio' => 70.00,
                'grupos' => [
                    ['nombre' => 'Grupo Infantil - Mañana', 'hora_inicio' => '08:30', 'hora_fin' => '09:30', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '16:00', 'hora_fin' => '17:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Gimnasia Rítmica - Cantera Ángel Vera',
                'descripcion' => 'Entrenamiento en gimnasia rítmica. Movimiento, ritmo y expresión.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Ángel Vera Rodríguez',
                'email_representante' => 'angel.vera@gimnasia.com',
                'telefono_representante' => '0995678902',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 18,
                'cupo_actual' => 0,
                'precio' => 65.00,
                'grupos' => [
                    ['nombre' => 'Grupo Femenino - Mañana', 'hora_inicio' => '09:00', 'hora_fin' => '10:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '15:30', 'hora_fin' => '16:30', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],

            // OTROS DEPORTES
            [
                'nombre' => 'Badminton - Escuela Ismael Sánchez',
                'descripcion' => 'Iniciación y entrenamiento en badminton. Técnica de golpes y estrategia.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Ismael Sánchez Montoya',
                'email_representante' => 'ismael.sanchez@badminton.com',
                'telefono_representante' => '0996789013',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 16,
                'cupo_actual' => 0,
                'precio' => 55.00,
                'grupos' => [
                    ['nombre' => 'Grupo Principiante - Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '09:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '17:00', 'hora_fin' => '18:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Ciclismo de Montaña - Dirección Gonzalo Pincay',
                'descripcion' => 'Entrenamiento en ciclismo de montaña. Técnica de manejo y resistencia.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Gonzalo Pincay Vélez',
                'email_representante' => 'gonzalo.pincay@ciclismo.com',
                'telefono_representante' => '0997890124',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 22,
                'cupo_actual' => 0,
                'precio' => 75.00,
                'grupos' => [
                    ['nombre' => 'Grupo Principiante - Mañana', 'hora_inicio' => '07:00', 'hora_fin' => '08:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '17:30', 'hora_fin' => '18:30', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Esgrima - Academia Hernán Cedeño',
                'descripcion' => 'Iniciación a la esgrima. Técnica de espada y estrategia de combate.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Hernán Cedeño Rodríguez',
                'email_representante' => 'hernan.cedeno@esgrima.com',
                'telefono_representante' => '0998901235',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 14,
                'cupo_actual' => 0,
                'precio' => 85.00,
                'grupos' => [
                    ['nombre' => 'Grupo Principiante - Mañana', 'hora_inicio' => '09:00', 'hora_fin' => '10:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '16:00', 'hora_fin' => '17:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Natación Sincronizada - Escuela Silvio Flores',
                'descripcion' => 'Entrenamiento en natación sincronizada. Coordinación, ritmo y expresión acuática.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Silvio Flores Montoya',
                'email_representante' => 'silvio.flores@natasync.com',
                'telefono_representante' => '0999012346',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 16,
                'cupo_actual' => 0,
                'precio' => 80.00,
                'grupos' => [
                    ['nombre' => 'Grupo Femenino - Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '09:00', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Avanzado - Tarde', 'hora_inicio' => '15:00', 'hora_fin' => '16:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
            [
                'nombre' => 'Boxeo Base - Academia Roque Pincay',
                'descripcion' => 'Iniciación al boxeo. Técnica de puños, defensa y acondicionamiento físico.',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'representante' => 'Roque Pincay Salinas',
                'email_representante' => 'roque.pincay@boxeo.com',
                'telefono_representante' => '0990123457',
                'tipo' => 'regular',
                'estado' => 'activo',
                'cupo_maximo' => 20,
                'cupo_actual' => 0,
                'precio' => 60.00,
                'grupos' => [
                    ['nombre' => 'Grupo Infantil - Mañana', 'hora_inicio' => '08:30', 'hora_fin' => '09:30', 'dias_semana' => ['lunes', 'miércoles', 'viernes']],
                    ['nombre' => 'Grupo Juvenil - Tarde', 'hora_inicio' => '17:00', 'hora_fin' => '18:00', 'dias_semana' => ['martes', 'jueves', 'sábado']],
                ]
            ],
        ];

        foreach ($cursos as $cursoData) {
            $grupos = $cursoData['grupos'];
            unset($cursoData['grupos']);

            $curso = Curso::create([
                ...$cursoData,
                'slug' => Str::slug($cursoData['nombre']),
            ]);

            foreach ($grupos as $grupo) {
                GrupoCurso::create([
                    'id_curso' => $curso->id_curso,
                    'nombre' => $grupo['nombre'],
                    'cupo_maximo' => 15,
                    'cupo_actual' => 0,
                    'hora_inicio' => $grupo['hora_inicio'],
                    'hora_fin' => $grupo['hora_fin'],
                    'dias_semana' => json_encode($grupo['dias_semana']),
                    'estado' => 'activo',
                ]);
            }
        }
    }
}
