<?php
namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /**
     * Registrar nuevo usuario como TUTOR (rol por defecto)
     * 
     * El registro público siempre crea un usuario con rol TUTOR.
     * El tutor puede luego registrar a sus hijos (participantes) e inscribirlos en cursos.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Validación simplificada para registro de tutores
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|min:2|max:100',
                'apellido' => 'required|string|min:2|max:100',
                'cedula' => 'nullable|string|size:10|unique:usuarios,cedula',
                'email' => 'required|email|unique:usuarios,email',
                'password' => 'required|string|min:8|confirmed',
                'telefono' => 'nullable|string|max:15',
                'direccion' => 'nullable|string|max:255',
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.min' => 'El nombre debe tener al menos 2 caracteres',
                'apellido.required' => 'El apellido es obligatorio',
                'apellido.min' => 'El apellido debe tener al menos 2 caracteres',
                'cedula.size' => 'La cédula debe tener 10 dígitos',
                'cedula.unique' => 'Esta cédula ya está registrada',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email no es válido',
                'email.unique' => 'Este email ya está registrado',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.confirmed' => 'Las contraseñas no coinciden',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validar cédula ecuatoriana si se proporciona
            if ($request->cedula && !$this->validarCedulaEcuatoriana($request->cedula)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cédula ecuatoriana no es válida',
                    'errors' => ['cedula' => ['La cédula ecuatoriana no es válida']]
                ], 422);
            }

            DB::beginTransaction();

            // Siempre asignar rol TUTOR para registro público
            $rolTutor = Rol::where('slug', 'tutor')->first();
            if (!$rolTutor) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error de configuración: rol Tutor no encontrado'
                ], 500);
            }

            // Crear usuario con rol TUTOR (ya no se crea registro separado en tabla tutores)
            $usuario = Usuario::create([
                'id_rol' => $rolTutor->id_rol,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'cedula' => $request->cedula,
                'status' => 'activo'
            ]);

            // Generar token para login automático
            $deviceName = $request->device_name ?? $request->userAgent() ?? 'web-browser';
            $token = $usuario->createToken($deviceName)->plainTextToken;

            // Cargar relaciones
            $usuario->load('rol');

            DB::commit();

            Log::info('Nuevo tutor registrado', [
                'usuario_id' => $usuario->id_usuario,
                'email' => $usuario->email
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Registro exitoso! Ya puedes inscribir a tus hijos en nuestros cursos.',
                'data' => [
                    'user' => [
                        'id_usuario' => $usuario->id_usuario,
                        'nombre' => $usuario->nombre,
                        'apellido' => $usuario->apellido,
                        'email' => $usuario->email,
                        'telefono' => $usuario->telefono,
                        'status' => $usuario->status,
                        'rol' => [
                            'id_rol' => $rolTutor->id_rol,
                            'nombre' => $rolTutor->nombre,
                            'slug' => $rolTutor->slug,
                        ],
                    ],
                ],
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar tutor: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al registrar. Intenta de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validar cédula ecuatoriana
     * 
     * @param string $cedula
     * @return bool
     */
    private function validarCedulaEcuatoriana($cedula)
    {
        // Verificar que tenga 10 dígitos
        if (!preg_match('/^\d{10}$/', $cedula)) {
            return false;
        }

        // Verificar provincia (01-24)
        $provincia = intval(substr($cedula, 0, 2));
        if ($provincia < 1 || $provincia > 24) {
            return false;
        }

        // Tercer dígito debe ser menor a 6 (personas naturales)
        $tercerDigito = intval($cedula[2]);
        if ($tercerDigito > 5) {
            return false;
        }

        // Algoritmo de validación del dígito verificador
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $valor = intval($cedula[$i]) * $coeficientes[$i];
            if ($valor >= 10) {
                $valor -= 9;
            }
            $suma += $valor;
        }

        $digitoVerificador = intval($cedula[9]);
        $resultado = $suma % 10 === 0 ? 0 : 10 - ($suma % 10);

        return $resultado === $digitoVerificador;
    }

    /**
     * Verificar email
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $existe = Usuario::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'disponible' => !$existe,
            'message' => $existe ? 'El email ya está en uso' : 'Email disponible'
        ]);
    }

    /**
     * Enviar código de verificación
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enviarCodigoVerificacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:usuarios,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        // Generar código de 6 dígitos
        $codigo = random_int(100000, 999999);

        // Guardar código temporalmente (10 minutos de expiración)
        cache()->put('verification_code_' . $usuario->id_usuario, $codigo, now()->addMinutes(10));

        // Aquí puedes enviar el código por email
        // Mail::to($usuario->email)->send(new VerificationCodeMail($codigo));

        Log::info('Código de verificación generado', [
            'usuario_id' => $usuario->id_usuario,
            'email' => $usuario->email,
            'codigo' => $codigo // Solo en desarrollo, remover en producción
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Código de verificación enviado al correo electrónico',
            // 'codigo' => $codigo // Solo para testing, remover en producción
        ]);
    }

    /**
     * Verificar código
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarCodigo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:usuarios,email',
            'codigo' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();
        $codigoGuardado = cache()->get('verification_code_' . $usuario->id_usuario);

        if (!$codigoGuardado || $codigoGuardado != $request->codigo) {
            return response()->json([
                'success' => false,
                'message' => 'Código de verificación inválido o expirado'
            ], 400);
        }

        // Marcar email como verificado
        $usuario->update(['email_verified_at' => now()]);

        // Eliminar código del cache
        cache()->forget('verification_code_' . $usuario->id_usuario);

        Log::info('Email verificado', [
            'usuario_id' => $usuario->id_usuario,
            'email' => $usuario->email
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verificado exitosamente'
        ]);
    }
}