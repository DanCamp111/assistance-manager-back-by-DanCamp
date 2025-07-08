<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required'
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales son incorrectas.'],
            ]);
        }

        return response()->json([
            'token' => $usuario->createToken($request->correo)->plainTextToken,
            'usuario' => $usuario
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user()->load('rol'));
    }
}