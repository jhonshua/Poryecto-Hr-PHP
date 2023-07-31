<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')
	<style type="text/css">
	.top-line-black {
	    width: 19%;}
	</style>
<div class="container"> 

	@include('includes.header',['title'=>'Cálculo de finiquito',
	        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
	        'route'=>'bandeja'])

	<div class="row">
		<div class="col-md-6">
		    <div class="form-group mb-2">
		            	{{-- {{ route('procesos.finiquito.historico') }} --}}
		        <a href="{{ route('procesos.historico') }}" class="btn button-style-cancel mb-2 btn-sm">
		            <i class="fa fa-history tooltip_" data-toggle="tooltip"  title="HISTORICO FINIQUITO"></i> Historico</button>
		        </a>
		    </div>
		</div>
		<div class="col-md-6">
			 <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
		</div>
	</div>


	<div class="article border">
		<div style="padding-top:20px;">
		    <div class="dataTables_historico" style="display:none">

		    </div>
		    <table class="table col-md-12  finiquitos" style="width:100%">
		        <thead  >
		            <tr>
		                <th scope="col">Id Empleado</th>
		                <th scope="col">Nombre</th>
		                <th scope="col">Fecha baja</th>
		                <th scope="col">Opciones</th>
		            </tr>
		        </thead>
		        <tbody></tbody>
		    </table>
		    
		</div>
	</div>
</div>


    <form id="calculo_nomina" action="{{route('procesos.finiquitocalculadora')}}" method="post">
        @csrf 
        <input type="hidden" name="id_empleado_calculo" id="id_empleado_calculo">
        <input type="hidden" name="id_periodo_calculo" id="id_periodo_calculo">
        <input type="hidden" name="ejercicio_calculo" id="ejercicio_calculo">
    </form>


@include('procesos.calculo-finiquito.captura-finiquito-modal')
@include('includes.footer')

<script src="{{asset('js/helper.js')}}"></script>
<script src="{{asset('js/typeahead.js')}}"></script>
<script>
    var table;
$(function(){

    
    let dataSrc = [];

    table = $('.finiquitos').DataTable({
            processing: true,
            serverSide: true,
            lengthChange: false,
            language: {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            ajax: "{{ route('procesos.finiquito') }}",
            columns: [
                {data: 'idempleado', name: 'idempleado'},
                {data: 'nombre_completo', name: 'nombre_completo'},
                {data: 'fecha_baja_empleado', name: 'fecha_baja_empleado'},
                {data: 'acciones', name: 'acciones', orderable: false, searchable: false},
            ],
            order:  [ 2, 'desc' ],
            columnDefs: [
                { className: 'text-center', targets: [0,1,2,3] },
                
            ],
            rowId: 'id',
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
  
        table.on( 'draw', function () {
            eventoCalcula();
            $(".dataTables_wrapper").prepend($(".dataTables_historico").css("display",'block'));
            $('.tooltip_').tooltip();

        } );
    /*
  $("#finiquitar").on("click",function(){

      if(id != ""){
        var cadena = $('select[name="empleados"] option:selected').val();
        var empleado = cadena.split(",");
        var nombre = $('select[name="empleados"] option:selected').text();
        var id = empleado[0];
        var fecha_baja = empleado[1];
        var causa ="";
        console.log(empleado[0]);
        if(empleado[1] != "0000-00-00"){
            causa = empleado[2];
            causa_oficial = empleado[3];
        }
        $("#finiquitos").append('<tr id="tr'+id+'"><td>'+id+'</td><td>'+nombre+'</td><td><div class="btn-group"><span class="tooltip_" data-toggle="tooltip" title="CAPTURAR FINIQUITO"><a data-fechabaja="'+fecha_baja+'" data-causa="'+causa+'" data-causaoficial="'+causa_oficial+'" data-id="'+id+'" data-nombre="'+nombre+'" class="btn btn-warning btn-sm mr-2 captura" name="captura'+id+'" id="captura'+id+'" href="#" role="button" data-toggle="modal" data-target="#capturaFiniquitoModal">CAPTURAR</a></span><a data-fechabaja="'+fecha_baja+'" data-causa="'+causa+'" data-id="'+id+'" data-nombre="'+nombre+'" class="btn btn-warning btn-sm mr-2 calcula" name="calcula'+id+'" id="calcula'+id+'" href="#" role="button">CALCULAR</a></div></td></tr>');
        $("#empleados option[value='"+cadena+"']").remove();
        eventoCalcula();
      }
      return false;
  });*/
});

function eventoCalcula(){
    $(".calcula").off().on("click", function(){
        $("#id_empleado_calculo").val($(this).data("id"));
        $("#id_periodo_calculo").val($(this).data("idperiodo"));
        $("#ejercicio_calculo").val($(this).data("ejercicio"));
        $("#calculo_nomina").submit();
    });
}
</script>