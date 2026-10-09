<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleModelRequest;
use App\Http\Requests\UpdateVehicleModelRequest;
use App\Models\Brand;
use App\Models\VehicleModel;

class VehicleModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicleModels = VehicleModel::with('brand')->orderBy('name')->get();

        return view('admin.vehicle-models.index', compact('vehicleModels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::orderBy('name')->get();

        return view('admin.vehicle-models.create', compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleModelRequest $request)
    {
        try {
            VehicleModel::create($request->validated());

            return redirect()->route('vehicle-models.index')->with('success', 'Modelo registrado');
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
    public function edit(VehicleModel $vehicle_model)
    {
        $brands = Brand::orderBy('name')->get();

        return view('admin.vehicle-models.edit', ['vehicleModel' => $vehicle_model, 'brands' => $brands]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleModelRequest $request, VehicleModel $vehicle_model)
    {
        try {
            $vehicle_model->update($request->validated());

            return redirect()->route('vehicle-models.index')->with('success', 'Modelo actualizado');
        } catch (\Throwable $th) {
            return back()->withInput()->with('error', 'Error en la actualización: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleModel $vehicle_model)
    {
        try {
            $vehicle_model->delete();

            return redirect()->route('vehicle-models.index')->with('success', 'Modelo eliminado');
        } catch (\Throwable $th) {
            return redirect()->route('vehicle-models.index')->with('error', 'Error de eliminación: ' . $th->getMessage());
        }
    }
}
