<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Registro de usuarios.
     */
    public function register(Request $request)
    {
        // Validar los datos de entrada con mensajes personalizados
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'nombreUsuario' => 'required|string|max:255|unique:usuarios,nombreUsuario',
            'email' => 'required|email|unique:usuarios,email',
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',

            'nombreUsuario.required' => 'El nombre de usuario es obligatorio.',
            'nombreUsuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'nombreUsuario.unique' => 'El nombre de usuario ya está en uso.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'El email ya está registrado.',

            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las contraseñas no coinciden.'
        ]);

        // Si la validación falla, devolver los errores
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear el usuario con la contraseña encriptada
        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'nombreUsuario' => $request->nombreUsuario,
            'email' => $request->email,
            'contrasena' => $request->contrasena, 
        ]);

        // Generar un token de acceso personal
        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Respuesta exitosa sin devolver la contraseña
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'nombre' => $usuario->nombre,
            'nombreUsuario' => $usuario->nombreUsuario,
            'email' => $usuario->email,
        ], 201);
    }


    /**
     * Inicio de sesión.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'contrasena' => 'required|min:8',
        ], [
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'contrasena.required' => 'El campo contraseña es obligatorio.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Usuario::where('email', $request->email)->first();

        if (!$user || $user->contrasena !== $request->contrasena) { 
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        // Generar un token para el usuario autenticado
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
        
    }
    
    /**
     * Cierre de sesion
     */
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Has salido de la cuenta',
        ], 200);
    }

    /**
     * Obtener credenciales de un usuario autenticado
     */
    public function getUser(Request $request) {
        // Verifica si el usuario está autenticado
        if (Auth::check()) {
            // Obtén el usuario autenticado
            $user = Auth::user();

            // Obtén el token actual del usuario
            $token = $request->bearerToken();

            // Retorna los datos del usuario en formato JSON
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token
            ], 200);
        }

        // Si no está autenticado, retorna un error
        return response()->json([
            'success' => false,
            'message' => 'Usuario no autenticado'
        ], 401);
    }

    /**
     * Borrar un usuario
     * 
     */
    public function deleteUser(Request $request){
        // Verifica si el usuario está autenticado
        if (Auth::check()) {
            // Obtén el usuario autenticado
            $id = Auth::id();
            $user = Usuario::find($id);
            $user->delete();

            // Retorna los datos del usuario en formato JSON
            return response()->json([
                'success' => true
            ], 200);
        }

        // Si no está autenticado, retorna un error
        return response()->json([
            'success' => false,
            'message' => 'Usuario no autenticado'
        ], 401);
    }
}
