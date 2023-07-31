<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
//print_r($empleados);
@endphp

<div class="container">
@include('includes.header',['title'=>'Precálculo de aguinaldo',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
        'route'=>'procesos.calculo.aguinaldo'])

        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                <div class="row col-sm-12 col-xs-12 col-md-12 col-lg-12 mb-3 ml-3 px-0">
                    <!-- <a href="{{route('procesos.calculo.aguinaldo')}}" class="button-style mr-3">Regresar</a> -->
                    <form action="" method="POST">
                        @csrf
                        <button type="submit" class="button-style btn-block recalcular mr-3 text-nowrap">Re-calcular aguinaldo</button>
                        @foreach ($deptos as $depto)
                            <input type="hidden" name="deptos[]" value="{{$depto}}">
                        @endforeach
                        <input type="hidden" name="impuestoanual" value="{{$impuestoanual}}">
                        <input type="hidden" name="ejercicio" value="{{$ejercicio}}">
                    </form>
                    <form action="{{route('procesos.exportar.aguinaldo')}}" method="POST" target="_blank">
                        @csrf
                        <button type="submit" class="button-style btn-block mr-3">Exportar</button>
                        @foreach ($deptos as $depto)
                            <input type="hidden" name="deptos[]" value="{{$depto}}">
                        @endforeach
                        <input type="hidden" name="impuestoanual" value="{{$impuestoanual}}">
                        <input type="hidden" name="ejercicio" value="{{$ejercicio}}">
                    </form>
                    <a href="#" class="button-style mr-3" data-toggle="modal" data-target="#importarModal">Importar</a>                    
                </div>
                <div class="">                   
                </div>
            </div>
            <div class="dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
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
            <table class="table w-100" id="tabla_preaguinaldo">
                <thead>
                    <th class="bg-light">ID</th>
                    <th class="bg-light">NOMBRE</th>
                    <th class="text-nowrap">FECHA ANTIGUEDAD</th>
                    <th class="text-nowrap">FECHA FISCAL</th>
                    <th class="text-nowrap">DÍAS A PAGAR</th>
                    <th class="text-nowrap">DÍAS FISCALES</th>
                    <th class="text-nowrap">AGUINALDO</th>
                    <th class="text-nowrap">TOTALPERCEPCIONES</th>
                    <th class="text-nowrap">AJUSTE ANUAL </th>
                    <th class="text-nowrap">ISR</th>
                    <th class="text-nowrap">PENSIÓN ALIMENTICIA</th>
                    <th class="text-nowrap">DESCUENTOS OTROS</th>
                    <th class="text-nowrap">TOTAL DEDUCCIONES</th>
                    <th class="text-nowrap">TOTAL</th>
                    @if ($sindical <= 0)
                        <th class="text-nowrap">BONO AGUINALDO</th>
                        <th class="text-nowrap">S PENSIÓN ALIMENTICIA</th>
                        <th class="text-nowrap">S DESCUENTOS OTROS</th>
                        <th class="text-nowrap">TOTAL A PAGAR</th>
                    @endif
                    <th class="text-nowrap">GUARDAR</th>
                </thead>
                <tbody class="">
                    @foreach ($empleados as $empleado)                
                        <tr class="GridViewScrollItem content" data-nombre="{{strtoupper($empleado->nombre_completo)}}">
                            <td class="bg-light">{{$empleado->id}}</td>
                            <td class="bg-light">{{$empleado->nombre_completo}}</td>
                            <td>{{formatoAFecha($empleado->fecha_antiguedad)}}</td>
                            <td>
                                <input type="date" id="fecha_fiscal" name="fecha_fiscal" value="{{$empleado->aguinaldo->fecha_fiscal !== '0000-00-00' ? $empleado->aguinaldo->fecha_fiscal : ''}}" class="form-control form-control-sm">
                            </td>
                            <td>{{round($empleado->aguinaldo->dias_aguinaldo, 2)}}</td>
                            <td>{{round($empleado->aguinaldo->dias_fiscales, 2)}}</td>
                            <td>${{number_format(round($empleado->aguinaldo->pago_aguinaldo, 2),2,'.',',')}}</td>
                            <td>${{number_format(round($empleado->aguinaldo->pago_aguinaldo, 2),2,'.',',')}}</td>
                            <td>${{number_format(round($empleado->aguinaldo->impuesto_anual, 2),2,'.',',')}}</td>
                            <td>${{number_format(round($empleado->aguinaldo->impuestos, 2),2,'.',',')}}</td>
                            <td>
                                <input type="number" name="pension_alimenticia" id="pension_alimenticia" value="{{$empleado->aguinaldo->pension_alimenticia}}" class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" name="descuentos_otros" id="descuentos_otros" value="{{$empleado->aguinaldo->descuentos_otros}}" class="form-control form-control-sm">
                            </td>
                            <td>
                                ${{number_format(round($empleado->aguinaldo->pension_alimenticia + $empleado->aguinaldo->descuentos_otros, 2),2,'.',',')}}
                            </td>
                            <td>
                                ${{number_format(round($empleado->aguinaldo->neto - ($empleado->aguinaldo->pension_alimenticia + $empleado->aguinaldo->descuentos_otros), 2),2,'.',',')}}
                            </td>

                            @if ($sindical <= 0)
                                <td>${{number_format(round($empleado->aguinaldo->importe2, 2),2,'.',',')}}</td>
                                <td>
                                    <input type="number" name="s_pension_alimenticia" id="s_pension_alimenticia" value="{{$empleado->aguinaldo->s_pension_alimenticia}}" class="form-control form-control-sm">
                                </td>
                                <td>
                                    <input type="number" name="s_descuentos_otros" id="s_descuentos_otros" value="{{$empleado->aguinaldo->s_descuentos_otros}}" class="form-control form-control-sm">
                                </td>
                                <td>${{number_format(round($empleado->aguinaldo->neto2, 2),2,'.',',')}}</td>
                            @endif

                            <td>
                                <button type="button" class="button-style guardarIndividual">Guardar</button>
                                <input type="hidden" name="id" id="id" value="{{$empleado->aguinaldo->id}}">
                            </td>
                        </tr>
                    @endforeach                    
                </tbody>
            </table>
        </div>
        
</div>
@include('procesos.calculo-aguinaldo.importar-aguinaldo-modal')
@include('includes.footer')
<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>
<script>
    let dataSrc = [];
    let table = $('#tabla_preaguinaldo').DataTable({
        scrollY:'65vh',
        scrollX: true,
        scrollCollapse: true,
        lengthChange: false,
        fixedColumns: { left: 2, right: 0 },    
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
            $('.guardarIndividual').click(function(){              
                guardar($(this));
            });
        },
    });
    $(function(){
        $('.recalcular').click(function(){
            $(this).text('Espere...');
        });
    });

    function guardar(btn){
        // btn = $(this);
        btn.attr('disabled', true).text('Espere...');

        fecha_fiscal           = btn.parents('tr').find('#fecha_fiscal').val();
        pension_alimenticia   = btn.parents('tr').find('#pension_alimenticia').val();
        descuentos_otros      = btn.parents('tr').find('#descuentos_otros').val();
        s_pension_alimenticia = btn.parents('tr').find('#s_pension_alimenticia').val();
        s_descuentos_otros    = btn.parents('tr').find('#s_descuentos_otros').val();
        id                    = btn.parents('tr').find('#id').val();

        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var url = "{{route('procesos.guardarDatosAdicionalesAguinaldo')}}";
        $.ajax({
            type: "POST",
            url: url,
            data:   {
                        '_token'               : CSRF_TOKEN,
                        'fecha_fiscal'          : fecha_fiscal,
                        'pension_alimenticia'  : pension_alimenticia,
                        'descuentos_otros'     : descuentos_otros,
                        's_pension_alimenticia': s_pension_alimenticia,
                        's_descuentos_otros'   : s_descuentos_otros,
                        'id'                   : id,
                    },
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    btn.attr('disabled', false).text('Guardar');
                    // alertify.success('El registro se actualizó correctamente.');
                    swal("El registro se actualizó correctamente.", {
                                icon: "success",
                            });
                } else {                    
                    swal("", "Ocurrió un error!", "error");
                }
            }
        });
    }
</script>
