<div class="modal" tabindex="-1" role="dialog" id="nueva_solicitud">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content"> 
        	<div class="modal-header">
				<h5 class="modal-title">Nueva Solicitud</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="form_nueva_solicitud" method="POST" action="{{route('empleados.guardar.vacaciones')}}" accept-charset="UTF-8" enctype="multipart/form-data"> 
					<input type="hidden" name="_token" value="{{ csrf_token() }}">     
					<input type="hidden" id="empleados_autoriza" name="empleados_autoriza"> 
					<input type="hidden" id="tipo_solicitud_nombre" name="tipo_solicitud_nombre"> 
					<div class="form-group">
						<select class="form-control input-style-custom select-clase" style="width: 100%!important;" name="tipo" id="tipo" required>    
							<option value="">Seleccione tipo de solicitud</option>     
							@foreach($datos_tipo_vac as $v)
								<option value="{{$v->id}}">{{$v->descripcion}}</option>                
							@endforeach
						</select> 
					</div>
					@php  $fecha = new \DateTime() @endphp
					<div class="form-group">
						<select class="form-control input-style-custom select-clase" style="width: 100%!important;" name="periodo" id="periodo" required>    
							<option value="">Seleccione el periodo</option> 
							@for($x = $fecha->format("Y"); $x>($fecha->format("Y")-3); $x--)                                                                       
								<option value="{{$x}}">{{$x}}</option>                                    
							@endfor
						</select> 
					</div>
					<div class="form-group">
						<select class="form-control input-style-custom select-clase" style="width: 100%!important;" name="empleado" id="empleado" required>  
							<option value="">Seleccione un empleado</option>      
							@foreach($datos_empleado as $e)
								<option data-id="" value="{{$e->id}}">{{$e->id}} {{$e->nombre}} {{$e->apaterno}} {{$e->amaterno}}</option>                
							@endforeach
						</select> 
					</div>
					<!--<div class="form-group">
						<select class="form-control input-style-custom select-clase" style="width: 100%!important;" name="autoriza_solicitud" id="autoriza_solicitud" required>  
							<option class="green-option" value="">Seleccione</option>      
							@ foreach($datos_empleado as $e)
								<option class="green-option" data-name1="{ {$e->nombre}} { {$e->apaterno}} { {$e->amaterno}}" value="{ {$e->id}}">{ {$e->id}} { {$e->nombre}} { {$e->apaterno}} { {$e->amaterno}}</option>                
							@ endforeach
						</select> 
					</div>-->
					<hr>
					<div class="form-group">
						<input type="text" id="txtAutoriza1" name="txtAutoriza1" class="form-control input-style-custom " placeholder="Autoriza persona" required>
					</div>
					<div class="form-group">
						<input type="text" id="txtAutoriza2" name="txtAutoriza2" class="form-control input-style-custom " placeholder="Autoriza persona 2 (Opcional)">
					</div>
					<div class="form-group">
						<input type="text" id="txtAutoriza3" name="txtAutoriza3" class="form-control input-style-custom" placeholder="Autoriza persona 3 (Opcional)">
					</div>
					<div class="form-group row justify-content-center">
						<label><strong>Seleccione las fechas de vacaciones. </strong></label>
					</div>
					<div class="form-group row justify-content-center">
						<div id="datepicker" name="datepicker" required></div>
		            </div>
					<div class="form-group row justify-content-center">
						<label><strong>Anotaciones</strong></label>
					</div>
					<div class="form-group">
    					<textarea class="form-control" id="textAreaNota" name="textAreaNota"></textarea>  
					</div>
					<div class="form-group row justify-content-center">
						<label><strong>Adjuntar Archivo</strong></label>
					</div>
					<div class="justify-content-center text-center">
						<div class="custom-file ">
							<input type="file" class="custom-file-input " name="file" id="input_subir_archivo" accept=".png, .jpg, .zip, .bmp, .tif, .pdf, .doc, .docx" required>
							<label class="custom-file-label" for="input_subir_archivo" id="archivo_text">Seleccionar archivo</label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-12 mt-3 text-center">
							<button type="button" class="button-style-cancel" data-dismiss="modal" aria-label="Close">Cancelar</button>
							<button type="submit" class="button-style" id="btn_subir">Guardar</button>
						</div>
					</div>
				  </form>
			</div>
        </div>
    </div>
</div>

@push('css')

<style>
	.elemento_selec {     
        display: flex;
        align-items: center;        
    }

	#textAreaNota{
		resize: none;
		width: 95%;
	}

	#autoriza_solicitud, #empleado{		
		width: 320px;
	}
	

</style>

@endpush


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var array_empleado_autoriza = [];
let elemento_auth = 0;
$(function(){

	$(".select-clase").select2();

    $('#nueva_solicitud').on('shown.bs.modal', function (e) {	
		
    });

	

	// $("#autoriza_solicitud").change(function(e)
	$("#btnAutoriza").click(function(e){
		elemento_auth++;				
		let id_empleado = $("#autoriza_solicitud").val();
		let id_empleado_aut = $("#autoriza_solicitud").val()+'_autoriza';
		let nombre_empleado = $("#autoriza_solicitud option:selected").text();
		
		if(elemento_auth <= 3){
			$(".lista_autoriza").append(`
				<div class="row justify-content-center form-inline form-group">
					<label class="control-label mx-1">Autoriza:</label>
					<div class="">
						<input type="text" class="form-control form-control-sm" placeholder="">
					</div>
				</div>
			`);
		}

		$(".eliminar_autorizaX").on("click", function(e){			
			let eliminar_elemento = $(e.currentTarget).data('id');
			// console.log(eliminar_elemento);
			$("#"+eliminar_elemento).remove();
			this.remove();
			array_empleado_autoriza = array_empleado_autoriza.filter(item => item !== eliminar_elemento.toString());			
			// $("#form_nueva_solicitud").append(`<input type="hidden" name="empleados_autoriza" value="`+array_empleado_autoriza+`">`);
			$("#empleados_autoriza").prop("value", array_empleado_autoriza);
			console.log(array_empleado_autoriza);
		});
		
	});

	$("#tipo").change(function() {
		$("#tipo_solicitud_nombre").prop("value", $("#tipo option:selected").text());
		// console.log($("#tipo option:selected").text());		
	});

});

</script>
