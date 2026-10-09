@extends('adminlte::page')

@section('title', 'RSUProject')

@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <div class="w-50">
                <h4>Listado de Marcas</h4>
            </div>
            <div class="w-50 d-flex justify-content-end">
                <button class="btn btn-success" id="btnNuevo"><i class="bi bi-plus-circle"></i> Nueva
                    Marca</button>
            </div>

        </div>
        <div class="card-body">
            <table class="table table-striped" id="DataTable">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Creación</th>
                        <th>Actualización</th>
                        <th width="20">Editar</th>
                        <th width="20">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($brands as $brand)
                        <tr>
                            <td><img src={{ $brand->logo == '' ? asset('/storage/images/no_logo.png') : asset($brand->logo) }}
                                    style="width:70px; height:50px;"></td>
                            <td>{{ $brand->name }}</td>
                            <td>{{ $brand->description }}</td>
                            <td>{{ $brand->created_at }}</td>
                            <td>{{ $brand->updated_at }}</td>
                            <td> <button class="btn btn-primary btn-sm btnEditar" data-id={{ $brand->id }}><i
                                        class="bi bi-pencil-square"></i></button>
                            </td>
                            <td>
                                <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" class="frmEliminar">
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
                url: "{{ route('brands.create') }}",
                type: "GET",
                success: function(response) {
                    $('#formModal #formModalLabel').html("Nueva Marca");
                    $('#formModal .modal-body').html(response);
                    $('#formModal').modal('show');
                }
            });
        });

        $(document).on('click', '.btnEditar', function() {
            var id = $(this).data('id');

            $.ajax({
                url: "{{ route('brands.edit', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $('#formModal #formModalLabel').html("Editar Marca");
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
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });
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
