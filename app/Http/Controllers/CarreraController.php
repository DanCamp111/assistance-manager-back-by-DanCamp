<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;

class CarreraController extends Controller
{
    public function index()
    {
        $carreras = Carrera::all();
        return response()->json($carreras);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:20|unique:carreras',
            'descripcion' => 'nullable|string'
        ]);

        $carrera = Carrera::create($request->all());
        return response()->json($carrera, 201);
    }

    public function show($id)
    {
        $carrera = Carrera::findOrFail($id);
        return response()->json($carrera);
    }

    public function update(Request $request, $id)
    {
        $carrera = Carrera::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:20|unique:carreras,codigo,'.$carrera->id,
            'descripcion' => 'nullable|string'
        ]);

        $carrera->update($request->all());
        return response()->json($carrera);
    }

    public function destroy($id)
    {
        $carrera = Carrera::findOrFail($id);
        $carrera->delete();
        return response()->json(null, 204);
    }
}