<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::all();
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $logo = '';

            $request->validate([
                'name' => 'unique:brands'
            ]);

            if ($request->logo != '') {
                $image = $request->file('logo')->store('brand_logo', 'public');
                $logo = Storage::url($image);
            }

            Brand::create([
                'name' => $request->name,
                'logo' => $logo,
                'description' => $request->description
            ]);

            return redirect()->route('brands.index')->with('success', 'Marca registrada');
        } catch (\Throwable $th) {
            //return redirect()->route('brands.index')->with('error', 'Error en el registro: ' . $th->getMessage());
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
    public function edit(string $id)
    {
        $brand = Brand::find($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $brand = Brand::find($id);

            $request->validate([
                'name' => 'unique:brands,name,' . $id,
            ]);

            $logo = '';

            if ($request->logo != '') {
                $image = $request->file('logo')->store('brand_logo', 'public');
                $logo = Storage::url($image);

                $brand->update([
                    'name' => $request->name,
                    'logo' => $logo,
                    'description' => $request->description
                ]);
            } else {
                /*$brand->update([
                    'name' => $request->name,
                    'description' => $request->description
                ]);*/
                $brand->update($request->except('logo'));
            }
            return redirect()->route('brands.index')->with('success', 'Marca actualizada');
        } catch (\Throwable $th) {
            //return redirect()->route('brands.index')->with('error', 'Error en la actualización: ' . $th->getMessage());
            return back()->withInput()->with('error', 'Error en la actualización: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $brand = Brand::find($id);
            $brand->delete();
            return redirect()->route('brands.index')->with('success', 'Marca eliminada');
        } catch (\Throwable $th) {
            return redirect()->route('brands.index')->with('error', 'Error de eliminación: ' . $th->getMessage());
        }
    }
}
