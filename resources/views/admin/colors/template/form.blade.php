<div class="row">
    <div class="col-8">
        <div class="form-group">
            {!! Form::label('name', 'Nombre') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre del color', 'required']) !!}
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            {!! Form::label('hex', 'Color') !!}
            {!! Form::color('hex', $color->hex ?? '#000000', ['class' => 'form-control form-control-color']) !!}
            <small class="text-muted">Código hexadecimal (ej. #FF0000)</small>
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
