<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
    //dd($ponderadores);
    $pondera = $ponderadores->categoriasYdominios;
@endphp
<div class="container">

@include('includes.header',['title'=>'Diagnostico de empleados',
        'subtitle'=>'Norma 035', 'img'=>'img/header/norma/icono-diagnostico.png',
        'route'=>'norma.normaTabla'])   

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
                <a href="{{route('norma.implementacion.diagnostico.exportar', $periodoNorma->id)}}" class="button-style" target="_blank"> <img src="/img/icono-exportar.png" class="button-style-icon"> Exportar</a>            
            </div>          
        </div>
        <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>
    </div>
    <div class="article border">
        <table class="table w-200 diagnosticos" id="tablaDiagnostico">
            <thead class="text-center">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Guia I</th>
                    @if(count($encabezado)>0)
                        <th>Guia II</th>
                        @foreach($encabezado as $categoria)
                            <th>{{$categoria}}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>            
                @foreach($trabajador_diagnostico as $key => $t)
                <tr>
                    <td> {{$t['id']}} </td>
                    <td> {{$t['nombre']}} </td>
                    <td class="text-center">
                        @php  
                            if($t['guiaI'] == "CANALIZAR"){ echo "<span class='font-weight-bold text-danger'>Canalizar</span>";}
                            else if($t['guiaI'] == "NO CANALIZAR"){ echo "<span class='font-weight-bold text-success'>Aprobado</span>";}
                            else{ echo "<span class='txt-secundary'>".$t['guiaI']."</span>";} 
                        @endphp
                    </td>
                    <td class="text-center">
                        @php
                            if($t['guiaII'] == "CANALIZAR") echo "<span class='font-weight-bold text-danger'>Canalizar</span>";
                            else if($t['guiaII'] == "NO CANALIZAR") echo "<span class='font-weight-bold text-success'>Aprobado</span>";
                            else echo "<span class='txt-secundary'>".$t['guiaII']."</span>"
                        @endphp
                    </td>

                    @if(!empty($pondera))                       
                        @foreach($pondera as $p => $cyd)
                            <td class="text-center">
                                @if($t[$p] == 'CANALIZAR')
                                    <span class='font-weight-bold text-danger'>Canalizar</span>
                                @elseif($t[$p] == 'NO CANALIZAR')
                                    <span class='font-weight-bold text-success'>Aprobado</span>
                                @else
                                    <span class='txt-secundary'>{{$t[$p]}}</span>
                                @endif
                            </td>
                        @endforeach
                    @else
                        <!-- @for($x=0; $x<12; $x++)
                            <td></td>
                        @endfor -->
                    @endif
                </tr>         
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th><input type="text"/></th>
                    <th><input type="text"/></th>
                    @if(count($encabezado)>0)
                        <th><input type="text"/></th>
                        @foreach($encabezado as $categoria)
                            <th><input type="text"/></th>
                        @endforeach
                    @endif
                </tr>
            </tfoot>
        </table>
    </div>
    <form action="" role="form" id="diagnostico" method="post">
        @csrf
        <input type="hidden" id="implementacion" name="implementacion" value="{{$datosImplementacion->id}}"/>
        <input type="hidden" id="periodo_norma" name="periodo_norma" value="{{$periodoNorma->id}}" />      
        <input type="hidden" id="tcuestionario" name="tcuestionario" value="{{$tcuestionario}}" />       
    </form>
</div>
@include('includes.footer')
<script>
let table = $('#tablaDiagnostico').DataTable({        
         
        scrollY:'60vh',
        scrollX:'0',
        scrollCollapse: true,
        // paging:false,
        processing: true,
        // serverSide: true,
        lengthChange: false,
        columnDefs: [        
            //APLICAR ANCHO DE LAS COLUMNAS DE LA TABLA
            { width: "200px", targets: [1,4,5,6,7,8,9,10,11,12,13,14,15] },           
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