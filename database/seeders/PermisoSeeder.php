<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * Permisos para Sistema de Cursos Vacacionales
 * 
 * Módulos:
 * - dashboard: Estadísticas generales
 * - cursos: Gestión de cursos vacacionales
 * - grupos: Grupos dentro de cursos
 * - inscripciones: Inscripciones de deportistas
 * - deportistas: Participantes (niños/jóvenes)
 * - categorias: Categorías por edad
 * - tutores: Padres/representantes
 * - instructores: Profesores de cursos
 * - facturas: Facturación
 * - pagos: Registro de pagos
 * - usuarios: Gestión de usuarios del sistema
 * - roles: Gestión de roles
 * - configuracion: Configuración del sistema
 */
class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // ========== DASHBOARD ==========
            ['nombre' => 'Ver Dashboard', 'slug' => 'dashboard.ver', 'descripcion' => 'Ver estadísticas del dashboard', 'modulo' => 'dashboard'],
            
            // ========== CURSOS ==========
            ['nombre' => 'Ver Cursos', 'slug' => 'cursos.ver', 'descripcion' => 'Ver listado de cursos', 'modulo' => 'cursos'],
            ['nombre' => 'Crear Cursos', 'slug' => 'cursos.crear', 'descripcion' => 'Crear nuevos cursos', 'modulo' => 'cursos'],
            ['nombre' => 'Editar Cursos', 'slug' => 'cursos.editar', 'descripcion' => 'Editar cursos existentes', 'modulo' => 'cursos'],
            ['nombre' => 'Eliminar Cursos', 'slug' => 'cursos.eliminar', 'descripcion' => 'Eliminar cursos', 'modulo' => 'cursos'],
            
            // ========== GRUPOS ==========
            ['nombre' => 'Ver Grupos', 'slug' => 'grupos.ver', 'descripcion' => 'Ver grupos de cursos', 'modulo' => 'grupos'],
            ['nombre' => 'Crear Grupos', 'slug' => 'grupos.crear', 'descripcion' => 'Crear grupos', 'modulo' => 'grupos'],
            ['nombre' => 'Editar Grupos', 'slug' => 'grupos.editar', 'descripcion' => 'Editar grupos', 'modulo' => 'grupos'],
            ['nombre' => 'Eliminar Grupos', 'slug' => 'grupos.eliminar', 'descripcion' => 'Eliminar grupos', 'modulo' => 'grupos'],
            ['nombre' => 'Asignar Instructores', 'slug' => 'grupos.asignar-instructor', 'descripcion' => 'Asignar instructores a grupos', 'modulo' => 'grupos'],
            
            // ========== ASISTENCIAS ==========
            ['nombre' => 'Ver Asistencias', 'slug' => 'asistencias.ver', 'descripcion' => 'Ver registro de asistencias', 'modulo' => 'asistencias'],
            ['nombre' => 'Registrar Asistencias', 'slug' => 'asistencias.registrar', 'descripcion' => 'Registrar asistencia de deportistas', 'modulo' => 'asistencias'],
            ['nombre' => 'Exportar Asistencias', 'slug' => 'asistencias.exportar', 'descripcion' => 'Exportar informes de asistencia', 'modulo' => 'asistencias'],
            
            // ========== INSCRIPCIONES ==========
            ['nombre' => 'Ver Inscripciones', 'slug' => 'inscripciones.ver', 'descripcion' => 'Ver inscripciones', 'modulo' => 'inscripciones'],
            ['nombre' => 'Crear Inscripciones', 'slug' => 'inscripciones.crear', 'descripcion' => 'Inscribir deportistas', 'modulo' => 'inscripciones'],
            ['nombre' => 'Editar Inscripciones', 'slug' => 'inscripciones.editar', 'descripcion' => 'Editar inscripciones', 'modulo' => 'inscripciones'],
            ['nombre' => 'Cancelar Inscripciones', 'slug' => 'inscripciones.cancelar', 'descripcion' => 'Cancelar inscripciones', 'modulo' => 'inscripciones'],
            ['nombre' => 'Calificar Inscripciones', 'slug' => 'inscripciones.calificar', 'descripcion' => 'Calificar participantes', 'modulo' => 'inscripciones'],
            ['nombre' => 'Ver Mis Inscripciones', 'slug' => 'inscripciones.ver-propias', 'descripcion' => 'Ver inscripciones propias', 'modulo' => 'inscripciones'],
            
            // ========== DEPORTISTAS ==========
            ['nombre' => 'Ver Deportistas', 'slug' => 'deportistas.ver', 'descripcion' => 'Ver todos los deportistas', 'modulo' => 'deportistas'],
            ['nombre' => 'Crear Deportistas', 'slug' => 'deportistas.crear', 'descripcion' => 'Registrar deportistas', 'modulo' => 'deportistas'],
            ['nombre' => 'Editar Deportistas', 'slug' => 'deportistas.editar', 'descripcion' => 'Editar deportistas', 'modulo' => 'deportistas'],
            ['nombre' => 'Eliminar Deportistas', 'slug' => 'deportistas.eliminar', 'descripcion' => 'Eliminar deportistas', 'modulo' => 'deportistas'],
            ['nombre' => 'Ver Mis Participantes', 'slug' => 'deportistas.ver-propios', 'descripcion' => 'Ver solo mis hijos/participantes', 'modulo' => 'deportistas'],
            
            // ========== CATEGORÍAS ==========
            ['nombre' => 'Ver Categorías', 'slug' => 'categorias.ver', 'descripcion' => 'Ver categorías', 'modulo' => 'categorias'],
            ['nombre' => 'Gestionar Categorías', 'slug' => 'categorias.gestionar', 'descripcion' => 'Crear/editar/eliminar categorías', 'modulo' => 'categorias'],
            
            // ========== TUTORES ==========
            ['nombre' => 'Ver Tutores', 'slug' => 'tutores.ver', 'descripcion' => 'Ver todos los tutores', 'modulo' => 'tutores'],
            ['nombre' => 'Crear Tutores', 'slug' => 'tutores.crear', 'descripcion' => 'Registrar tutores', 'modulo' => 'tutores'],
            ['nombre' => 'Editar Tutores', 'slug' => 'tutores.editar', 'descripcion' => 'Editar tutores', 'modulo' => 'tutores'],
            ['nombre' => 'Eliminar Tutores', 'slug' => 'tutores.eliminar', 'descripcion' => 'Eliminar tutores', 'modulo' => 'tutores'],
            
            // ========== INSTRUCTORES ==========
            ['nombre' => 'Ver Instructores', 'slug' => 'instructores.ver', 'descripcion' => 'Ver instructores', 'modulo' => 'instructores'],
            ['nombre' => 'Crear Instructores', 'slug' => 'instructores.crear', 'descripcion' => 'Registrar instructores', 'modulo' => 'instructores'],
            ['nombre' => 'Editar Instructores', 'slug' => 'instructores.editar', 'descripcion' => 'Editar instructores', 'modulo' => 'instructores'],
            ['nombre' => 'Eliminar Instructores', 'slug' => 'instructores.eliminar', 'descripcion' => 'Eliminar instructores', 'modulo' => 'instructores'],
            ['nombre' => 'Ver Mis Grupos', 'slug' => 'instructores.ver-grupos', 'descripcion' => 'Ver grupos asignados', 'modulo' => 'instructores'],
            
            // ========== FACTURAS ==========
            ['nombre' => 'Ver Facturas', 'slug' => 'facturas.ver', 'descripcion' => 'Ver todas las facturas', 'modulo' => 'facturas'],
            ['nombre' => 'Crear Facturas', 'slug' => 'facturas.crear', 'descripcion' => 'Generar facturas', 'modulo' => 'facturas'],
            ['nombre' => 'Editar Facturas', 'slug' => 'facturas.editar', 'descripcion' => 'Editar facturas', 'modulo' => 'facturas'],
            ['nombre' => 'Anular Facturas', 'slug' => 'facturas.anular', 'descripcion' => 'Anular facturas', 'modulo' => 'facturas'],
            ['nombre' => 'Ver Mis Facturas', 'slug' => 'facturas.ver-propias', 'descripcion' => 'Ver facturas propias', 'modulo' => 'facturas'],
            
            // ========== PAGOS ==========
            ['nombre' => 'Ver Pagos', 'slug' => 'pagos.ver', 'descripcion' => 'Ver todos los pagos', 'modulo' => 'pagos'],
            ['nombre' => 'Registrar Pagos', 'slug' => 'pagos.registrar', 'descripcion' => 'Registrar pagos', 'modulo' => 'pagos'],
            ['nombre' => 'Verificar Pagos', 'slug' => 'pagos.verificar', 'descripcion' => 'Verificar/aprobar pagos', 'modulo' => 'pagos'],
            ['nombre' => 'Ver Mis Pagos', 'slug' => 'pagos.ver-propios', 'descripcion' => 'Ver pagos propios', 'modulo' => 'pagos'],
            
            // ========== ASISTENCIAS ==========
            ['nombre' => 'Ver Asistencias', 'slug' => 'asistencias.ver', 'descripcion' => 'Ver asistencias de grupos', 'modulo' => 'asistencias'],
            ['nombre' => 'Registrar Asistencias', 'slug' => 'asistencias.registrar', 'descripcion' => 'Registrar asistencias', 'modulo' => 'asistencias'],
            ['nombre' => 'Exportar Asistencias', 'slug' => 'asistencias.exportar', 'descripcion' => 'Exportar reportes de asistencia', 'modulo' => 'asistencias'],
            
            // ========== USUARIOS ==========
            ['nombre' => 'Ver Usuarios', 'slug' => 'usuarios.ver', 'descripcion' => 'Ver usuarios del sistema', 'modulo' => 'usuarios'],
            ['nombre' => 'Crear Usuarios', 'slug' => 'usuarios.crear', 'descripcion' => 'Crear usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'Editar Usuarios', 'slug' => 'usuarios.editar', 'descripcion' => 'Editar usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'Eliminar Usuarios', 'slug' => 'usuarios.eliminar', 'descripcion' => 'Eliminar usuarios', 'modulo' => 'usuarios'],
            
            // ========== ROLES ==========
            ['nombre' => 'Ver Roles', 'slug' => 'roles.ver', 'descripcion' => 'Ver roles', 'modulo' => 'roles'],
            ['nombre' => 'Gestionar Roles', 'slug' => 'roles.gestionar', 'descripcion' => 'Crear/editar roles y permisos', 'modulo' => 'roles'],
            
            // ========== CONFIGURACIÓN ==========
            ['nombre' => 'Ver Configuración', 'slug' => 'configuracion.ver', 'descripcion' => 'Ver configuración', 'modulo' => 'configuracion'],
            ['nombre' => 'Editar Configuración', 'slug' => 'configuracion.editar', 'descripcion' => 'Modificar configuración', 'modulo' => 'configuracion'],
            
            // ========== REPORTES ==========
            ['nombre' => 'Ver Reportes', 'slug' => 'reportes.ver', 'descripcion' => 'Ver reportes y estadísticas', 'modulo' => 'reportes'],
            ['nombre' => 'Exportar Reportes', 'slug' => 'reportes.exportar', 'descripcion' => 'Exportar reportes', 'modulo' => 'reportes'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['slug' => $permiso['slug']],
                $permiso
            );
        }

        // Asignar permisos a roles
        $this->asignarPermisosARoles();
    }

    private function asignarPermisosARoles(): void
    {
        // ========== ADMINISTRADOR: TODOS LOS PERMISOS ==========
        $admin = Rol::where('slug', 'administrador')->first();
        if ($admin) {
            $todosPermisos = Permiso::pluck('id_permiso')->toArray();
            $admin->permisos()->sync($todosPermisos);
        }

        // ========== TUTOR: Gestiona sus hijos e inscripciones ==========
        $tutor = Rol::where('slug', 'tutor')->first();
        if ($tutor) {
            $permisosTutor = Permiso::whereIn('slug', [
                'dashboard.ver',
                // Puede ver cursos disponibles
                'cursos.ver',
                'grupos.ver',
                // Gestiona sus propios participantes (hijos)
                'deportistas.ver-propios',
                'deportistas.crear',
                'deportistas.editar',
                // Puede inscribir a sus hijos
                'inscripciones.ver-propias',
                'inscripciones.crear',
                'inscripciones.cancelar',
                // Ve sus facturas y puede registrar pagos
                'facturas.ver-propias',
                'pagos.ver-propios',
                'pagos.registrar',
                // Ve categorías
                'categorias.ver',
            ])->pluck('id_permiso')->toArray();
            
            $tutor->permisos()->sync($permisosTutor);
        }

        // ========== INSTRUCTOR: Ve sus grupos y califica ==========
        $instructor = Rol::where('slug', 'instructor')->first();
        if ($instructor) {
            $permisosInstructor = Permiso::whereIn('slug', [
                'dashboard.ver',
                // Ve cursos y grupos
                'cursos.ver',
                'grupos.ver',
                // Ve sus grupos asignados
                'instructores.ver-grupos',
                // Ve inscripciones de sus grupos y puede calificar
                'inscripciones.ver',
                'inscripciones.calificar',
                // Ve deportistas de sus grupos
                'deportistas.ver',
                // Ve categorías
                'categorias.ver',
                // Asistencias
                'asistencias.ver',
                'asistencias.registrar',
                'asistencias.exportar',
                // Asistencias
                'asistencias.ver',
                'asistencias.registrar',
                'asistencias.exportar',
            ])->pluck('id_permiso')->toArray();
            
            $instructor->permisos()->sync($permisosInstructor);
        }
    }
}
