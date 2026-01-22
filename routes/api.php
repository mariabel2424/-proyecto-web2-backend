<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\PasswordResetController;

// Usuarios
use App\Http\Controllers\API\Usuarios\UsuarioController;
use App\Http\Controllers\API\Usuarios\RolController;
use App\Http\Controllers\API\Usuarios\PermisoController;

// Cursos Vacacionales
use App\Http\Controllers\API\Cursos\CursoController;
use App\Http\Controllers\API\Cursos\InscripcionCursoController;
use App\Http\Controllers\API\Cursos\GrupoCursoController;
use App\Http\Controllers\API\Cursos\AsistenciaController;

// Deportistas y Tutores
use App\Http\Controllers\API\Deportistas\DeportistaController;
use App\Http\Controllers\API\Deportistas\CategoriaController;
use App\Http\Controllers\API\Deportistas\TutorController;
use App\Http\Controllers\API\Deportistas\InstructorController;

// Finanzas
use App\Http\Controllers\API\Finanzas\FacturaController;
use App\Http\Controllers\API\Finanzas\PagoController;

// Sistema
use App\Http\Controllers\API\Sistema\DashboardController;
use App\Http\Controllers\API\Sistema\ConfiguracionController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('roles', [RolController::class, 'all']);
Route::post('register', [RegisterController::class, 'register']);

Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('verificar-email', [RegisterController::class, 'verificarEmail']);
    Route::post('enviar-codigo', [RegisterController::class, 'enviarCodigoVerificacion']);
    Route::post('verificar-codigo', [RegisterController::class, 'verificarCodigo']);
    Route::post('solicitar-reset', [PasswordResetController::class, 'solicitarReset']);
    Route::post('verificar-token', [PasswordResetController::class, 'verificarToken']);
});

// Cursos públicos (para landing page)
Route::get('cursos-abiertos', [CursoController::class, 'cursosAbiertos']);
Route::get('cursos/{id}/publico', [CursoController::class, 'showPublico']);
Route::get('cursos/{id}/grupos-publico', [GrupoCursoController::class, 'gruposPublico']);
Route::get('categorias', [CategoriaController::class, 'index']);

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // ========== AUTH ==========
    Route::prefix('auth')->group(function () {
        Route::post('logout', [LogoutController::class, 'logout']);
        Route::get('me', [LoginController::class, 'me']);
        Route::post('cambiar-password', [PasswordResetController::class, 'cambiarPassword']);
    });
    
    // ========== DASHBOARD (todos los autenticados) ==========
    Route::prefix('dashboard')->group(function () {
        Route::get('estadisticas', [DashboardController::class, 'estadisticasGenerales']);
        Route::get('cursos-activos', [DashboardController::class, 'cursosActivos']);
        Route::get('inscripciones-recientes', [DashboardController::class, 'inscripcionesRecientes']);
        Route::get('participantes', [DashboardController::class, 'participantes']);
        Route::get('facturacion-mensual', [DashboardController::class, 'facturacionMensual'])
            ->middleware('role:administrador');
        Route::get('mis-datos', [DashboardController::class, 'misDatos']);
        
        // Reportes con filtro de fechas (solo admin)
        Route::get('reporte/cursos', [DashboardController::class, 'reporteCursos'])
            ->middleware('role:administrador');
        Route::get('reporte/finanzas', [DashboardController::class, 'reporteFinanzas'])
            ->middleware('role:administrador');
        Route::get('reporte/participantes', [DashboardController::class, 'reporteParticipantes'])
            ->middleware('role:administrador');
    });
    
    // ========== CURSOS VACACIONALES ==========
    // Ver cursos: todos pueden ver
    Route::get('cursos', [CursoController::class, 'index']);
    Route::get('cursos/{id}', [CursoController::class, 'show']);
    Route::get('cursos/{id}/participantes', [CursoController::class, 'participantes'])
        ->middleware('permission:inscripciones.ver');
    Route::get('cursos/{id}/grupos-disponibles', [GrupoCursoController::class, 'gruposDisponibles']);
    Route::get('cursos-disponibles', [InscripcionCursoController::class, 'cursosDisponibles']);
    
    // CRUD cursos: solo admin
    Route::post('cursos', [CursoController::class, 'store'])
        ->middleware('permission:cursos.crear');
    Route::put('cursos/{id}', [CursoController::class, 'update'])
        ->middleware('permission:cursos.editar');
    Route::delete('cursos/{id}', [CursoController::class, 'destroy'])
        ->middleware('permission:cursos.eliminar');
    
    // ========== GRUPOS DE CURSO ==========
    Route::get('grupos-curso', [GrupoCursoController::class, 'index']);
    Route::get('grupos-curso/{id}', [GrupoCursoController::class, 'show']);
    Route::get('grupos-curso/{id}/deportistas', [GrupoCursoController::class, 'deportistas']);
    Route::get('grupos-curso/{id}/instructores', [GrupoCursoController::class, 'instructores']);
    
    // CRUD grupos: solo admin
    Route::post('grupos-curso', [GrupoCursoController::class, 'store'])
        ->middleware('permission:grupos.crear');
    Route::put('grupos-curso/{id}', [GrupoCursoController::class, 'update'])
        ->middleware('permission:grupos.editar');
    Route::delete('grupos-curso/{id}', [GrupoCursoController::class, 'destroy'])
        ->middleware('permission:grupos.eliminar');
    Route::post('grupos-curso/{id}/asignar-instructor', [GrupoCursoController::class, 'asignarInstructor'])
        ->middleware('permission:grupos.asignar-instructor');
    Route::post('grupos-curso/{id}/quitar-instructor', [GrupoCursoController::class, 'quitarInstructor'])
        ->middleware('permission:grupos.asignar-instructor');
    Route::patch('grupos-curso/{id}/cambiar-estado', [GrupoCursoController::class, 'cambiarEstado'])
        ->middleware('permission:grupos.editar');
    
    // ========== ASISTENCIAS ==========
    Route::get('grupos-curso/{id}/asistencias', [AsistenciaController::class, 'index']);
    Route::post('grupos-curso/{id}/asistencias', [AsistenciaController::class, 'registrar']);
    Route::get('grupos-curso/{id}/asistencias/resumen', [AsistenciaController::class, 'resumen']);
    Route::get('grupos-curso/{id}/asistencias/reporte', [AsistenciaController::class, 'reporte']);
    Route::get('grupos-curso/{id}/asistencias/exportar', [AsistenciaController::class, 'exportar']);
    
    // ========== INSCRIPCIONES ==========
    // Ver todas: admin e instructor
    Route::get('inscripciones-curso', [InscripcionCursoController::class, 'index'])
        ->middleware('permission:inscripciones.ver');
    Route::get('inscripciones-curso/{id}', [InscripcionCursoController::class, 'show']);
    
    // Crear inscripción: admin y tutor
    Route::post('inscripciones-curso', [InscripcionCursoController::class, 'store'])
        ->middleware('permission:inscripciones.crear');
    
    // Editar/eliminar: solo admin
    Route::put('inscripciones-curso/{id}', [InscripcionCursoController::class, 'update'])
        ->middleware('permission:inscripciones.editar');
    Route::delete('inscripciones-curso/{id}', [InscripcionCursoController::class, 'destroy'])
        ->middleware('role:administrador');
    
    // Calificar: admin e instructor
    Route::post('inscripciones-curso/{id}/calificar', [InscripcionCursoController::class, 'calificar'])
        ->middleware('permission:inscripciones.calificar');
    
    // Cancelar: admin y tutor (sus propias)
    Route::patch('inscripciones-curso/{id}/cancelar', [InscripcionCursoController::class, 'cancelar'])
        ->middleware('permission:inscripciones.cancelar');
    
    // Generar factura: admin
    Route::post('inscripciones-curso/{id}/generar-factura', [InscripcionCursoController::class, 'generarFactura'])
        ->middleware('permission:facturas.crear');
    
    // Inscripciones de un deportista específico
    Route::get('inscripciones-curso/deportista/{id}', [InscripcionCursoController::class, 'inscripcionesDeportista']);
    
    // Mis inscripciones (tutor)
    Route::get('mis-inscripciones', [InscripcionCursoController::class, 'misInscripciones']);
    
    // ========== DEPORTISTAS ==========
    // Ver todos: admin e instructor
    Route::get('deportistas', [DeportistaController::class, 'index'])
        ->middleware('permission:deportistas.ver');
    Route::get('deportistas/activos/listar', [DeportistaController::class, 'activos']);
    Route::get('deportistas/{id}', [DeportistaController::class, 'show']);
    
    // CRUD: admin
    Route::post('deportistas', [DeportistaController::class, 'store'])
        ->middleware('permission:deportistas.crear');
    Route::put('deportistas/{id}', [DeportistaController::class, 'update'])
        ->middleware('permission:deportistas.editar');
    Route::delete('deportistas/{id}', [DeportistaController::class, 'destroy'])
        ->middleware('permission:deportistas.eliminar');
    
    // Mis participantes (tutor)
    Route::get('mis-participantes', [DeportistaController::class, 'misParticipantes']);
    Route::post('mis-participantes', [DeportistaController::class, 'crearMiParticipante']);
    Route::put('mis-participantes/{id}', [DeportistaController::class, 'actualizarMiParticipante']);
    Route::delete('mis-participantes/{id}', [DeportistaController::class, 'eliminarMiParticipante']);
    
    // ========== CATEGORÍAS ==========
    Route::get('categorias', [CategoriaController::class, 'index']);
    Route::get('categorias/{id}', [CategoriaController::class, 'show']);
    
    Route::post('categorias', [CategoriaController::class, 'store'])
        ->middleware('permission:categorias.crear');
    Route::put('categorias/{id}', [CategoriaController::class, 'update'])
        ->middleware('permission:categorias.editar');
    Route::delete('categorias/{id}', [CategoriaController::class, 'destroy'])
        ->middleware('permission:categorias.eliminar');
    
    // ========== TUTORES ==========
    Route::middleware('permission:tutores.ver')->group(function () {
        Route::get('tutores', [TutorController::class, 'index']);
        Route::get('tutores/{id}', [TutorController::class, 'show']);
        Route::get('tutores/{id}/deportistas', [TutorController::class, 'deportistas']);
        Route::get('tutores/{id}/facturas', [FacturaController::class, 'facturasPorTutor']);
    });
    
    Route::post('tutores', [TutorController::class, 'store'])
        ->middleware('permission:tutores.crear');
    Route::put('tutores/{id}', [TutorController::class, 'update'])
        ->middleware('permission:tutores.editar');
    Route::delete('tutores/{id}', [TutorController::class, 'destroy'])
        ->middleware('permission:tutores.eliminar');
    
    // ========== INSTRUCTORES ==========
    Route::get('instructores/disponibles/listar', [InstructorController::class, 'disponibles']);
    Route::get('instructores', [InstructorController::class, 'index'])
        ->middleware('permission:instructores.ver');
    Route::get('instructores/{id}', [InstructorController::class, 'show']);
    Route::get('instructores/{id}/grupos', [InstructorController::class, 'grupos']);
    
    Route::post('instructores', [InstructorController::class, 'store'])
        ->middleware('permission:instructores.crear');
    Route::put('instructores/{id}', [InstructorController::class, 'update'])
        ->middleware('permission:instructores.editar');
    Route::delete('instructores/{id}', [InstructorController::class, 'destroy'])
        ->middleware('permission:instructores.eliminar');
    
    // Mis grupos (instructor)
    Route::get('mis-grupos', [InstructorController::class, 'misGrupos']);
    
    // ========== FINANZAS - FACTURAS ==========
    // Ver todas: admin
    Route::get('facturas', [FacturaController::class, 'index'])
        ->middleware('permission:facturas.ver');
    Route::get('facturas/reporte/facturacion', [FacturaController::class, 'reporteFacturacion'])
        ->middleware('permission:facturas.reportes');
    Route::get('facturas/{id}', [FacturaController::class, 'show']);
    Route::get('facturas/{id}/pdf', [FacturaController::class, 'datosParaPdf']);
    
    Route::post('facturas', [FacturaController::class, 'store'])
        ->middleware('permission:facturas.crear');
    Route::put('facturas/{id}', [FacturaController::class, 'update'])
        ->middleware('permission:facturas.editar');
    Route::delete('facturas/{id}', [FacturaController::class, 'destroy'])
        ->middleware('permission:facturas.anular');
    Route::post('facturas/{id}/registrar-pago', [FacturaController::class, 'registrarPago'])
        ->middleware('permission:pagos.registrar');
    
    // Mis facturas (tutor)
    Route::get('mis-facturas', [FacturaController::class, 'misFacturas']);
    
    // ========== FINANZAS - PAGOS ==========
    Route::get('pagos', [PagoController::class, 'index'])
        ->middleware('permission:pagos.ver');
    Route::get('pagos/{id}', [PagoController::class, 'show']);
    Route::put('pagos/{id}', [PagoController::class, 'update'])
        ->middleware('permission:pagos.ver');
    Route::delete('pagos/{id}', [PagoController::class, 'destroy'])
        ->middleware('role:administrador');
    Route::post('pagos/{id}/verificar', [PagoController::class, 'verificar'])
        ->middleware('permission:pagos.verificar');
    
    // Mis pagos (tutor)
    Route::get('mis-pagos', [PagoController::class, 'misPagos']);
    Route::post('mis-pagos', [PagoController::class, 'realizarPago']);
    
    // ========== SISTEMA - Solo Admin ==========
    Route::middleware('role:administrador')->group(function () {
        // Usuarios
        Route::apiResource('usuarios', UsuarioController::class);
        Route::put('usuarios/{id}/estado', [UsuarioController::class, 'cambiarEstado']);
        
        // Roles
        Route::apiResource('rols', RolController::class);
        
        // Permisos
        Route::get('permisos/modulos', [PermisoController::class, 'modulos']);
        Route::apiResource('permisos', PermisoController::class);
        
        // Configuración
        Route::apiResource('configuraciones', ConfiguracionController::class);
    });
});
