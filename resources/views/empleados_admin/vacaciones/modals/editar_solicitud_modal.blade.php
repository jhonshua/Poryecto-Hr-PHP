<div class="modal" tabindex="-1" role="dialog" id="editar_solicitud">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content"> 
        	<div class="modal-header">
				<h5 class="modal-title">Editar Solicitud</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="form_editar_solicitud" method="POST" action="{{route('empleados.guardar.vacaciones')}}" accept-charset="UTF-8" enctype="multipart/form-data"> 
					<input type="hidden" name="_token" value="{{ csrf_token() }}">     
					<div class="form-group">
						<select class="form-control input-style-custom" style="width: 100%!important;" name="tipo" id="edit_tipo" required>    
							<option value="">Seleccione tipo de solicitud</option>     
							@foreach($datos_tipo_vac as $v)
								<option value="{{$v->id}}">{{$v->descripcion}}</option>                
							@endforeach
						</select> 
					</div>
					@php  $fecha = new \DateTime() @endphp
					<div class="form-group">
						<select class="form-control input-style-custom" style="width: 100%!important;" name="periodo" id="edit_periodo" required>    
							<option value="">Seleccione el periodo</option> 
							@for($x = $fecha->format("Y"); $x>($fecha->format("Y")-3); $x--)                                                                       
								<option value="{{$x}}">{{$x}}</option>                                    
							@endfor
						</select> 
					</div>
					<div class="form-group">
						<select class="form-control input-style-custom" style="width: 100%!important;" name="empleado" id="edit_empleado" required>  
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
						<input type="text" id="txtAutoriza1_editar" name="txtAutoriza1" class="form-control input-style-custom " placeholder="Autoriza persona" required>
					</div>
					<div class="form-group">
						<input type="text" id="txtAutoriza2_editar" name="txtAutoriza2" class="form-control input-style-custom " placeholder="Autoriza persona 2 (Opcional)">
					</div>
					<div class="form-group">
						<input type="text" id="txtAutoriza3_editar" name="txtAutoriza3" class="form-control input-style-custom" placeholder="Autoriza persona 3 (Opcional)">
					</div>
					<div class="form-group row justify-content-center">
						<label><strong>Seleccione las fechas de vacaciones. </strong></label>
					</div>
					<div class="form-group row justify-content-center">
						<div id="datepicker_edit" name="datepicker" required></div>
		            </div>
                    <div class="form-group row justify-content-center">
						<strong>*</strong><label class="text-danger"> Vuelva a seleccionar las fechas.</label>
					</div>	
					<div class="form-group row justify-content-center">
						<label><strong>Anotaciones</strong></label>
					</div>
					<div class="form-group">
    					<textarea class="form-control" id="edit_textAreaNota" name="textAreaNota"></textarea>  
					</div>
					<div class="form-group row justify-content-center">
						<label><strong>Adjuntar solicitud
                        	<a class="text-success" id="archivo_solicitud" name="archivo_solicitud" href="" target="_blank"></a> </strong></label>    
                        </strong></label>
					</div>
					<div class="justify-content-center text-center">
						<div class="custom-file ">
							<input type="file" class="custom-file-input " name="file" id="input_subir_archivo_edit" accept=".png, .jpg, .zip, .bmp, .tif, .pdf, .doc, .docx">
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function(){
        $('#editar_solicitud').on('shown.bs.modal', function (e) {		
            
        });
    
        var fechas_vac;   
        var fechas_vac_valida = [];     
        var date = new Date(); 
        $("#datepicker_edit").multiDatesPicker({
            changeYear: true,
            changeMonth: true,
            minDate: date,     
            beforeShowDay : $.datepicker.noWeekends,
            dateFormat: 'yy-mm-dd',
            // addDates: [date.setDate(13), date.setDate(23)],
            // addDates: ["23/04/2021", "21/04/2021"],
            // disabled: true,
            onSelect: function (date){
                fechas_vac_valida = $('#datepicker_edit').multiDatesPicker('getDates');
                fechas_vac = JSON.stringify($('#datepicker_edit').multiDatesPicker('getDates'));
                console.log(fechas_vac);
                
                if ( $("#fechas_datepicker_edit").length > 0 ){
                    $("#fechas_datepicker_edit").val("");			
                }			
                $("#form_editar_solicitud").append('<input type="hidden" id="fechas_datepicker_edit" name="fechas_datepicker" value="'+btoa(fechas_vac)+'">');			
            }            		
        });
    
        $('#form_editar_solicitud').submit( function(){

            let txtAutoriza_edit = $("#txtAutoriza1_editar").val();
            
            if(txtAutoriza_edit.trim().length>0){
                if(fechas_vac_valida.length > 0){
                    $('#btn_subir').attr('disabled', true).text('ESPERE...');
                    return true;
                }else{
                 
                    swal("", "Seleccione por lo menos una fecha.", "error");                 
                }
                
            }else{
                $( "#txtAutoriza1_editar").focus();
        
                swal("", "Ingrese al menos un nombre del responsable para autorizar.", "error"); 
                
            } 
            return false;
            
        });
    
        $("#edit_empleadoX").select2({
            tags: true,
            // matcher: function(term, text, option) {
            // 	return option.hasClass('car');
            // }
        });
    
        $("#edit_autoriza_solicitudx").select2({
            tags: true,
            // width : 'resolve'
        });
    
        $("#edit_tipo").change(function() {
            $("#tipo_solicitud_nombre_edit").prop("value", $("#edit_tipo option:selected").text());
            // console.log($("#edit_tipo option:selected").text());		
        });
    
    });
    
</script>
