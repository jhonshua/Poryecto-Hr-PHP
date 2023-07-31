<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<style type="text/css">
    .top-line-black {
        width: 19%; }
</style>
<body>
    @include('includes.navbar')
    <div class="container">
        @include('includes.header',['title'=>'Dispersión nóminas','subtitle'=>'Procesos de cálculo / Dispersiones', 'img'=>'img/header/parametria/icono-puestos.png','route'=>'procesos.dispersion.inicio'])
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
            <table class="table w-100 text-center" id="tabla_puestos">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Nombre del periodo</th>
                        <th>Fecha inicial</th>
                        <th>Fecha final</th>
                        <th>Fecha pago</th>
                        <th>Ejercicio</th>
                        <th>Estado</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periodos_nomina as $periodo)
                        <tr>
                            <td>{{$periodo->id}}</td>
                            <td>{{$periodo->nombre_periodo}}</td>
                            <td>{{\Carbon\Carbon::parse($periodo->fecha_inicial_periodo)->format('d-m-Y') }}</td>
                            <td>{{\Carbon\Carbon::parse($periodo->fecha_final_periodo)->format('d-m-Y')}}</td>
                            <td>{{\Carbon\Carbon::parse($periodo->fecha_pago)->format('d-m-Y')}}</td>
                            <td>{{$periodo->ejercicio}}</td>
                            <td>
                                @if($periodo->activo == App\Models\periodosNomina::CERRADO)
                                <b style="color: red">Periodo cerrado</b><br>
                                @elseif($periodo->activo == App\Models\periodosNomina::ACTIVO)
                                <b style="color: red">Periodo en proceso de cálculo</b> 
                                @else
                                <b>Nómina sin calcular</b>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                  
                                    @if((Session::get('empresa')['parametros'][0]['tipo_nomina'] =='Sindical' || Session::get('empresa')['parametros'][0]['tipo_nomina'] =='sindical') && $periodo->dispersiones->count() == 1 )
                                        
                                        <button class="administrar btn" 
                                            data-toggle="modal" 
                                            data-target="#administrarDispersionModal" 
                                            data-placement="right" 
                                            title="Administrar Dispersión"
                                            data-nombreperiodo="{{$periodo->nombre_periodo}}" 
                                            data-ejercicio="{{$periodo->ejercicio}}" 
                                            data-periodo="{{$periodo->id}}"> 
                                            <img src="/img/icono-empresa.png" class="button-style-icon">
                                        </button>
                                        
                                        
                                        <a data-enlace="#" data-nombreperiodo="{{$periodo->nombre_periodo}}" data-ejercicio="{{$periodo->ejercicio}}" data-periodo="{{$periodo->id}}" class="btn btn-warning btn-sm mr-2" data-toggle="tooltip" data-placement="right" title="Ver Dispersión"><i class="fas fa-sitemap"></i></a>
            
                                    @elseif($periodo->dispersiones->count() == 1)
                                        
                                        <a data-enlace="#" data-nombreperiodo="{{$periodo->nombre_periodo}}" data-ejercicio="{{$periodo->ejercicio}}" data-periodo="{{$periodo->id}}" class="btn btn-warning btn-sm mr-2" data-toggle="tooltip" data-placement="right" title="Ver Dispersión"><i class="fas fa-sitemap"></i></a>
                                    
                                    @elseif($periodo->dispersiones->count() > 1)
                                        @php $cont = 1; @endphp
                                        @foreach($periodo->dispersiones as $dispersion)
                                            <a data-enlace="#" data-nombreperiodo="{{$periodo->nombre_periodo}}" data-ejercicio="{{$periodo->ejercicio}}" data-periodo="{{$periodo->id}}" name="verdispersion{{$cont}}" class="btn btn-warning btn-sm mr-2" data-toggle="tooltip" data-placement="right" title="Ver Dispersión {{$cont}}">{{$cont}} <i class="fas fa-sitemap"></i></a>
                                        @php $cont++; @endphp
                                        @endforeach
                                    @elseif($periodo->dispersiones->count() == 0)

                                        <span data-toggle="tooltip">
                                            <button class="administrar btn" 
                                                data-toggle="modal" 
                                                data-target="#administrarDispersionModal" 
                                                data-placement="right" 
                                                title="Administrar Dispersión"
                                                data-nombreperiodo="{{$periodo->nombre_periodo}}" 
                                                data-ejercicio="{{$periodo->ejercicio}}" 
                                                data-periodo="{{$periodo->id}}"> 
                                                <img src="/img/icono-registro-p.png" class="button-style-icon">
                                            </button>
                                        </span>
                                    @endif

                                    <button class="excel btn" 
                                        data-placement="right"
                                        data-toggle="tooltip" 
                                        title="Generar excel"
                                        data-nombreperiodo="{{$periodo->nombre_periodo}}" 
                                        data-ejercicio="{{$periodo->ejercicio}}" 
                                        data-periodo="{{$periodo->id}}"> 
                                        <img src="/img/icono-pdf.png" class="button-style-icon">
                                    </button>
                                    
                                    <button class="totales btn" 
                                        data-placement="right"
                                        data-toggle="tooltip" 
                                        title="Exportar totales"
                                        data-nombreperiodo="{{$periodo->nombre_periodo}}" 
                                        data-ejercicio="{{$periodo->ejercicio}}" 
                                        data-periodo="{{$periodo->id}}" >
                                        <img src="/img/descargar-timbrado.png" class="button-style-icon">
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <form id="enviar_periodo" action="{{route('procesos.dispersion.panelAdministracion')}}" method="post">
        @csrf
            <input type="hidden" name="tipo_dispersion" id="tipo_dispersion" value="1">
            <input type="hidden" name="idperiodo" id="idperiodo">
            <input type="hidden" name="ejercicio" id="ejercicio">
            <input type="hidden" name="nombre_periodo" id="nombre_periodo">
    </form>
    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_puestos').DataTable({
            scrollY:'65vh',
            "order": [[ 0,"desc" ]],
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por puesto',
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

            $(".excel").off().on("click",function(){
             var url = "{{route('procesos.dispersion.nomina.excel')}}";
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

