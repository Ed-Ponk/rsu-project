@php
    $currentVehicle = $vehicle ?? null;
    $selectedModelId = old('vehicle_model_id', $currentVehicle->vehicle_model_id ?? null);
    $selectedBrandId = old('brand_filter', $currentVehicle?->vehicleModel?->brand_id);
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="brand_filter">Marca</label>
            <select id="brand_filter" name="brand_filter" class="form-control">
                <option value="">Todas las marcas</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) $selectedBrandId === (string) $brand->id)>{{ $brand->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Solo filtra los modelos. No se guarda.</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="vehicle_model_id">Modelo</label>
            <select name="vehicle_model_id" id="vehicle_model_id" class="form-control" required>
                <option value="">Seleccione un modelo</option>
                @foreach ($vehicleModels as $model)
                    <option value="{{ $model->id }}" data-brand="{{ $model->brand_id }}" @selected((string) $selectedModelId === (string) $model->id)>
                        {{ $model->brand?->name }} - {{ $model->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('color_id', 'Color') !!}
            {!! Form::select('color_id', $colors->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'placeholder' => 'Seleccione un color',
                'required',
            ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('plate', 'Placa') !!}
            {!! Form::text('plate', null, ['class' => 'form-control', 'placeholder' => 'Ej. ABC-123', 'required']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('year', 'Año') !!}
            {!! Form::number('year', null, ['class' => 'form-control', 'min' => 1900, 'max' => date('Y') + 1]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('capacity', 'Capacidad') !!}
            {!! Form::number('capacity', null, ['class' => 'form-control', 'min' => 0]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <div id="imageButton" style="width: 100%; text-align:center; padding:10px;">
                <img id="imagePreview" src="{{ $currentVehicle?->image_url ?? asset('images/no_logo.svg') }}"
                    alt="Vista previa del vehículo" style="width: 100%;height:180px;object-fit:cover;cursor: pointer;">
                <p style="font-size:12px">Haga click para seleccionar una imagen</p>
            </div>
        </div>
        <div class="form-group">
            {!! Form::file('image', [
                'class' => 'form-control-file d-none',
                'accept' => 'image/*',
                'id' => 'imageInput',
            ]) !!}
        </div>
        @if ($currentVehicle?->image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input', 'id' => 'remove_image']) !!}
                <label class="form-check-label" for="remove_image">Eliminar imagen actual</label>
            </div>
        @endif
    </div>
</div>

<script>
    (function() {
        var $modelSelect = $('#vehicle_model_id');
        var $brandFilter = $('#brand_filter');

        function filterModels() {
            var brandId = $brandFilter.val();

            $modelSelect.find('option').each(function() {
                var value = $(this).attr('value');

                if (!value) {
                    return;
                }

                var optionBrand = String($(this).data('brand'));
                var match = !brandId || optionBrand === String(brandId);

                $(this).prop('disabled', !match).toggle(match);

                if (!match && $(this).is(':selected')) {
                    $modelSelect.val('');
                }
            });
        }

        $brandFilter.on('change', filterModels);
        filterModels();

        $('#imageButton').on('click', function() {
            $('#imageInput').click();
        });

        $('#imageInput').on('change', function(event) {
            var file = event.target.files[0];

            if (file) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });
    })();
</script>

@section('js')
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
@endsection
