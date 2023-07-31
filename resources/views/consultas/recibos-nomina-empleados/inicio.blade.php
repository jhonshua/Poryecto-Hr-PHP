<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
    $array_tipo_tabla = [];
    foreach($periodos as $i){
        if(!in_array(strtoupper($i->nombre_periodo), $array_tipo_tabla)){
            $array_tipo_tabla[] = strtoupper($i->nombre_periodo);
        }
    }
@endphp
<div class="container">
    
@include('includes.header',['title'=>'Recibos de nómina',
        'subtitle'=>'Consultas', 'img'=>'img/icono-parametros-empresa.png',
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
        </div>
        <div class="dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
    </div>
    <div class="article border">
        @if (count($periodos) > 0)
            <table class="table w-100" id="tabla_recibos_nomina">
                <thead class="">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Periodo</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center"></th>
                        <th class="text-center">Id</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($periodos as $periodo)
                        <tr id="{{ $periodo->id }}">
                            <td class="text-center">{{ $periodo->numero_periodo }}</td>
                            <td class="text-center">{{ $periodo->nombre_periodo }}</td>
                            <td class="text-center">{{ $periodo->fecha_inicial_periodo }} - {{ $periodo->fecha_final_periodo }}</td>
                            <td class="text-center">
                                @if($periodo->especial !=0)
                                    <span class="badge badge-warning">Especial</span>
                                @else
                                    <span class="badge badge-light">Normal</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{route('timbrar.periodo.nomina.cerrada', $periodo->id)}}" class="btn btn-success" >
                                    <img src="{{asset('/img/icono-ver.png')}}" class="button-style-icon">
                                    Ver timbres
                                </a>
                            </td>
                            <td class="text-center">{{ $periodo->id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @else
            <div class="text-black-50 mt-4 font-weight-bold">NO HAY PERIODOS</div>
        @endif
    </div>
</div>
@include('includes.footer')

<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>
<script>
        let dataSrc = [];
        let table = $('#tabla_recibos_nomina').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por tipo',
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


    table.order([5, 'desc']).draw();
    $('#filtro').on( 'change', function () {
        table
            .columns( 1 )
            .search( this.value )
            .draw();
    });
$(function(){
    $('.select-clase').select2();
});
</script>
