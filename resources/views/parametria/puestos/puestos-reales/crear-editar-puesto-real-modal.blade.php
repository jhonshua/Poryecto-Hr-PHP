<div class="modal" tabindex="-1" role="dialog" id="puestosRealesModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content"> <!--style="border-radius: 10px;"-->
        <div class="modal-header">
            <h5 class="modal-title"><span></span>Anexar puesto real</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">
            <form method="post" id="puestos_form" action="{{route('parametria.puesto.real.guardar')}}">
                @csrf
                <label>Nombre del puesto alias:</label>
                <input type="text" name="puesto" id="puestoreal" class="form-control input-style-custom mb-3" required>
                <label for="">Puesto:</label>
                <div class="mb-2">
                    <select name="puesto_empresa" id="puesto_empresa" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" required>
                      
                    </select>
                </div>
                <label for="">Jerarquía:</label>
                <div class="mb-2">
                    <select name="jerarquia" id="jerarquia" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" required>
                    </select>
                </div>
                <div id="div-dep">
                    <label for="">Dependencia:</label>
                    <!-- <img src="http://127.0.0.1/HRSystem_v2/public/img/exclamation-mark-sign.png" width="11px" height="11px" alt="Áreas de apoyo" title="Áreas de apoyo"/> -->
                    <div class="mb-2">
                        <select name="dependencia" id="dependencia" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" placeholder="Nombre del puesto" required>
                        </select>
                    </div>
                </div>
                <label for="">Rama:</label>
                <div class="mb-2">
                    <select name="rama" id="rama" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" required>
                    </select>
                </div>

                <input type="hidden" name="id" id="id">
                <input type="hidden" name="id_detalle" id="id_detalle">
                <div class="text-center">
                    <button type="button" class="button-style" id="guardarbtn">Guardar</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>
<script>

    $(".select-clase").select2();
    
    $('#guardarbtn').click(function(){
        
        let form = $("#puestos_form");
        if(form.parsley().isValid()){
            $(this).text('Espere...');
            $(this).prop('disabled', true);
            form.submit();
        }else{
            form.parsley().validate();
        }
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
            $.get("{{route('parametria.puestos.obtenerPuestosPorJerarquia')}}",{'jerarquia':nivel},(data)=>{
            
                const {puestos} = data;
                puestos.map((item,i)=>{
              
                    const {id,puesto,alias} = item;
                    let puesto_valor;
                    let id_puesto ;
                    if(alias==""){
                        puesto_valor = puesto ;
                        id_puesto= id;
                    }else{
                        let cadena = alias.split('--');
                        let as = cadena[1];
                        id_puesto = cadena[0];                     
                        puesto_valor =`${puesto} -- (Alias) ${as}`;
                    } 

                    $("#dependencia").append(`<option value=${id}>${puesto_valor}</option>`);

                });
                $("#spinner").toggle();
            });
     
           
        }else{
            $("#dependencia").prop('required',false);
            $("#div-dep").addClass('d-none'); 
        }
    });

    $(".editar").on('click',function(){
        
        
        selectRama();
        selectPuestos();
        selectJerarquia();

        $("#puestoreal").val($(this).data('nombre'));
        $("#id_detalle").val($(this).data('id-detalle'));
        $("#id").val($(this).data('id'))
        $("#jerarquia").append(`<option selected value="${$(this).data('jerarquia')}">${$(this).data('jerarquia')}</option> `);
        $("#puesto_empresa").append(`<option selected value="${$(this).data('id-puesto')}">${$(this).data('puesto')}</option> `);
        $("#rama").append(`<option selected value="${$(this).data('rama')}">${($(this).data('rama')=="1") ?'front-end' : 'back-end'  }</option> `);
        if($(this).data('jerarquia')!="0"){

            $("#dependencia").attr("required", true);
            $("#div-dep").removeClass('d-none');
            $("#dependencia").empty()
            $("#dependencia").append(`<option selected value="${$(this).data('id-dependencia')}">${$(this).data('dependencia')} -- ${$(this).data('alias-dep')}</option> `);

        }else{
            $("#dependencia").empty()
            $("#dependencia").prop('required',false);
            $("#div-dep").addClass('d-none');    
        }

        
    });
    $(".crear").on('click',function(){

        $("#puestoreal").val("");
        $("#id").val("")
        $("#jerarquia").empty()
        $("#puesto_empresa").empty()
        $("#rama").empty()
        $("#dependencia").empty()

        selectRama();
        selectPuestos();
        selectJerarquia();
    })

    const selectJerarquia =()=>{ 
        $("#jerarquia").empty();
         $("#jerarquia").append(`<option value="" >Seleccione nivel de jerarquía</option>`);
        for( let i = 0; i <= 20; i++ ) $("#jerarquia").append(`<option value="${i}" >${i}</option>`); 
    }

    const selectPuestos=()=>{
        $("#puesto_empresa").empty();
        $("#puesto_empresa").append('<option value="" selected hidden disabled>Seleccione el puesto</option>');
        @foreach ($puestos_empresa as $puesto)
            $("#puesto_empresa").append(`<option value="{{$puesto->id}}">{{$puesto->puesto}}<option>`);
        @endforeach
       
    }
    const selectRama =()=>{
        $("#rama").empty();
        $("#rama").append(`<option value="" selected hidden disabled>Selecciona una rama</option>
                            <option value="1">Front-end</option>
                            <option value="2">Back-end</option>`);
    }
    selectRama();
    selectPuestos();
    selectJerarquia();
</script>