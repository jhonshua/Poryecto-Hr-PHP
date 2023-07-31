<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    @php
    $array_tipo_tabla = [];
    foreach($impuestos as $i){
    if(!in_array($i->tipo_tabla, $array_tipo_tabla)){
    $array_tipo_tabla[] = $i->tipo_tabla;
    }
    }
    @endphp
    <div class="container">
        
    @include('includes.header',['title'=>'Tabla ISR',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-isr.png',
        'route'=>'bandeja'])       

        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                <div class="col-xs-12 col-md-12 col-lg-3 mb-3 ml-3 px-0">
                    <select name="" id="filtro" class="form-control input-style-custom select-clase" style="width: 100%!important;">
                        <option value="">TODOS</option>
                        @foreach ($array_tipo_tabla as $key => $t)
                        <option value="{{$t}}">{{$t}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="">
                    <!-- <a type="button" class="button-style ml-3 mb-3" href="{{url('parametria/crear-editar-isr')}}">Crear nuevo</a> -->
                    <button type="button" class="button-style ml-3 mb-3" data-toggle="modal" data-target="#isrModal" data-id=""> <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
                    <button type="button" class="button-style mb-3" data-toggle="modal" data-target="#importarModal" data-id=""> <img src="/img/icono-importar.png" class="button-style-icon">Importar</button>
                    <a href="{{route('parametria.isr.exportar')}}" class="button-style mb-3" target="_blank"> <img src="/img/icono-exportar.png" class="button-style-icon">Exportar</a>
                </div>
            </div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
        @endif


        @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif

        <div class="article border">
            <table class="table w-100 impuestos" id="tabla_isr">
                <thead>
                    <tr>
                        <th class="text-center">Tipo Tabla</th>
                        <th class="text-center">Límite Inferior</th>
                        <th class="text-center">Límite Superior</th>
                        <th class="text-center">Cuota Fija</th>
                        <th class="text-center">Porcentaje</th>
                        <th>Opciones</th>

                    </tr>
                </thead>
                <tbody class="">
                    @foreach ($impuestos as $impuesto)
                    <tr id="{{$impuesto->id}}" class="{{strtoupper($impuesto->tipo_tabla)}}">
                        <td class="text-center">{{strtoupper($impuesto->tipo_tabla)}}</td>
                        <td class="text-center">{{$impuesto->limite_inferior}}</td>
                        <td class="text-center">{{$impuesto->limite_superior}}</td>
                        <td class="text-center">{{$impuesto->cuota_fija}}</td>
                        <td class="text-center">{{$impuesto->porcentaje}}%</td>
                        <td class="text-center" width="100px">
                            <div style="display: inline-flex; width:100%">
                            <!-- <a href="{{route('parametria.editar.isr', [$impuesto->id])}}"> -->
                            <button class="editar btn btn-sm" alt="Editar prestación" title="Editar prestación" data-toggle="modal" data-target="#isrModal" data-id="{{$impuesto->id}}" data-tipo_tabla="{{$impuesto->tipo_tabla}}" data-limite_inferior="{{$impuesto->limite_inferior}}" data-limite_superior="{{$impuesto->limite_superior}}" data-cuota_fija="{{$impuesto->cuota_fija}}" data-porcentaje="{{$impuesto->porcentaje}}"><img src="/img/icono-editar.png" class="button-style-icon"></button>
                            <!-- </a> -->

                            <button data-id="{{$impuesto->id}}" class="btn borrar btn-sm" alt="Eliminar prestación" title="Eliminar prestación"><img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                      </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('parametria.isr.importar-modal')
        @include('parametria.isr.crear-editar-modal')

    </div>
    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_isr').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por tipo tabla',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [0]).every(function(){

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
            "drawCallback": function( settings ) {              
                $(".btn.borrar").click(function() {
                    let id = $(this).data('id');
                    validarBorrado(id);
                });
            },
        });

        table.order([0, 'desc']).draw();  
        $('#filtro').on('change', function() {
            table
                .columns(0)
                .search(this.value)
                .draw();          
        });

        $(function() {             
            $('.select-clase').select2();
        });
        
        function validarBorrado(id){ 
            swal({
                    title: "",
                    text: "¿Esta seguro de eliminar este registro?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,

                })
                .then((willDelete) => {
                    if (willDelete) {
                        borrarImpuesto(id);
                    }
                });        
        }
        
        function borrarImpuesto(id) {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {
                'id': id,
                '_token': CSRF_TOKEN
            }
            $.ajax({
                url: `{{route('parametria.isr.borrar')}}`,
                type: 'POST',
                data: data,
                dataType: 'JSON',
                beforeSend: function() {},
                success: function(response) {
                    if (response.ok == 1) {
                        swal("El registro se eliminó correctamente.", {
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
