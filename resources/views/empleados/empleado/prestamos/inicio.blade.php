@extends('layouts.empleado')
@section('tituloPagina', "Mis prestamos")

@section('content')


<div class="row">
	<div class="col-md-12">
        <button type="button" class="btn btn-warning mb-3 font-weight-bold" data-toggle="modal" data-target="#prestamoModal">Solicitar prestamo</button>

        <table class="table table-striped table-hover empresas mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Tipo Prestamo</th>
                    <th>Fecha Creación</th>
                    <th>Fecha Cierre</th>
                    <th>Estatus</th>
                    <th class="text-center">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @if ($prestamos)
                    @foreach ($prestamos as $prestamo)
                        <tr>
                            <td>{{$prestamo->pid}}</td>
                            <td>{{$prestamo->nombre}}</td>
                            <td>{{$prestamo->fecha_creacion}}</td>
                            <td>{{$prestamo->fecha_cierre}}</td>
                            <td>
                                @if($prestamo->estatus == App\Models\Prestamo::PRESTAMO_CERRADO)
                                    <span class='text-secondary font-weight-bold'>Cerrado</span>
                                @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_ABIERTO && !$prestamo->usuario_id)
                                    <span class='text-success font-weight-bold'>Sin aprobar</span>
                                @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_ABIERTO)
                                    <span class='text-success font-weight-bold'>Abierto</span>
                                @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_RECHAZADO)
                                    <span class='text-danger font-weight-bold'>Rechazado</span>
                                @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_PARA_REVISION)
                                    <span class='text-warning font-weight-bold'>Para revisión</span>
                                @endif
                            </td>
                            <td align="center">
                                <a href="{{route('prestamos.miPrestamo', encrypt($prestamo->pid))}}" class="btn btn-warning font-weight-bold" target="_blank">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="6">Sin registros</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="prestamoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Solicitar prestamo</h5>
        </div>
        <div class="modal-body">
            <form method="post" id="prestamo_form" action="{{ route('empleados.prestamos.solicitar') }}">
                @csrf
                <select name="prestamos_tipo_id" id="prestamos_tipo_id" class="form-control" required>
                    <option value="">Selecciona una opción</option>
                    @foreach ($prestamos_disponibles as $pd)
                        <option value="{{$pd->id}}">{{$pd->nombre}}</option>
                    @endforeach
                </select>
                <div class="requisitos d-none mt-3">
                    Requisitos de este prestamo:
                    <ul></ul>
                </div>
                <div class="form-row mt-4">
                    <button type="button" class="btn btn-secondary cancelar mr-2" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning solicitarPrestamo">Solicitar</button>

                    <input type="hidden" name="empresa_id" id="empresa_id" value="{{$empresaId}}">
                    <input type="hidden" name="usuario_id" id="usuario_id" value="0">
                    <input type="hidden" name="empleado_id" id="empleado_id" value="{{Session::get('empleado')['id']}}">
                    <input type="hidden" name="empleado" id="empleado" value="{{Session::get('empleado')['apaterno']}} {{Session::get('empleado')['amaterno']}} {{Session::get('empleado')['nombre']}}">
                    <input type="hidden" name="medio_contacto" id="medio_contacto" value="webEmpleado">
                    <input type="hidden" name="estatus" id="estatus" value="1">
                    <input type="hidden" name="fecha_cierre" id="fecha_cierre">
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){

    // se crean los arreglos con los requisitos de los prestamos
    @foreach ($prestamos_disponibles as $pd)
        var req{{$pd->id}} = [
        @foreach($pd->requisitos as $req)
            '{{$req->nombre}}',
        @endforeach
        ];
    @endforeach

    $('#prestamos_tipo_id').change(function(){
        prestamo_tipo_id = $(this).val();
        requisitos = eval('req'+prestamo_tipo_id);
        if(requisitos.length > 0){
            requisitos.forEach(element => $('#prestamoModal .requisitos ul').append(`<li>${element}</li>`))
            $('#prestamoModal .requisitos').removeClass('d-none');
        } else {
            $('#prestamoModal .requisitos ul').empty();
            $('#prestamoModal .requisitos').addClass('d-none');
        }
    });

    // Rechazar prestamo
    $('#prestamo_form').submit(function(e) {
        $('#prestamo_form .btn').attr('disabled', true);
        $('#prestamo_form .solicitarPrestamo').text('Espere...');
    });
});
</script>
@endpush



@endsection

