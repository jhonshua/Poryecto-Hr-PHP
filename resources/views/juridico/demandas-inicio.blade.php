<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<div class="container">
	@include('includes.header',['title'=>'Demandas', 'subtitle'=>'Juridico', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])


    @if (array_key_exists('demandas', Session::get('usuarioPermisos')))
        <a href="#" data-tipo="3" class="btn button-style  mb-3 mr-1" data-toggle="modal" data-target="#audienciaModal">Nueva audiencia masiva</a> 
    @endif

<div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>	
	<div class="article border mt-4">
        <table class="table" id="demandas" style="font-size:14px;"> 
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Folio</th>
                        <th>ID Empleado</th>
                        <th>Nombre Empleado</th>
                        <th>Ejercicio</th>
                        <th>Fecha Baja</th>
                        <th>Proxima Audiencia</th>
                        <th>TOTAL</th>
                        <th>Estatus</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                
                </tbody>
        </table>
	</div>

</div>

@include('includes.footer')
@include('juridico.demanda-modal')
@include('juridico.audiencia-modal')

<form action="{{route('demandas.audienciainicio')}}" role="form" id="accionDemanda" method="post">
        @csrf
        <input type="hidden" id="demanda_audiencia" name="demanda_audiencia" value="" />
</form>

<script src="{{asset('js/typeahead.js')}}"></script>
<script>
var tabla;
$(function(){
    
	let dataSrc = [];
    tabla = $('#demandas').DataTable({
            processing: true,
            serverSide: true,
            lengthChange: false,
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            ajax: {
                "url":"{{ route('demandas.inicio') }}",
                "type":"POST",
                "data": {'_token':"{{ csrf_token() }}"}
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'folio', name: 'folio'},
                {data: 'id_empleado', name: 'id_empleado'},
                {data: 'nombre', name: 'nombre'},
                {data: 'ejercicio', name: 'ejercicio'},
                {data: 'fecha_baja', name: 'fecha_baja'},
                {data: 'proxima_audiencia', name: 'proxima_audiencia'},
                {data: 'total', name: 'total'},
                {data: 'estatus', name: 'estatus'},
                {data: 'operaciones', name: 'operaciones', orderable: false, searchable: false},
            ],

            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [3]).every(function(){

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

    $("#demandas").on("click",".audiencia",function(){
        $("#demanda_audiencia").val($(this).data('demanda'));
        $("#accionDemanda").submit();
    });

    
    tabla.on( 'draw', function () {
        $("#spinner").addClass("ocultar");
        $('.tooltip_').tooltip();
        $(".dataTables_wrapper").prepend($(".dataTables_botones").css("display",'block'));

    });
    
});
</script>