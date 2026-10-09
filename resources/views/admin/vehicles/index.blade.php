@extends('adminlte::page')

@section('title', 'RSUProject')

@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <div class="w-50">
                <h4>Listado de Vehículos</h4>
            </div>
            <div class="w-50 d-flex justify-content-end">
                <button class="btn btn-success" id="btnNuevo"><i class="bi bi-plus-circle"></i> Nuevo
                    Vehículo</button>
            </div>

        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('vehicles.index') }}" class="row g-2 mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label">Placa</label>
                    <input type="text" name="search" id="search" class="form-control"
                        value="{{ $filters['search'] ?? '' }}" placeholder="Buscar por placa">
                </div>
                <div class="col-md-2">
                    <label for="filterBrand" class="form-label">Marca</label>
                    <select name="brand_id" id="filterBrand" class="form-control">
                        <option value="">Todas</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) ($filters['brand_id'] ?? '') === (string) $brand->id)>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterModel" class="form-label">Modelo</label>
                    <select name="vehicle_model_id" id="filterModel" class="form-control">
                        <option value="">Todos</option>
                        @foreach ($vehicleModels as $vehicleModel)
                            <option value="{{ $vehicleModel->id }}" data-brand="{{ $vehicleModel->brand_id }}"
                                @selected((string) ($filters['vehicle_model_id'] ?? '') === (string) $vehicleModel->id)>
                                {{ $vehicleModel->brand->name }} - {{ $vehicleModel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterColor" class="form-label">Color</label>
                    <select name="color_id" id="filterColor" class="form-control">
                        <option value="">Todos</option>
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}" @selected((string) ($filters['color_id'] ?? '') === (string) $color->id)>
                                {{ $color->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i>
                        Limpiar filtros</a>
                </div>
            </form>

            <table class="table table-striped" id="DataTable">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Color</th>
                        <th>Placa</th>
                        <th>Año</th>
                        <th>Capacidad</th>
                        <th width="20">Editar</th>
                        <th width="20">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vehicles as $vehicle)
                        <tr>
                            <td>
                                <img src="{{ $vehicle->image_url }}" alt="Vehículo {{ $vehicle->plate }}"
                                    style="width:70px;height:50px;object-fit:cover;">
                            </td>
                            <td>{{ $vehicle->vehicleModel?->brand?->name }}</td>
                            <td>{{ $vehicle->vehicleModel?->name }}</td>
                            <td>
                                @if ($vehicle->color)
                                    <span
                                        style="display:inline-block;width:16px;height:16px;border-radius:50%;border:1px solid #ced4da;background:{{ $vehicle->color->hex ?: '#ffffff' }};vertical-align:middle;"></span>
                                    {{ $vehicle->color->name }}
                                @endif
                            </td>
                            <td>{{ $vehicle->plate }}</td>
                            <td>{{ $vehicle->year }}</td>
                            <td>{{ $vehicle->capacity }}</td>
                            <td> <button class="btn btn-primary btn-sm btnEditar" data-id={{ $vehicle->id }}><i
                                        class="bi bi-pencil-square"></i></button>
                            </td>
                            <td>
                                <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" class="frmEliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i
                                            class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                {{ $vehicles->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="formModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')

    <script>
        $('#btnNuevo').click(function() {
            $.ajax({
                url: "{{ route('vehicles.create') }}",
                type: "GET",
                success: function(response) {
                    $('#formModal #formModalLabel').html("Nuevo Vehículo");
                    $('#formModal .modal-body').html(response);
                    $('#formModal').modal('show');
                }
            });
        });

        $(document).on('click', '.btnEditar', function() {
            var id = $(this).data('id');

            $.ajax({
                url: "{{ route('vehicles.edit', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $('#formModal #formModalLabel').html("Editar Vehículo");
                    $('#formModal .modal-body').html(response);
                    $('#formModal').modal('show');
                }
            });
        });

        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: "Está seguro de eliminar?",
                text: "Esto no se puede revertir!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, eliminar!"
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }

            });
        });

        $(document).ready(function() {
            var table = $('#DataTable').DataTable({
                "paging": false,
                "searching": false,
                "info": false,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });

            function filterModels() {
                var brandId = $('#filterBrand').val();

                $('#filterModel option').each(function() {
                    var $option = $(this);
                    var optionBrand = $option.data('brand');

                    if (optionBrand === undefined) {
                        return;
                    }

                    var match = !brandId || String(optionBrand) === String(brandId);
                    $option.prop('hidden', !match).prop('disabled', !match);
                });

                var selected = $('#filterModel').val();
                if (selected && $('#filterModel option[value="' + selected + '"]').prop('disabled')) {
                    $('#filterModel').val('');
                }
            }

            $('#filterBrand').on('change', filterModels);
            filterModels();
        });
    </script>

    @if (session('success') != null)
        <script>
            Swal.fire({
                title: "Proceso exitoso!",
                icon: "success",
                text: '{{ session('success') }}',
                draggable: true
            });
        </script>
    @endif

    @if (session('error') != null)
        <script>
            Swal.fire({
                title: "Ocurrió un error!",
                icon: "error",
                text: '{{ session('error') }}',
                draggable: true
            });
        </script>
    @endif

@stop
