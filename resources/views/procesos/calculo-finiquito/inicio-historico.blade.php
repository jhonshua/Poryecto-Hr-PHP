<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')
<style>
    .dataTables_ejercicio {
        float: left;
        text-align: left;
    }
    .wrapper-table{
        height: 530px;
        margin-bottom: 20px;
        overflow-y: scroll;
        width: 100%;
    }

    .finiquitos tr td .menu{
        box-shadow: #666 3px 3px 3px;
        background-color: #212529;
        display: none;
        list-style: none;
        left: -140px;
        padding: 0px;
        position: absolute;
        top: 30px;
        width: 190px;
        z-index: 10;
    }

    .finiquitos tr td .menu li{
        border-bottom: 1px solid #595c5f;
        padding: 5px 10px;
    }

    .finiquitos tr td .menu li:hover{
        background-color: #F0C018;
        color: #000;
        transition: background-color 0.5s ease-out;
    }

    .finiquitos tr td .menu li:hover a{
        color: #000;
    }

    .finiquitos tr td .menu a{
        color: #fff;
        text-decoration: none;
    }

    .top-line-black {
        width: 19%; }

</style>
<div class="container">


    @include('includes.header',['title'=>'Historico de finiquito',
            'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
            'route'=>'procesos.finiquito'])


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
		<div style="padding-top:20px;">
		    <div class="dataTables_ejercicio" style="display:none;width: 100%">
		        <form class="form-inline" id="form_ejercicio" action="{{ route('procesos.historico') }}" method="post">
		            @csrf    
		            <div class="form-group mb-2">
		                <label for="ejercicio" class="">Ejercicio: &nbsp</label>
		                <select class="form-control" name="buscar_ejercicio" id="buscar_eje">
		                    @foreach($ejercicios->get() as $ejercicio) 
		                        @if(!empty($buscar_ejercicio) && $buscar_ejercicio == $ejercicio->ejercicio)
		                            <option value="{{$ejercicio->ejercicio}}" selected><b>{{$ejercicio->ejercicio}}</b></option>
		                        @else
		                            <option value="{{$ejercicio->ejercicio}}">{{$ejercicio->ejercicio}}</option>
		                        @endif
		                    @endforeach
		                </select>
		            </div>
		            &nbsp;<button type="submit" class="btn button-style-cancel mb-2 btn-sm">Buscar</button>
		        </form>
		    </div>
		    <table class="table w-100 table-hover finiquitos" style="width:100%">
		        <thead>
		        <tr>
		            <th scope="col">No.Empleado</th>
		            <th scope="col">Nombre</th>
		            <th scope="col">Fecha Baja</th>
		            <th scope="col">Importe</th>
		            <th scope="col">Estatus</th>
		            <th scope="col">Departamento</th>
		            <th scope="col">Respondio encuesta</th>
		            <th scope="col">Kit</th>
		            <th scope="col">Opciones</th>
		        </tr>
		        </thead>
		    </table>
		    
			<form id="calculo_nomina" action="{{ route('procesos.finiquitover') }}" method="post">
		        @csrf 
		        <input type="hidden" name="id_empleado_calculo" id="id_empleado_calculo">
		        <input type="hidden" name="id_periodo_calculo" id="id_periodo_calculo">
		        <input type="hidden" name="ejercicio_calculo" id="ejercicio_calculo" value="{{$buscar_ejercicio}}">
		    </form>
		    {{-- {{route('procesos.calculo_finiquito.pdf.kit')}} --}}
		    <form id="imprimir" action="{{ route('procesos.calculo_finiquitopdfkit') }}" method="post" target="_blank">
		        @csrf     
		        <input type="hidden" name="idperiodo" id="idperiodo" />
		        <input type="hidden" name="ejercicio" id="ejercicio" />
		        <input type="hidden" name="numPeriodo" id="numPeriodo" />
		        <input type="hidden" name="idempleado" id="idempleado" />
		        <input type="hidden" name="idRutina" id="idRutina" />
		    </form>

		</div>
	</div>
</div>
@include('includes.footer')
@include('procesos.calculo-finiquito.firma-finiquito-modal')
@include('empleados_admin.kit-baja.archivos-modal')

<script src="{{asset('js/helper.js')}}"></script>
<script src="{{asset('js/typeahead.js')}}"></script>
<script>
    var table;
$(function(){


    table = $('.finiquitos').DataTable({
            processing: true,
            serverSide: true,
            lengthChange: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            ajax: "{{ route('procesos.historico',$buscar_ejercicio) }}",
            columns: [
                {data: 'idempleado', name: 'idempleado'},
                {data: 'nombre_completo', name: 'nombre_completo'},
                {data: 'fecha_baja', name: 'fecha_baja'},
                {data: 'neto_fiscal', name: 'neto_fiscal'},
                {data: 'estatus_firma_finiquito', name: 'estatus_firma_finiquito'},
                {data: 'departamento', name: 'departamento'},
                {data: 'encuesta', name: 'encuesta'},
                {data: 'kit', name: 'kit'},
                {data: 'acciones', name: 'acciones', orderable: false, searchable: false},
            ],
            order:  [ 0, 'desc' ],
            columnDefs: [
                { className: 'text-center', targets: [0,1,2,3,4,5] },
                
            ],
            rowId: 'idempleado'
        });
        // console.log(table);
        table.on( 'draw', function () {
            $(".dataTables_wrapper").prepend($(".dataTables_ejercicio").css("display",'block'));
           form_ejercicio_evento();
           eventosCalcula();
           $('.tooltip_').tooltip();
           $('.menubtn').hover(
                function(){ 
                    $('.finiquitos td .menu').fadeOut();
                    $(this).next('.menu').fadeIn(); 
                },
                function(){  }
            );

            $('.menu').hover(
                function(){ $(this).show(); },
                function(){ $(this).fadeOut(); }
            );

        } );

    $("#formArchivos").attr('action','{{route("procesos.finiquitosubirArchivos")}}')

});

function form_ejercicio_evento(){
    // $("#form_ejercicio").on("submit",function(){
    //     var url = "/calculo-finiquito/" + $("#e").val() ;
    //     window.location = url;
    //     return false;
    // })
}

function eventosCalcula(){
    $(".ver").off().on("click", function(){
        $("#id_empleado_calculo").val($(this).data("id"));
        $("#id_periodo_calculo").val($(this).data("idperiodo"));
        $("#ejercicio_calculo").val($(this).data("ejercicio"));
        $("#calculo_nomina").submit();
    });

    $(".imprimir").off().on("click", function(){

        $("#idempleado").val($(this).data("id"));
        $("#idperiodo").val($(this).data("idperiodo"));
        $("#numPeriodo").val($(this).data("numeroperiodo"));
        $("#idRutina").val($(this).data("rutina"));
        $("#ejercicio").val($(this).data("ejercicio"));
        
        let url ='{{route("procesos.encuestasalida")}}';
        console.log($(this).data("id"));
        const response = $.get(url,{'id':$(this).data("id")},function(data){
            
           (data==1) ? $("#imprimir").submit() : 
           swal("", "No se puede imprimir el documento porque no se ha contestado la encuesta de salida..!!", "warning");
           // alertify.error("No se puede imprimir el documento porque no se ha contestado la encuesta de salida..!!");
        });
    });
    /*
    
        <input type="hidden" name="idperiodo" id="idperiodo" >
        <input type="hidden" name="ejercicio" id="ejercicio" >
        <input type="hidden" name="numPeriodo" id="numPeriodo" >
        <input type="hidden" name="idempleado" id="idempleado" >
        <input type="hidden" name="idRutina" id="idRutina" >
    
     */
}
</script>