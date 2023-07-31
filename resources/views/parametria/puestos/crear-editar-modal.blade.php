<div class="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" id="puestosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content"> <!--style="border-radius: 10px;"-->
        <div class="modal-header">
            <h5 class="modal-title"><span></span> puesto</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="puestos_form" action="{{route('parametria.guardar.puesto')}}">
                @csrf
                <label>Nombre del puesto:</label>
                <input type="text" name="puesto" id="puesto" class="form-control input-style-custom mb-3" required min="0">
                <label for="">Jerarquía:</label>
                <div class="mb-2">
                    <select name="jerarquia" id="jerarquia" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" required>
                    </select>
                </div>
                <div id="div-dep">
                    <label for="">Dependencia:</label>
                    <!-- <img src="http://127.0.0.1/HRSystem_v2/public/img/exclamation-mark-sign.png" width="11px" height="11px" alt="Áreas de apoyo" title="Áreas de apoyo"/> -->
                    <div class="mb-2">
                        <select name="dependencia" id="dependencia" class="form-control input-style-custom mb-2" style="width: 100%!important;" placeholder="Nombre del puesto" required>
                        </select>
                    </div>
                </div>
                <strong>Actividades del puesto:</strong>
                <div class="d-flex mb-3">
                    <input type="text" name="" id="actividad" class="form-control input-style-custom mr-2" placeholder="Actividades del puesto">           
                    <button class="btn bg-color-yellow text-white add" type="button"><strong>+</strong></button>
                </div>

                
                <div class="card p-3 actividades"></div>

                <div class="row">
                    <div class="col-md-12 mt-3 text-center">
                        <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                        <button type="submit" class="btn button-style guardar">Guardar</button>
                    </div>
                </div>

                <input type="hidden" name="id" id="id">
            </form>
        </div>
        </div>
    </div>
</div>

<script>
$(function(){

   

    $('.select-clase').select2({
		placeholder: {
		    id: '', // the value of the option
		    text: 'Seleccione uno o varios puestos reales'
		  },
		  allowClear: true
	});

    let accionLabel = '';
    let puesto = '';
    let jerarquia = '';
    let dependencia = '';
    let $actividad = `<div class="d-flex actividad mb-2">
                    <input type="text" name="actividades[]" class="form-control input-style-custom mr-2" value="**value**">    
                    <button class="btn bg-color-yellow text-white del" type="button"><strong>-</strong></button>
                </div>`;

    // al abrir el modal cargamos las prestaciones
    $('#puestosModal').on('shown.bs.modal', function (e) {
        
        let id = $(e.relatedTarget).data('id');
        $(".card.actividades").empty();
        $('#jerarquia').empty();

        $('#dependencia').empty();
        if(id == ''){
            accionLabel = 'Crear';
            puesto = dependencia = '';
            $(".card.actividades").empty();


            selectJerarquia();

        } else {

          
            accionLabel = 'Editar';
            puesto = $(e.relatedTarget).data('puesto').trim();
            jerarquia = $(e.relatedTarget).data('jerarquia');
            let val_jerarquia = jerarquia;
            jerarquia =`<option value=${jerarquia} selected> ${jerarquia}</option>`;
            if(val_jerarquia!=0){
                dependencia =`<option value=${$(e.relatedTarget).data('dependencia')} selected> ${$(e.relatedTarget).data('nombre-dependencia')}</option>`;
            }else{
                $("#dependencia").prop('required',false);
                $("#div-dep").addClass('d-none');  
            }

            
            actividades = $(e.relatedTarget).data('actividades').trim();

            $('#puestosModal .modal-body #jerarquia').append(jerarquia);
            $('#puestosModal .modal-body #dependencia').append(dependencia);
            selectJerarquia();
          
       


            actividades = actividades.split('##');
            
            actividades.forEach(actividad => {
                let act = $actividad;
                
                if(actividad !=""){
                    act = act.replace('**value**', actividad);
                    $(".card.actividades").append(act);
                    $('#actividad').val('');
                }
            });
            
        }

        $('#puestosModal .modal-title span').text(accionLabel);  
        $('#puestosModal .modal-body #id').val(id);
        $('#puestosModal .modal-body #puesto').val(puesto.trim());
    });

    // Agregar actividad fisica
    $('.btn.add').click(function(){
        if($('#actividad').val() != ''){
            let act = $actividad;
            act = act.replace('**value**', $('#actividad').val().trim());
            $(".card.actividades").append(act);
            $('#actividad').val('');
        }
    });

    // BOrrar actividad
    $('.actividades').on('click', '.btn.del', function(){
        $(this).parents('.actividad').remove();
    });

    // boton guardar
    $('#puestosModal').submit(function(){
        let valida = false;
        if($('#puesto').val().trim() != '') {            
            valida = true;
            $('#subsidiosModal .guardar').attr('disabled', true).text('Espere...');
        } else {            
            swal("", "Ingrese el nombre del puesto");
        }
        return valida; 
    });

    $("#dependencia").select2({             
        tags: true,
        // width : 'resolve'
    });
    $("#jerarquia").select2({             
        tags: true,
        // width : 'resolve'
    });
    $("#jerarquia").change('click',function(){
        
        let nivel = Number($(this).val()) - 1;
        (nivel < 0 ) ? nivel = 0 : nivel =nivel;

        if($(this).val()!="0"){

            $("#spinner").show();
            $("#dependencia").attr("required", true);
            $("#div-dep").removeClass('d-none');
            $("#dependencia").empty(); 
            $("#dependencia").append(`<option value=""  >Seleccione un puesto</option>`);     
            $.get("{{route('parametria.puestos.obtenerPuestos')}}",{'jerarquia':nivel},(data)=>{
            
                const {puestos} = data;
                puestos.map((item,i)=>{
                    const {id,puesto} = item;
                    $("#dependencia").append(`<option value=${id}>${puesto}</option>`);
                });

                $("#spinner").toggle();
            });
     
           
        }else{
            $("#dependencia").prop('required',false);
            $("#div-dep").addClass('d-none'); 
        }
    });



    const selectJerarquia =()=>{ 
     
        $("#jerarquia").append(`<option value="" >Seleccione nivel de jerarquía</option>`);
        for( let i = 0; i <= 20; i++ ) $("#jerarquia").append(`<option value="${i}" >${i}</option>`); 
    }

});
</script>
