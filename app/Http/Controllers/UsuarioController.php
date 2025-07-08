<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with(['rol', 'carrera'])->get();
        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'required|string|max:50',
            'correo' => 'required|email|unique:usuarios',
            'password' => 'required|string|min:8',
            'rol_id' => 'required|exists:roles,id',
            'carrera_id' => 'nullable|exists:carreras,id',
            'status' => 'required|in:activo,inactivo'
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'correo' => $request->correo,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
            'carrera_id' => $request->carrera_id,
            'status' => $request->status
        ]);

        return response()->json($usuario, 201);
    }

    public function show($id)
    {
        $usuario = Usuario::with(['rol', 'carrera'])->findOrFail($id);
        return response()->json($usuario);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:50',
            'apellido_paterno' => 'sometimes|string|max:50',
            'apellido_materno' => 'sometimes|string|max:50',
            'correo' => ['sometimes', 'email', Rule::unique('usuarios')->ignore($usuario->id)],
            'password' => 'sometimes|string|min:8',
            'rol_id' => 'sometimes|exists:roles,id',
            'carrera_id' => 'nullable|exists:carreras,id',
            'status' => 'sometimes|in:activo,inactivo'
        ]);

        $data = $request->all();
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);
        return response()->json($usuario);
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return response()->json(null, 204);
    }
}