<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeders para el Sistema de Cursos Vacacionales
     */
    public function run(): void
    {
        $this->call([
            PermisoSeeder::class,
            RolSeeder::class,
            CategoriaSeeder::class,
            ConfiguracionSeeder::class,
            UsuarioAdminSeeder::class,
            CursosFutbolCostaSeeder::class,
            CursosDeportesVariadosSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 ¡Sistema de Cursos Vacacionales inicializado!');
        $this->command->info('');
        $this->command->info('Datos creados:');
        $this->command->info('✅ 5 Roles (Administrador, Coordinador, Instructor, Tutor, Secretaria)');
        $this->command->info('✅ Permisos del sistema');
        $this->command->info('✅ Categorías por edades');
        $this->command->info('✅ Configuraciones del sistema');
        $this->command->info('✅ 1 Usuario Administrador');
        $this->command->info('✅ 20 Cursos de Fútbol con instructores de la costa ecuatoriana');
        $this->command->info('✅ 30 Cursos variados (Natación, Baloncesto, Voleibol, Tenis, Atletismo, Artes Marciales, Gimnasia, etc.)');
        $this->command->info('✅ 60 Grupos de cursos con horarios variados');
        $this->command->info('');
        $this->command->info('Credenciales de acceso:');
        $this->command->info('📧 Email: admin@cursosvacacionales.com');
        $this->command->info('🔒 Password: Admin123!');
    }
}
