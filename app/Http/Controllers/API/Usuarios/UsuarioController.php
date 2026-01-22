<?php
namespace App\Http\Controllers\API\Usuarios;
use App\Http\Controllers\Controller;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::with('rol');

        // Filtros opcionales - usar filled() en lugar de has() para ignorar valores vacíos
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_rol')) {
            $query->where('id_rol', $request->id_rol);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->input('sort_by', 'id_usuario');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $usuarios = $query->paginate($perPage);
        
        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_rol' => 'required|exists:rols,id_rol',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'cedula' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048'
        ]);

        $data = $request->except('password', 'avatar');
        $data['password'] = Hash::make($request->password);

        // Manejar avatar
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $usuario = Usuario::create($data);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario->load('rol')
        ], 201);
    }

    public function show($id)
    {
        $usuario = Usuario::with('rol')->findOrFail($id);
        return response()->json($usuario);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'id_rol' => 'sometimes|exists:rols,id_rol',
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:usuarios,email,' . $id . ',id_usuario',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'sometimes|in:activo,inactivo,suspendido',
            'avatar' => 'nullable|image|max:2048'
        ]);

        $data = $request->except('password', 'avatar');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($usuario->avatar) {
                \Storage::disk('public')->delete($usuario->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $usuario->update($data);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario->load('rol')
        ]);
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    // Métodos adicionales útiles
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:activo,inactivo,suspendido'
        ]);

        $usuario = Usuario::findOrFail($id);
        $usuario->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data' => $usuario
        ]);
    }

    public function perfil(Request $request)
    {
        $usuario = $request->user()->load('rol');
        return response()->json($usuario);
    }
}