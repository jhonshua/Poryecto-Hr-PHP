<!-- Modal -->
<div class="modal fade" id="otorgarPermisoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="exampleModalLabel">Crear asistencia personal </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="permisoPersonal">
                    <form action="{{route('empleado.asistencias.permisoGeneral')}}" method="POST" id="form" >
                        <div class="modal-body row pb-2" >
                            @csrf
                            <input type="hidden" name="dia">
                            <div class="col-md-12 mt-1">
                                <label for="">Fecha de inicio del permiso: </label>
                                <input type="text" name="fecha_inicio" id="fecha_inicio"  class="form-control input-style-custom  mb-2 datepicker" placeholder="Seleccione una fecha de inicio" autocomplete="off" required>
        
                                <label for="">Fecha de fin del permiso: </label>
                                <input type="text" name="fecha_fin" id="fecha_fin" class="form-control input-style-custom  mb-4 datepicker" placeholder="Seleccione una fecha final" autocomplete="off" required>

                                <div class="asignar">

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_permiso" id="inasistencia" value="inasistencia" checked>
                                        <label class="form-check-label" for="inasistencia">
                                            Inasistencia
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_permiso" id="enfermedad" value="enfermedad">
                                        <label class="form-check-label" for="enfermedad">
                                            Enfermedad
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_permiso" id="vacaciones" value="vacaciones">
                                        <label class="form-check-label" for="vacaciones">
                                            Vacaciones
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_permiso" id="dia_festivo" value="dia_festivo">
                                        <label class="form-check-label" for="dia_festivo">
                                            Día Festivo
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_permiso" id="p_entrada" value="p_entrada">
                                        <label class="form-check-label" for="p_entrada">
                                            Permiso entrada:
                                        </label>
                                    </div>

                                    <div class="h_entrada d-none" >
                                        <div class="d-flex align-items-end" >
                                            <label for="p_entrada" class="text-nowrap">Nueva hora de entrada:</label>
                                            <input type="time" name="h_entrada" id="hora_entrada" value="09:00" class="form-control ml-2">
                                        </div>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_permiso" id="p_salida" value="p_salida">
                                        <label class="form-check-label" for="p_salida">
                                            Permiso salida:
                                        </label>
                                    </div>

                                    <div class="h_salida d-none" >
                                        <div class="d-flex align-items-end">
                                            <label for="p_entrada" class="text-nowrap">Nueva hora de salida:</label>
                                            <input type="time" name="h_salida" id="hora_salida" value="19:00" class="form-control ml-2">
                                        </div>
                                    </div>

                                </div>
        
                                <div class="mt-3">
                                    <label>Motivo del permiso:</label>
                                    <textarea class="form-control input-style-custom " name="motivo" id="motivo" rows="5" placeholder="Por favor detalle el motivo del permiso otorgado al empleado" required></textarea>
                                </div>
        
        
                                <input type="hidden" name="fecha_inicio_" id="fecha_inicio_" >
                                <input type="hidden" name="fecha_fin_"  id="fecha_fin_" >
                                <input type="hidden" name="empleados[]" id="idemp"> 
                                <input type="hidden" name="dia_" id="dia_" >
                                <br>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="button-style-cancel" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="button-style  guardar">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{asset('js/parsley/parsley.min.js')}}"></script>
<!-- Cambiar idioma de parsley -->
<script src="{{asset('js/parsley/i18n/es.js')}}"></script>
<script src="{{asset('js/datapicker-es.js')}}"></script>
<script>

    $(document).ready(function() {
       
       $("#fecha_inicio").datepicker({ 
                                       
            dateFormat: 'yy-mm-dd',                                           
            changeYear: true,
            changeMonth: true,
            onSelect: function (date) {      

                let fechaFinal=date.split("-");
                        
                $("#fecha_fin").prop('disabled', false);     
                $("#fecha_fin" ).val('');
                $("#fecha_fin" ).datepicker( "option", "minDate", date );
                $("#fecha_fin").datepicker({          
                                changeYear: true,
                                changeMonth: true,
                                dateFormat: 'yy-mm-dd',             
                                minDate: date
                });
                                            
            }
        });

        const radioButtons = document.querySelectorAll('input[name="tipo_permiso"]');
        for(const radioButton of radioButtons){
            radioButton.addEventListener('change', showSelected);
        }

        const entrada = document.querySelector(".h_entrada");
        const salida = document.querySelector(".h_salida");
        function showSelected(e) {
            if (this.checked && this.id == 'p_entrada') {
                entrada.classList.remove("d-none");
                salida.classList.add("d-none");
            }else if(this.checked && this.id == 'p_salida'){
                salida.classList.remove("d-none");
                entrada.classList.add("d-none");
            }else{
                entrada.classList.add("d-none");
                salida.classList.add("d-none");
            }

        }
                   
        $('.guardar').click(function(){
    
            let form = $("#form");
            if(form.parsley().isValid()){
                $(this).text('Espere...');
                $(this).prop('disabled', true);
                form.submit();
            }else{
                form.parsley().validate();
            }

        });
                   
        $(".btn.borrar").click(function(){
            var id = $(this).data('id');
            swal({
                title: `¿Está seguro de eliminar este tipo de  prestación?`,
                text: "Al realizar la acción ya no podrá recuperarla !",
                icon: "warning",
                buttons:  ["Cancelar", true],
                dangerMode: true,
            
            }).then((willDelete) => {
                if (willDelete) {
                    eliminarDatos(id).then(data=>{
                        
                        if(data.respuesta){
                            swal("Datos actualizados  correctamente!", {
                                icon: "success",
                            });
                            
                            location.reload();
                        }else{

                            swal("Error al desactivar los datos comunicate con tu adminstrador!", {
                                icon: "error",
                            });
                        }
                    }); 
                }
            });
        });
        
    });
                   
    const eliminarDatos = async id =>{
        
        let url = "{{route('parametria.prestaciones.borrar')}}";

        const response = await  fetch(url,{
            method: 'POST',
            headers: {'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json'},
            body: JSON.stringify({'id' : id})
        });
        
        const res = await response.json();
        return res;
    }
  </script>
  