<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
    //dd($ponderadores);   
    //$respuesta = $trabajador_diagnostico->respuestas;
    //dd($bloques);
    //print_r($bloques);
@endphp
<div class="container">
    <a href="{{route('norma.normaTabla')}}" data-toggle="tooltip" title="Regresar">
        @include('includes.back')
    </a>
    <label class="font-size-1-5em mb-5 under-line"><strong>Diagnóstico de empleados</strong></label>
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
            <div class="ml-3 mb-3">    
                <a href="{{route('norma.implementacion.diagnostico.exportarMenos16', $periodoNorma->id)}}" class="button-style" target="_blank"> Exportar</a>            
            </div>          
        </div>
        <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>
    </div>
    <div class="article border">
        <div class="table-responsive">
            <table class="diagnostico" id="diagnostico">
                <thead class="text-center">
                    <tr>
                        <th width='50px'>ID</th>
                        <th>Nombre</th>
                        <th>Guia I</th>
                        @foreach($bloques as $bloque)
                            @foreach($bloque->preguntas as $pregunta)
                                <th>{{$pregunta->pregunta}}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($trabajador_diagnostico as $key => $t)
                    <tr>
                        <td> {{$t['id']}} </td>
                        <td> {{$t['nombre']}} </td>
                        <td class="text-center">
                            @php
                                if($t['guiaI'] == "CANALIZAR"){ echo "<span class='text-secundary font-weight-bold alert-danger'>CANALIZAR</span>";}
                                else if($t['guiaI'] == "NO CANALIZAR"){ echo "<span class='text-secundary font-weight-bold alert-success'>APROBADO</span>";}
                                else{ echo "<span class='txt-secundary'>".$t['guiaI']."</span>";}                                
                            @endphp                            
                        </td>           
                        @foreach($bloques as $bloque)
                            @foreach($bloque->preguntas as $pregunta)                             
                                <td class="text-center">{{$t[$pregunta->id]}}</td>
                            @endforeach
                        @endforeach                        
                    </tr>                    
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th><input type="text"/></th>
                        <th><input type="text"/></th>
                        @foreach($bloques as $bloque)
                            @foreach($bloque->preguntas as $pregunta)
                                <th><input type="text"/></th>
                            @endforeach
                        @endforeach
                    </tr>
                </tfoot>
              
            </table>
        </div>
    </div>
    <!-- <form action="" role="form" id="diagnostico" method="post">
        @csrf
        <input type="hidden" id="implementacion" name="implementacion" value="{{$datosImplementacion->id}}"/>
        <input type="hidden" id="periodo_norma" name="periodo_norma" value="{{$periodoNorma->id}}" />      
    </form> -->
</div>
<script>
let table = $('#diagnostico').DataTable({    
        scrollY:'60vh',
        scrollX:'0',
        scrollCollapse: true,
        // paging:false,
        processing: true,
        // serverSide: true,
        lengthChange: false,
        columnDefs: [        
            //APLICAR ANCHO DE LAS COLUMNAS DE LA TABLA
            { width: "200px", targets: [1,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22] },           
        ],
        "language": {          
            search: '',
            searchPlaceholder: 'Buscar registros',
            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
        },
        initComplete: function () {
            // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
            elementos = $(".dataTables_filter > label > input").detach();  
            elementos.appendTo('#div_buscar');
            $("#div_buscar > input").addClass("input-style-custom");

            // Apply the search
            this.api().columns().every( function () {
                let that = this; 
                $( 'input', this.footer() ).on( 'keyup change clear', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( this.value )
                            .draw();
                    }
                } );
                $('input').attr("placeholder", "Buscar");
                $("input").addClass("input-style-custom");
            });
        },
    });
$(function () {    
});
</script>