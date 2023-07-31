@extends('layouts.principal')
@section('tituloPagina', "Mi Préstamo")

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">

        <ul class="nav nav-tabs float-none" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">Préstamo</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="requisitos-tab" data-toggle="tab" href="#requisitos" role="tab" aria-controls="requisitos" aria-selected="false">Requisitos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="notas-tab" data-toggle="tab" href="#notas" role="tab" aria-controls="notas" aria-selected="false">Notas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="chat-tab" data-toggle="tab" href="#chat" role="tab" aria-controls="chat" aria-selected="false">Chat</a>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="mt-4 card">
                    <div class="card-body">

                        <div class="form-row col-md-12 mb-lg-4 estatus">
                            <h3>Mi préstamo:
                                @if ($prestamo->estatus == 0)
                                    <span class="font-weight-bold text-secondary">Cerrado</span>
                                @elseif ($prestamo->estatus == 1)
                                    <span class="font-weight-bold text-success">Abierto</span>
                                @elseif ($prestamo->estatus == 3)
                                    <span class="font-weight-bold text-danger">Rechazado</span>
                                @elseif ($prestamo->estatus == 4)
                                    <span class="font-weight-bold text-success">En proceso de Revisión</span>
                                @endif
                            </h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-7 mt-6">
                                <div>Empleado:</div>
                                <div class="font-weight-bold">{{ $prestamo->empleado }}</div>
                            </div>
                            <div class="form-group col-md-5">
                                <div>Fecha de ingreso del empleado:</div>
                                <div class="font-weight-bold">{{ formatoAFecha($empleado->fecha_antiguedad) }}</div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <div>Empresa:</div>
                                <div class="font-weight-bold">{{ $prestamo->empresa->razon_social }}</div>
                            </div>
                            <div class="form-group  col-md-5">
                                <div>Tipo de préstamo:</div>
                                <div class="font-weight-bold">{{ $prestamo->tipoPrestamo->nombre }}</div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group  col-md-7">
                                <div>Fecha/Hora de requisición del préstamo:</div>
                                <div class="font-weight-bold">{{ formatoAFecha($prestamo->fecha_creacion, true) }}</div>
                            </div>
                            <div class="form-group col-md-5">
                                <div>Medio de contacto:</div>
                                <div class="font-weight-bold">{{ ucfirst($prestamo->medio_contacto) }}</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <div>Email:</div>
                                <div class="font-weight-bold">{{ $empleado->correo }}</div>
                            </div>
                            <div class="form-group col-md-5">
                                <div>Teléfono:</div>
                                <div class="font-weight-bold">{{ $empleado->telefono_movil }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade show" id="requisitos" role="tabpanel" aria-labelledby="requisitos-tab">
                <div class="requisitos mb-5">
                    <div class="form-row col-md-12 my-lg-3">
                        <h3>Documentos del usuario: </h3>
                    </div>
                    <table class="table table-striped documentos-list">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Completado</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $faltantes = 0;
                            @endphp
                            @foreach ($prestamo->tipoPrestamo->requisitos as $req)
                                <tr>
                                    <td width="350">{{$req->nombre}}:</td>
                                    <td >
                                        @if ($req->tipo == 'info')
                                            {{ $req->valor}}
                                        @else
                                            @php $encontrado = false; @endphp
                                            @foreach($prestamo->requisitosLlenos as $reqLleno)
                                                @if($req->id == $reqLleno->prestamo_requisito_id)
                                                    <div class="doc{{ $reqLleno->id }} d-flex">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                        @if ($req->tipo == 'text')
                                                            {{ $reqLleno->valor }}
                                                        @else
                                                            <a href="{{ asset('storage'. $reqLleno->valor) }}" target="_blank">{{ $reqLleno->valor }}</a>
                                                        @endif
                                                    </div>
                                                    @php $encontrado = true; @endphp
                                                    @break
                                                @endif
                                            @endforeach
                                            @if (!$encontrado)
                                                @php $faltantes++; @endphp
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($prestamo->estatus == '1' || $prestamo->estatus == '4')
                                            @foreach($prestamo->requisitosLlenos as $reqLleno)
                                                @if($req->id == $reqLleno->prestamo_requisito_id)
                                                    <a href="#" data-id="{{ $reqLleno->id }}" class="borrar btn btn-danger btn-sm doc{{ $reqLleno->id }}"><i class="fas fa-trash-alt"></i></a>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade show" id="notas" role="tabpanel" aria-labelledby="notas-tab">
                <div class="row">
                    <div class="col-md-7 pt-3 notas-list">
                        @if (sizeof($prestamo->notas) > 0)
                            @foreach ($prestamo->notas as $nota)
                                <div class="alert alert-secondary mb-3" role="alert">
                                    <div class="text-muted">{!! $nota->texto !!}</div>
                                    <div class="text-sm-right font-italic" style="font-size: 10px">{{ \Carbon\Carbon::parse($nota->created_at)->format('d/M/Y H:ia') }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="font-weight-bold text-muted mt-5 sin-notas">Sin notas</div>
                        @endif
                    </div>
                    <div class="col-md-5 pt-3">
                        <form action="" method="post">
                            <h4>Agregar una nota:</h4>
                            <textarea name="" id="nota" cols="30" rows="10" required class="form-control"></textarea>
                            <button type="button" class="btn btn-warning agregar mt-3">Agregar</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade show" id="chat" role="tabpanel" aria-labelledby="chat-tab">
                <div class="row">
                    <div class="col-sm-12 mt-3 card">
                            <div class="col-sm-12 mt-3" >
                                <h3>Poximamente</h3>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row botones my-lg-5 ">
            <a type="button" class="btn btn-dark mr-3" href="{{ route("prestamos.tabla") }}">Regresar</a>

            {{-- Abierto o Para revision --}}
            @if ($prestamo->estatus == 1 || $prestamo->estatus == 4)
                <button type="button" class="btn btn-dark concluir mr-3" data-pid="{{ $prestamo->id }}">Concluir préstamo</button>
            @endif
            <button type="button" class="btn btn-warning notificar @if ($faltantes <= 0) d-none @endif">Enviar notificación al usuario</button>

        </div>


    </div>
</div>



@endsection

@push('scripts')
<script>
$(function(){

    // ELIMINAR REQUISITO
    $('.borrar').click( function(e){
        e.preventDefault();

        var id = $(this).data('id');
        alertify.confirm('Aviso ','¿Esta seguro de eliminar este documento?',
            function(){ borrarRequisito(id); },
            function(){ alertify.alert().close(); }
        );
    });

    function borrarRequisito(id) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            '_token': CSRF_TOKEN,
            'id' : id,
            '_method': 'DELETE'
        }

        var url = "{{route('prestamos.documento.borrar', '*ID*')}}";
        url = url.replace('*ID*', id);

        $.ajax({
            method: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    $('.documentos-list .doc' + id).remove();
                    alertify.success('El documento se eliminó correctamente.');
                    $('.botones .notificar').removeClass('d-none');
                } else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                }
            }
        });
    }


    // AGREGAR NOTAS
    $('.agregar').click( function(e){
        e.preventDefault();

        if($('#nota').val().trim() == '') {
            $('#nota').val('');
            alertify.alert('Error', 'Por favor ingrese el texto de la nota para poder continuar.');
            return;
        }

        $('.agregar').attr('disabled', true);

        var id = '{{ $prestamo->id }}';
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var notaTexto = $('#nota').val().trim().replace(/\n/g, "<br />");
        data = {
            '_token': CSRF_TOKEN,
            'prestamo_id' : id,
            'texto' : notaTexto
        }

        var url = "{{route('prestamos.notas.crear')}}";

        $.ajax({
            method: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    nota = `<div class="alert alert-secondary mb-3" role="alert">
                                <div class="text-muted">${ notaTexto }</div>
                                <div class="text-sm-right font-italic" style="font-size: 10px">${ moment().format('DD/MMM/YYYY H:mma') }</div>
                            </div>`;
                    $('.notas-list').prepend(nota);
                    $('#nota').val('');
                    $('.agregar').attr('disabled', false);
                    $('.sin-notas').remove();
                } else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                }
            }
        });
    });


    // CONCLUIR PRESTAMO
    // ELIMINAR REQUISITO
    $('.concluir').click( function(e){
        e.preventDefault();

        var id = $(this).data('pid');
        alertify.confirm('Aviso ','¿Esta seguro de concluir el préstamo?',
            function(){ concluirPrestamo(id); },
            function(){ alertify.alert().close(); }
        );
    });

    function concluirPrestamo(id) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            '_token': CSRF_TOKEN,
            'id' : id,
            '_method': 'PUT',
        }

        var url = "{{route('prestamos.cerrar', '*ID*')}}";
        url = url.replace('*ID*', id);

        $.ajax({
            method: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    $('.concluir').remove();
                    $('.documentos-list .btn.borrar').remove();
                    $('.estatus span').text('Cerrado').removeClass('text-success').addClass('text-secondary');
                    alertify.success('Se ha concluido el préstamo correctamente.');
                } else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                }
            }
        });
    }

    // Notificar al usuario que se eliminó algun documento
    $('.notificar').click(function(){
        alertify.prompt( 'Aviso', 'Por favor esciba un mensaje que será añadido al email que se enviará a continuación:', ''
            , function(evt, value) { notificarUsuario(value) }
            , function() { alertify.prompt().close();}
        );
    });

    function notificarUsuario(mensaje){
        $('.botones .notificar').text('Espere...');
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            '_token': CSRF_TOKEN,
            'pid' : {{ $prestamo->id }},
            'email' : '{{ $empleado->correo }}',
            'tipoPrestamo' : '{{ $prestamo->tipoPrestamo->nombre }}',
            'nombreEmpleado' : '{{ $prestamo->empleado }}',
            'mensaje' : mensaje
        }

        var url = "{{route('prestamos.documento.notificar')}}";

        $.ajax({
            method: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    alertify.success('Se envió el mail correctamente.');
                    $('.botones .notificar').text('Enviar notificación al usuario');
                } else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                }
            }
        });
    }

});
</script>
@endpush
