<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::with('vehicleModel.brand', 'color')->orderBy('plate')->get();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.vehicles.create', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request)
    {
        $storedImage = null;

        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $storedImage = $request->file('image')->store('vehicles', 'public');
                $data['image'] = $storedImage;
            }

            Vehicle::create($data);

            return redirect()->route('vehicles.index')->with('success', 'Vehículo registrado');
        } catch (\Throwable $th) {
            if ($storedImage) {
                Storage::disk('public')->delete($storedImage);
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
    public function edit(Vehicle $vehicle)
    {
        return view('admin.vehicles.edit', array_merge(
            $this->formData(),
            ['vehicle' => $vehicle]
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $oldImage = $vehicle->image;
        $newImage = null;
        $deleteOld = false;

        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $newImage = $request->file('image')->store('vehicles', 'public');
                $data['image'] = $newImage;
                $deleteOld = true;
            } elseif ($request->boolean('remove_image')) {
                $data['image'] = null;
                $deleteOld = true;
            }

            $vehicle->update($data);

            if ($deleteOld && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            return redirect()->route('vehicles.index')->with('success', 'Vehículo actualizado');
        } catch (\Throwable $th) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            return back()->withInput()->with('error', 'Error en la actualización: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        try {
            if ($vehicle->image) {
                Storage::disk('public')->delete($vehicle->image);
            }

            $vehicle->delete();

            return redirect()->route('vehicles.index')->with('success', 'Vehículo eliminado');
        } catch (\Throwable $th) {
            return redirect()->route('vehicles.index')->with('error', 'Error de eliminación: ' . $th->getMessage());
        }
    }

    /**
     * Shared data for the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'brands' => Brand::orderBy('name')->get(),
            'vehicleModels' => VehicleModel::with('brand')->orderBy('name')->get(),
            'colors' => Color::orderBy('name')->get(),
        ];
    }
}
