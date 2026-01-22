<?php
namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    /**
     * Crear usuarios de prueba para el Sistema de Cursos Vacacionales
     */
    public function run(): void
    {
        $passwordDefault = 'Admin123!';

        $usuarios = [
            [
                'rol_slug' => 'administrador',
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'email' => 'admin@cursosvacacionales.com',
                'telefono' => '0991234567',
            ],
            [
                'rol_slug' => 'instructor',
                'nombre' => 'María',
                'apellido' => 'Instructora',
                'email' => 'instructor@cursosvacacionales.com',
                'telefono' => '0991234569',
            ],
            [
                'rol_slug' => 'tutor',
                'nombre' => 'Ana',
                'apellido' => 'Representante',
                'email' => 'tutor@cursosvacacionales.com',
                'telefono' => '0991234570',
            ],
        ];

        $this->command->info('');
        $this->command->info('👤 Creando usuarios de prueba...');
        $this->command->info('');

        foreach ($usuarios as $userData) {
            $rol = Rol::where('slug', $userData['rol_slug'])->first();

            if ($rol) {
                Usuario::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'id_rol' => $rol->id_rol,
                        'nombre' => $userData['nombre'],
                        'apellido' => $userData['apellido'],
                        'telefono' => $userData['telefono'],
                        'password' => Hash::make($passwordDefault),
                        'status' => 'activo',
                        'email_verified_at' => now()
                    ]
                );

                $this->command->info("✅ {$rol->nombre}: {$userData['email']}");
            }
        }

        $this->command->info('');
        $this->command->info('🔒 Contraseña para todos: ' . $passwordDefault);
    }
}
