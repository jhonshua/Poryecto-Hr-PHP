<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    @php
    $array_tipo_tabla = [];
    foreach($subsidios as $r){
    if(!in_array($r->tipo_tabla, $array_tipo_tabla)){
    $array_tipo_tabla[] = $r->tipo_tabla;
    }
    }
    @endphp

    <div class="container">
    @include('includes.header',['title'=>'Tabla subsidio',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-subsidio.png',
        'route'=>'bandeja'])
      
        @if(\Session::get('mensaje'))
        <div class="alert alert-info" role="alert" id="alerta">
            {!! \Session::get('mensaje') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            setTimeout(() => $('#alerta').fadeOut(), 10000);
        </script>
        @endif
        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                <div class="col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3 ml-3 px-0">
                    <select name="" id="filtro" class="form-control input-style-custom select-clase" style="width: 100%!important;">
                        <option value="">TODOS</option>
                        @foreach ($array_tipo_tabla as $key => $t)
                        <option value="{{$t}}">{{$t}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="">
                    <!-- <a type="button" class="button-style ml-3 mb-3" href="{{url('parametria/crear-editar-subsidio')}}">Crear nuevo</a> -->
                    <button type="button" class="button-style ml-3 mb-3 nuevo" data-toggle="modal" data-target="#subsidiosModal" data-id=""> <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
                    <button type="button" class="button-style mb-3" data-toggle="modal" data-target="#importarModal" data-id=""> <img src="/img/icono-importar.png" class="button-style-icon">Importar</button>
                    <a href="{{route('parametria.subsidio.exportar')}}" class="button-style mb-3" target="_blank"> <img src="/img/icono-exportar.png" class="button-style-icon">Exportar</a>
                </div>

            </div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <table class="table w-100 subsidios text-center" id="tabla_subsidios">
                <thead>
                    <tr>
                        <th class="text-center">Tipo tabla</th>
                        <th class="text-center">Para ingresos de</th>
                        <th class="text-center">Hasta ingresos de</th>
                        <th class="text-center">Subsidio</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach ($subsidios as $subsidio)
                    <tr id="{{$subsidio->id}}" class="{{$subsidio->tipo_tabla}}">
                        <td class="text-center">{{$subsidio->tipo_tabla}}</td>
                        <td class="text-center">{{$subsidio->ingreso_desde}}</td>
                        <td class="text-center">{{$subsidio->ingreso_hasta}}</td>
                        <td class="text-center">{{$subsidio->subsidio}}</td>
                        <td class="text-center" width="150px">
                            <!-- <a href="{{route('parametria.editar.subsidio', [$subsidio->id])}}"> -->
                            <button class="editar btn" alt="Editar subsidio" title="Editar subsidio" data-toggle="modal" data-target="#subsidiosModal" data-id="{{$subsidio->id}}" data-tipo_tabla="{{$subsidio->tipo_tabla}}" data-ingreso_desde="{{$subsidio->ingreso_desde}}" data-ingreso_hasta="{{$subsidio->ingreso_hasta}}" data-subsidio="{{$subsidio->subsidio}}"> <img src="/img/icono-editar.png" class="button-style-icon"></button>
                            <!-- </a> -->

                            <button data-id="{{$subsidio->id}}" class="borrar btn" alt="Eliminar subsidio" title="Eliminar subsidio"> <img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('parametria.subsidios.importar-modal')
        @include('parametria.subsidios.crear-editar-modal')
    </div>
    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_subsidios').DataTable({
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
                        borrarSubsidio(id);
                    }
                });
        }
        function borrarSubsidio(id) {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {
                'id': id,
                '_token': CSRF_TOKEN
            }

            $.ajax({
                url: `{{route('parametria.subsidio.borrar')}}`,
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
