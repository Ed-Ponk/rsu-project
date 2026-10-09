<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Support\HandlesImageUpload;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    use HandlesImageUpload;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'brand_id', 'vehicle_model_id', 'color_id']);

        $vehicles = Vehicle::with('vehicleModel.brand', 'color')
            ->filter($filters)
            ->orderBy('plate')
            ->paginate(15)
            ->withQueryString();

        $brands = Brand::orderBy('name')->get();
        $vehicleModels = VehicleModel::with('brand')->orderBy('name')->get();
        $colors = Color::orderBy('name')->get();

        return view('admin.vehicles.index', compact('vehicles', 'brands', 'vehicleModels', 'colors', 'filters'));
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
                $storedImage = $this->storeImage($request->file('image'), 'vehicles');
                $data['image'] = $storedImage;
            }

            Vehicle::create($data);

            return redirect()->route('vehicles.index')->with('success', 'Vehículo registrado');
        } catch (\Throwable $th) {
            if ($storedImage) {
                $this->deleteImage($storedImage);
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
                $newImage = $this->storeImage($request->file('image'), 'vehicles');
                $data['image'] = $newImage;
                $deleteOld = true;
            } elseif ($request->boolean('remove_image')) {
                $data['image'] = null;
                $deleteOld = true;
            }

            $vehicle->update($data);

            if ($deleteOld && $oldImage) {
                $this->deleteImage($oldImage);
            }

            return redirect()->route('vehicles.index')->with('success', 'Vehículo actualizado');
        } catch (\Throwable $th) {
            if ($newImage) {
                $this->deleteImage($newImage);
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
                $this->deleteImage($vehicle->image);
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
