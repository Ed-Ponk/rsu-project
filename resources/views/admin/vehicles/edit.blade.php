{!! Form::model($vehicle, ['route' => ['vehicles.update', $vehicle], 'method' => 'put', 'files' => true]) !!}
@include('admin.vehicles.template.form')
<br>
<button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> Actualizar</button>
<button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
{!! Form::close() !!}
