<?php
namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogoutController extends Controller
{
    /**
     * Logout de usuario
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user) {
                Log::info('Cierre de sesión del usuario', ['user_id' => $user->id_usuario]);
                
                // Revocar el token actual
                $user->currentAccessToken()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error durante el logout', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error durante el logout'
            ], 500);
        }
    }
}
