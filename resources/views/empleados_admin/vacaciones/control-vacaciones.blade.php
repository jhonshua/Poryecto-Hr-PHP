<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('includes.head')
<body>
    @include('includes.navbar')
    @php
    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Control de empleados',
        'subtitle'=>'Información', 'img'=>'img/header/parametria/icono-puestos.png',
        'route'=>'empleados.vacaciones'])

        @if(\Session::get('mensaje'))
        <div class="alert alert-info" role="alert" id="alerta">
            {!! \Session::get('mensaje') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9"></div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
          
            <table class="table w-100 tabla_datos empleados"  id="idTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th width="100px" >Periodo</th>
                        <th>Vac. Disp. </th>
                        <th width="100px">Vac. Pag.</th>
                        <th width="100px">Vac. Disf.</th>
                        <th>Tot. Vac.</th>
                        <th width="100px">Perm. Pag.</th>
                        <th>Perm. Disf.</th>
                        <th>Total. Perm.</th>
                        <th>Historial</th>         
                    </tr>
                </thead>
                <tbody>
                    @php $fecha = new \DateTime() /*echo $fecha->format("Y")-3;*/ @endphp
                    @foreach($datos_empleados as $e)
                    <tr>
                        <td>{{$e->id}}</td>  
                        <td>{{$e->nombre}} {{$e->apaterno}} {{$e->amaterno}}</td>
                        <td>
                            <select class="form-control input-style-custom  form-control-sm select_periodo" name="periodo" data-idempleado="{{$e->id}}" id="periodo_{{$e->id}}" width="150px">    
								<!-- <option value="">Seleccione</option> $fecha->format("Y-m-d H:i:s") -->
                                @for($x = $fecha->format("Y"); $x>($fecha->format("Y")-3); $x--)                                                                       
                                    <option value="{{$x}}">{{$x}}</option>                                    
                                @endfor
                            </select>
                        </td>
                        <td><span id="vac_disp_{{$e->id}}">{{$e->dias_vacaciones_prestaciones - ($e->total_dias_vac_pedidos + $e->total_dias_vac_pagados)}}</span></td>
                        <td><span id="vac_pag_{{$e->id}}">{{$e->total_dias_vac_pagados}}  </span><img src="{{ asset('/img/icono-agregar-inhabilitado.png') }}" class="widht-25 ml-2" data-idempleado="{{$e->id}}" data-periodo="{{$fecha->format('Y')}}" data-nombre="{{$e->nombre}} {{$e->apaterno}} {{$e->amaterno}}" data-vacacionesdisp="{{$e->dias_vacaciones_prestaciones - ($e->total_dias_vac_pedidos + $e->total_dias_vac_pagados)}}" class="btnAgregaVacacionesPag btn btn-secondary mr-2" /></td>
                        <td><span id="vac_pedido_{{$e->id}}">{{$e->total_dias_vac_pedidos}}</span><img src="{{ asset('/img/icono-agregar-inhabilitado.png') }}" class="widht-25 ml-2"></td>
                        <td><span id="total_vac_{{$e->id}}">{{$e->total_dias_vac_pedidos + $e->total_dias_vac_pagados}}</span></td>
                        <td><span id="perm_pag_{{$e->id}}">{{$e->total_dias_perm_pagados}}</span><img src="{{ asset('/img/icono-agregar-inhabilitado.png') }}" class="widht-25 ml-2" data-idempleado="{{$e->id}}" data-periodo="{{$fecha->format('Y')}}" data-nombre="{{$e->nombre}} {{$e->apaterno}} {{$e->amaterno}}" class="btnAgregaPermisoPag btn btn-secondary mr-2"/></td>
                        <td><span id="perm_pedido_{{$e->id}}">{{$e->total_perm_dias_pedidos}}</span></td>
                        <td><span id=total_perm_{{$e->id}}">{{$e->total_dias_perm_pagados + $e->total_perm_dias_pedidos}}</span></td>
                        <td><button type="button" class="button-style-custom">Ver</button></td> 
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('parametria.puestos.crear-editar-modal')

    </div>
    @include('includes.footer')


    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js'></script>
    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{ asset('js/datetimepicker/ui/i18n/ui.datepicker-es.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-multidatespicker/1.6.6/jquery-ui.multidatespicker.js"></script>
    <script>
        let dataSrc = [];
        let table = $('#idTable').DataTable({
            scrollY: '65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por puesto',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {

                let api = this.api();

                api.cells('tr', [1]).every(function() {
                    let data = $('<div>').html(this.data()).text();
                    if (dataSrc.indexOf(data) === -1) {
                        dataSrc.push(data);
                    }
                });
                dataSrc.sort();

                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value) {
                            api.search(value).draw();
                        }
                    });
                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            }
        });
    </script>
