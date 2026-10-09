<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreColorRequest;
use App\Http\Requests\UpdateColorRequest;
use App\Models\Color;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $colors = Color::all();

        return view('admin.colors.index', compact('colors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.colors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreColorRequest $request)
    {
        try {
            Color::create($request->validated());

            return redirect()->route('colors.index')->with('success', 'Color registrado');
        } catch (\Throwable $th) {
            return back()->withInput()->with('error', 'Error en el registro: ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Color $color)
    {
        return view('admin.colors.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateColorRequest $request, Color $color)
    {
        try {
            $color->update($request->validated());

            return redirect()->route('colors.index')->with('success', 'Color actualizado');
        } catch (\Throwable $th) {
            return back()->withInput()->with('error', 'Error en la actualización: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        try {
            $color->delete();

            return redirect()->route('colors.index')->with('success', 'Color eliminado');
        } catch (\Throwable $th) {
            return redirect()->route('colors.index')->with('error', 'Error de eliminación: ' . $th->getMessage());
        }
    }
}
