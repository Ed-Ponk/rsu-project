<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use App\Support\HandlesImageUpload;

class BrandController extends Controller
{
    use HandlesImageUpload;

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
    public function store(StoreBrandRequest $request)
    {
        $storedLogo = null;

        try {
            $data = $request->validated();

            if ($request->hasFile('logo')) {
                $storedLogo = $this->storeImage($request->file('logo'), 'brands');
                $data['logo'] = $storedLogo;
            }

            Brand::create($data);

            return redirect()->route('brands.index')->with('success', 'Marca registrada');
        } catch (\Throwable $th) {
            if ($storedLogo) {
                $this->deleteImage($storedLogo);
            }

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
    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $oldLogo = $brand->logo;
        $newLogo = null;
        $deleteOld = false;

        try {
            $data = $request->validated();

            if ($request->hasFile('logo')) {
                $newLogo = $this->storeImage($request->file('logo'), 'brands');
                $data['logo'] = $newLogo;
                $deleteOld = true;
            } elseif ($request->boolean('remove_logo')) {
                $data['logo'] = null;
                $deleteOld = true;
            }

            $brand->update($data);

            if ($deleteOld && $oldLogo) {
                $this->deleteImage($oldLogo);
            }

            return redirect()->route('brands.index')->with('success', 'Marca actualizada');
        } catch (\Throwable $th) {
            if ($newLogo) {
                $this->deleteImage($newLogo);
            }

            return back()->withInput()->with('error', 'Error en la actualización: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        try {
            if ($brand->vehicleModels()->exists()) {
                return redirect()->route('brands.index')->with('error', 'No se puede eliminar la marca porque tiene modelos de vehículo asociados.');
            }

            $this->deleteImage($brand->logo);

            $brand->delete();
            return redirect()->route('brands.index')->with('success', 'Marca eliminada');
        } catch (\Throwable $th) {
            return redirect()->route('brands.index')->with('error', 'Error de eliminación: ' . $th->getMessage());
        }
    }
}
