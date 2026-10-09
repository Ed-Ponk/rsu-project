<div class="row">
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('brand_id', 'Marca') !!}
            {!! Form::select('brand_id', $brands->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'placeholder' => 'Seleccione una marca',
                'required',
            ]) !!}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('name', 'Modelo') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre del modelo', 'required']) !!}
        </div>
    </div>
</div>

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
