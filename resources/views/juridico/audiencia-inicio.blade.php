<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')
<style>
        td.details-control {
            background: url("{{asset('img/details_open.png')}}") no-repeat center center;
            cursor: pointer;
        }
        tr.details td.details-control {
            background: url("{{asset('img/details_close.png')}}") no-repeat center center;
        }
        .row {
            margin-right: 0px; 
            margin-left: 0px; 
        }
</style>
<div class="container">
	@include('includes.header',['title'=>'Audiencias', 'subtitle'=>'Juridico', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'demandas.inicio'])


	<div class="article border mt-4">

		<h4>Datos de Demanda</h4>

		<div class="containertable" id="containertable" style="margin:35px 0 20px 0px;">
		    <table class="table   table-sm">

		        <tbody>
		            <tr class="" style="background-color: #fbba00;">
		                <th scope="col" >Folio</th>
		                <th scope="col" >Empleado</th>
		                <th scope="col" >Notificación Demanda</th>
		                <th scope="col" >Fecha Alta</th>
		                <th scope="col" >Fecha Alta cliente</th>
		                <th scope="col" >Fecha Baja</th>
		                <th scope="col" >ID Demanda</th>
		                <th scope="col" >Estatus</th>
		            </tr>
		            <tr>
		                <td>{{$demanda->folio}}</td>
		                <td>{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</td>
		                <td>{{$demanda->created_at}}</td>
		                <td>{{$empleado->fecha_alta}}</td>
		                <td>{{$empleado->fecha_antiguedad}}</td>
		                <td>{{$demanda->fecha_baja}}</td>
		                <td>{{$demanda->id}}</td>
		                <td>
		                    @if($demanda->estatus == 1)
		                         NUEVA
		                    @elseif($demanda->estatus == 2)
		                         AMPARO
		                    @elseif($demanda->estatus == 3)
		                         CONCILIADO
		                    @else
		                         -
		                    @endif
		                </td>
		            </tr>
		            <tr style="background-color: #fbba00;">
		                <th scope="col" >Importe</th>
		                <th scope="col" >Indeminización Const</th>
		                <th scope="col" >Indeminización Anual</th>
		                <th scope="col" >Salarios caidos</th>
		                <th scope="col" >Prestaciones devengadas</th>
		                <th scope="col" >Importe extra</th>
		                <th scope="col" >Honorarios</th>
		                <th scope="col" >Total</th>
		            </tr>
		            <tr>
		                <td>$ {{number_format($demanda->importe,2)}}</td>
		                <td>$ {{number_format($IndmConst,2)}}  ({{number_format($demanda->indemnizacion_constitucional)}} dias)</td>
		                <td>$ {{number_format($demanda->indemnizacion_anio,2)}}</td>
		                <td>$ {{number_format($demanda->salario_caido,2)}}</td>
		                <td>$ {{number_format($demanda->prestaciones_devengadas,2)}}</td>
		                <td>$ {{number_format($demanda->importe_extra,2)}}</td>
		                <td>$ {{$totales['honorarios']}}</td>
		                <td>$ {{$totales['total']}}</td>
		            </tr>
		        </tbody>
		    </table>
		</div>


<div class="" style="marging:20px 0px">
    <div class="dataTables_botones float-left" style="display:none">
        {{-- <a data-toggle="tooltip" href="{{route('sistema.juridico.demandas')}}" type="button" class="btn btn-dark font-weight-bold">Regresar</a>  --}}
        @if($demanda->estatus == 1)      
            <button  data-toggle="modal" data-tipo="0" data-target="#audienciaModal" type="button" class="btn button-style-cancel font-weight-bold">Nueva audiencia Judicial</button> 
            <button data-toggle="modal" data-tipo="1" data-target="#audienciaModal" type="button" class="btn btn-warning font-weight-bold" id="nueva-prejudicial">Nueva audiencia PRE-Judicial</button> 
        @endif
    </div>

    <div class="row" style="margin-top: 20px;">
	    
	    <table class="table table-striped table-sm" style="font-size:12px; margin-top: 45px;" id="tablaAudiencias">
	            <thead style="background-color: #fbba00;">
	                <tr>
	                    <th></th>
	                    <th>ID</th>
	                    <th>Junta</th>
	                    <th>Expediente</th>
	                    <th>Ciudad</th>
	                    <th>Fecha Notificacion</th>
	                    <th>Fecha Audiencia</th>
	                    <th>Proxima Audiencia</th>
	                    <th>Honorarios</th>
	                    <th>Tipo Audiencia</th>
	                    <th>Tipo de prueba</th>
	                    <th>Fecha de sentencia</th>
	                    <th>Sentido</th>
	                    <th>PRE</th>
	                    <th></th>
	                </tr>
	            </thead>
	            <tbody>
	    </table>
   
    </div>
</div>

	</div>


@include('juridico.audiencia-modal')


</div>


@include('includes.footer')

<link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.js"></script>
<script>
var tabla;
$(document).ready(function() {
    
    var groupColumn = 13;
    
    tabla = $('#tablaAudiencias').DataTable({
        processing: true,
        serverSide: true,
        lengthChange: false,
        searching:true,
        language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
        },
        ajax: {
            "url":"{{ route('demandas.audienciainicio') }}",
            "type":"POST",
            "data": {'_token':"{{ csrf_token() }}",'demanda':'{{$demanda->id}}'}
        },
        columns: [
            {
                "class":          "details-control",
                "orderable":      false,
                "data":           null,
                "defaultContent": ""
            },
            {data: 'id', name: 'id'},
            {data: 'junta', name: 'junta'},
            {data: 'expediente', name: 'expediente'},
            {data: 'ciudad', name: 'ciudad'},
            {data: 'fecha_aviso', name: 'fecha_aviso'},
            {data: 'fecha_audiencia', name: 'fecha_audiencia'},
            {data: 'fecha_proxima', name: 'fecha_proxima'},
            {data: 'costo_estimado_honorarios', name: 'costo_estimado_honorarios'},
            {data: 'tipo_audiencia', name: 'tipo_audiencia'},
            {data: 'tipo_prueba', name: 'tipo_prueba'},
            {data: 'fecha_sentencia', name: 'fecha_sentencia'},
            {data: 'sentido', name: 'sentido'},
            {data: 'pre', name: 'pre'},
            {data: 'operaciones', name: 'operaciones', orderable: false, searchable: false},
        ],
        "columnDefs": [
            { "visible": false, "targets": groupColumn }
        ],
        "order": [[ groupColumn, 'asc' ]],
        "displayLength": 25,
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
 
            api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group text-center" ><td colspan="14"><b>'+group+'</b></td></tr>'
                    );
 
                    last = group;
                }
            } );
        }
    });

    tabla.on( 'click', 'tr.group', function () {
        var currentOrder = tabla.order()[0];
        if ( currentOrder[0] === groupColumn && currentOrder[1] === 'asc' ) {
            tabla.order( [ groupColumn, 'desc' ] ).draw();
        }
        else {
            tabla.order( [ groupColumn, 'asc' ] ).draw();
        }
    } );
    $("#spinner").addClass('ocultar');

    var detailRows = [];

    tabla.on( 'click', 'tr td.details-control', function () {
        var tr = $(this).closest('tr');
        //console.log(tr);
        var row = tabla.row( tr );
        //console.log(row);
        var idx = $.inArray(tr.attr('id'), detailRows);
        //console.log(idx)
        if ( row.child.isShown() ) {
            tr.removeClass( 'details' );
            row.child.hide();
            // Remove from the 'open' array
            detailRows.splice( idx, 1 );
        }
        else {
            tr.addClass( 'details' );
            row.child( format(row.data())).show();
            // Add to the 'open' array
            if ( idx === -1 ) {
                detailRows.push( tr.attr('id') );
            }
        }
    });

    tabla.on( 'draw', function () {
        $.each( detailRows, function ( i, id ) {
        // alert(i+ " - " + id);
            $('#'+id+' td.details-control').trigger( 'click' );
        } );
        $(".dataTables_wrapper").prepend($(".dataTables_botones").css("display",'block'));
        const judiciales = tabla.columns( 13 ).data()[0].find(element => element === "AUDIENCIA JUDICIAL");
        if(judiciales != undefined){
            $("#nueva-prejudicial").remove();
        }


    } );


    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox({
        alwaysShowClose: true
      });
    });

 
    tabla.on("click",".eliminar-evidencia",function(){
        var evidencia = $(this).data("evidencia");
        var url = $(this).data("url");
        var masiva = $(this).data("masiva");
        // alertify.confirm('Evidencia ','¿Esta seguro de eliminar esta evidencia?',
        //         function(){ borrarEvidencia(evidencia,url,masiva); },
        //         function(){ alertify.alert().close(); }
        // );

		swal({
		  title: "¿Esta seguro de eliminar esta evidencia?",
		  icon: "warning",
		  buttons: true,
		  dangerMode: true,
		})
		.then((willDelete) => {
		  if (willDelete) {
		    swal("La evidencia se elimino correctamente!", {
		      icon: "success",
		    });
		    borrarEvidencia(evidencia,url,masiva);
		  } else {
		    swal("La acción se cancelo!");
		  }
		});


    });
});

function format (d) {
    var mensaje = d.evidencias;
    return '<div class="row justify-content-center"><div class="row col-md-12">'+mensaje+'</div></div>';
}

function borrarEvidencia(id,url,masiva) {
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    data = {
        'idEvidencia' : id,
        'url' : url,
        'masiva' : masiva,
        '_token': CSRF_TOKEN
    }

  	var url = "{{route('demandas.audienciaevidenciaborrar')}}";
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        dataType: 'JSON',
        success: function (response) {
            if(response.ok == 1) {
                $("#evi" + id).slideUp("slow",function(){
                    $(this).remove();
                });
                // alertify.success(response.msg);
                swal({
                    title: response.msg,
                    icon: "success",
                    button: "Ok",
                });

            } else {
                // alertify.alert('Error', response.msg);
                swal({
                    title: response.msg,
                    text: "Intente nuevamente!",
                    icon: "warning",
                    button: "Ok",
                });
            }
        }
    });
}

</script>