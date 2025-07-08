<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles',
            'descripcion' => 'nullable|string'
        ]);

        $rol = Rol::create($request->all());
        return response()->json($rol, 201);
    }

    public function show($id)
    {
        $rol = Rol::findOrFail($id);
        return response()->json($rol);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,'.$rol->id,
            'descripcion' => 'nullable|string'
        ]);

        $rol->update($request->all());
        return response()->json($rol);
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();
        return response()->json(null, 204);
    }
}