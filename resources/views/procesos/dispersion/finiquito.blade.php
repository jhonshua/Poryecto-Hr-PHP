<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link href="{{ asset('css/iconos_datepicker.css') }}" rel="stylesheet">
@include('includes.head')

<style type="text/css">
    .top-line-black {
        width: 19%; }
</style>

<body>
    @include('includes.navbar')
    <div class="container">
        @include('includes.header',['title'=>'Dispersion Finiquito','subtitle'=>'Procesos de cálculo', 'img'=>'img/header/parametria/icono-puestos.png','route'=>'procesos.dispersion.inicio'])
        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
        @elseif(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif
        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
            </div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>
        <div class="article border">
            <table class="table  responsive-table" id="periodos_nomina_dispersion">
                <thead>
                  <tr>
                    <th>No.Empleado</th>
                    <th>Nombre</th>
                    <th>Fecha Baja</th>
                    <th>Importe</th>
                    <th>Periodo</th>
                    <th>Estado</th>
                    <th>Opciones</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach($empleados as $empleado)
                        <tr>
                            <td>{{$empleado->id}}</td>
                            <td>{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</td>
                            <td>{{$empleado->fecha_baja}}</td>
                            <td>{{$empleado->neto}}</td>
                            <td>{{$empleado->periodo}}</td>
                            <td>
                            @if($empleado->estatus == 2)
                                <b class="text-success">Finiquito Cerrado</b><br>
                            @elseif($empleado->estatus == 20)
                                <b class="text-success">Finiquito Guardado</b><br>
                            @endif
                            
                            </td>
                            <td>
                            

                                <button class="administrar btn" 
                                        data-periodo="{{$empleado->periodo}}"
                                        data-empleado="{{$empleado->id}}"
                                        data-ejercicio="{{$empleado->ejercicio}}"
                                        data-nombreperiodo="{{$empleado->nombre_periodo}}"
                                        class="btn btn-warning btn-sm mr-2 administrar" 
                                        data-toggle="tooltip" 
                                        title="Administrar Dispersión"> 
                                    <img src="/img/icono-registro-p.png" class="button-style-icon">
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
          </table>
          <form id="enviar_periodo" action="{{route('procesos.dispersion.panelAdministracion')}}" method="post">
            @csrf
                <input type="hidden" name="tipo_dispersion" id="tipo_dispersion" value="2">
                <input type="hidden" name="idperiodo" id="idperiodo">
                <input type="hidden" name="ejercicio" id="ejercicio">
                <input type="hidden" name="idempleado" id="idempleado">
                <input type="hidden" name="nombre_periodo" id="nombre_periodo">
            </form>
        </div>
    </div>
 
    @include('includes.footer')


    <script src="{{asset('js/typeahead.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#periodos_nomina_dispersion').DataTable({
            scrollY:'65vh',
            "order": [[ 0,"desc" ]],
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por finiquito',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [1]).every(function(){

                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }});
                dataSrc.sort();
                
                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value){
                            api.search(value).draw();
                        }
                    }
                );

                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
        });

        $('[data-toggle="tooltip"]').tooltip();

        table.on( 'draw', function () {

            $(".administrar").off().on("click",function(){

                var url = "{{route('procesos.dispersion.panelAdministracion')}}";
                $("#enviar_periodo").attr('action',url);
                $("#idperiodo").val($(this).data('periodo'));
                $("#ejercicio").val($(this).data('ejercicio'));
                $("#nombre_periodo").val($(this).data('nombreperiodo'));
                $("#enviar_periodo").submit();
           });

            $(".totales").on('click',function(){
            
                var url = "{{route('procesos.dispersion.nomina.totales')}}";
                $("#enviar_periodo").attr('action',url);
                $("#enviar_periodo").attr('target',"_blank");

                $("#idperiodo").val($(this).data('periodo'));
                $("#ejercicio").val($(this).data('ejercicio'));
                $("#nombre_periodo").val($(this).data('nombreperiodo'));
                $("#enviar_periodo").submit();
            
            });
        });
      
    

        function validarBorrado(id){            
            swal({
                    title: "",
                    text: "¿Esta seguro de eliminar este puesto?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        borrarPuesto(id);
                    }
                });
        }

        function borrarPuesto(id) {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {
                'id': id,
                '_token': CSRF_TOKEN
            }

            $.ajax({
                url: `{{route('parametria.puesto.real.borrar')}}`,
                type: 'POST',
                data: data,
                dataType: 'JSON',
                beforeSend: function() {},
                success: function(response) {
                    if (response.ok == 1) {
                        swal("El puesto se eliminó correctamente.", {
                            icon: "success",
                        });
                        setTimeout('location.reload()', 500);
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("", "Ocurrió un error al eliminar el registro!", "error");
                    // console.log(errorThrown);
                }
            });           
        }
        
    </script>

