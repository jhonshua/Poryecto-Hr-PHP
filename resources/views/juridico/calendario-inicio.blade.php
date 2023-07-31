<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')
<style type="text/css">
	.top-line-black {
	    width: 19%; }
	a{
		color: black;
	}
</style>

<div class="container">
	@include('includes.header',['title'=>'Calendario de demandas', 'subtitle'=>'Juridico', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])

	<div class="article border mt-4">
	    <div id='calendar'></div>
	    <div class="modal" tabindex="-1" role="dialog" id="audienciaModal">
		    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
		        <div class="modal-content">
		            <div class="modal-header">
		                <h5 class="modal-title">Audiencia</h5>
		                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		                    <span aria-hidden="true">&times;</span>
		                </button>
		            </div>
		            <div class="modal-body">

		            </div>
		            <div class="modal-footer">
		              	<form method="post" action="{{ route('demandas.audienciainicio') }}" role="form">
		                	@csrf
		                	<input type="hidden" name="idAudiencia" id="idAudiencia">
		                	<input type="hidden" name="demanda_audiencia" id="idDemanda">
		                	<button type="button" class="btn button-style-cancel" data-dismiss="modal">Cerrar</button>
		                	<button type="submit" class="btn button-style">Ver audiencias</button>
		              	</form>
		            </div>
		        </div>
		    </div>
		</div>
	</div>

</div>
@include('includes.footer')
<link href="{{ asset('css/gantt/jquery-gantt.css') }}" rel="stylesheet">
<link href="{{ asset('css/calendario/fullcalendar.css') }}" rel="stylesheet">

<script src="{{ asset('js/highcharts/highcharts.js') }}"></script>


<script src="{{asset('js/moment/moment.js')}}"></script>
<script src="{{asset('js/moment/es.js')}}"></script>

<script src="{{asset('js/calendario/fullcalendar.js')}}"></script>
<script src="{{asset('js/calendario/es.js')}}"></script>

<script>
var audiencias = [];

@foreach($demandas as $demanda)
    var demanda = @json($demanda);

    demanda.audiencias.forEach(function(audiencia) {
    var tipo = "";
    	if(audiencia.pre == 0){
        	tipo = "Prejudicial";
      	}else if(audiencia.pre == 1){
        	tipo = "Judicial"
      	}else if(audiencia.pre == 2){
        	tipo = "Constitucional"
      	}else{
        	tipo = "¡?";
      	}

	    var hora = "00:00:00"
	   	if(audiencia.hora_audiencia != "" && audiencia.hora_audiencia != null){
	    	hora = audiencia.hora_audiencia;
    	}

    	var fecha = moment(audiencia.fecha_audiencia).format('YYYY-MM-DD hh:mm:ss');
    	audiencias.push({
        	id: audiencia.id,
          	title: 'Audiencia ' + tipo + " de " + demanda.nombre + " " + demanda.apaterno + " " + demanda.amaterno,
          	start: moment(audiencia.fecha_audiencia).format('YYYY-MM-DD') +" "+ hora,
          	constraint: audiencia.id, // defined below
          	color: '#f0c018',
          	classNames: ['audienciaMod'],
          	extendedProps: [{'fecha':audiencia.fecha_audiencia,'hora': hora,'empleado': demanda.nombre + " " + demanda.apaterno + " " + demanda.amaterno,'tipo':'Audiencia ' + tipo,'demanda':audiencia.id_demanda,'expediente':audiencia.expediente,'junta':audiencia.junta,'ciudad':audiencia.ciudad,'folioDem': demanda.folio}]
      	});
    },demanda)
@endforeach

console.log(audiencias);
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var hoy = moment();
    var calendar = new FullCalendar.Calendar(calendarEl, {
    	headerToolbar: {
        	left: 'prev,next today',
        	center: 'title',
        	right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
      	},
      	locale: "es",
      	initialDate: hoy.format(),
      	navLinks: true, // can click day/week names to navigate views
      	businessHours: true, // display business hours
      	editable: true,
      	selectable: true,
      	droppable: true,
      	eventClick: function(arg) {
        $(".modal-body").html('<ul class="list-group"><li class="list-group-item"><label for=""><b>Tipo audiencia: </b></label>&nbsp;<i>'+arg.event.extendedProps[0].tipo+'</i></li><li class="list-group-item"><label for=""><b>Empleado: </b></label>&nbsp;<i>'+arg.event.extendedProps[0].empleado+'</i></li><li class="list-group-item"><label for=""><b>idDemanda: </b></label>&nbsp;<i>'+arg.event.extendedProps[0].demanda+'</i></li><li class="list-group-item"><label for=""><b>idAudiencia: </b></label>&nbsp;<i>'+arg.event.id+'</i></li><li class="list-group-item"><label for=""><b>Folio: </b></label>&nbsp;<i>'+arg.event.extendedProps[0].folioDem+'</i></li><li class="list-group-item"><label for=""><b>Expediente: </b></label>&nbsp;<i>'+arg.event.extendedProps[0].expediente+'</i></li><li class="list-group-item"><label for=""><b>Junta: </b></label>&nbsp;<i>'+arg.event.extendedProps[0].junta+'</i></li><li class="list-group-item"><label for=""><b>Fecha: </b></label>&nbsp;<i>'+arg.event.extendedProps[0].fecha+' '+arg.event.extendedProps[0].hora+'</i></li></ul>');
        $("#idAudiencia").val(arg.event.id);
        $("#idDemanda").val(arg.event.extendedProps[0].demanda);
        $('#audienciaModal').modal('show');
        
      	},
      		events: 
        	audiencias
      	,
    	});

    calendar.render();
  });

</script>