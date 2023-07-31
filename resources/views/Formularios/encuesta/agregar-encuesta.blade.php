<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<link href="{{asset('css/datetimepicker/jquery.datetimepicker.min.css')}}" rel="stylesheet">
<div class="container">

@include('includes.header',['title'=>'Agregar encuesta',
        'subtitle'=>'Formularios', 'img'=>'img/header/norma/icono-norma.png',
        'route'=>'formularios.inicio'])
   
    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif
    <div class="mb-4"> 
        <div class="article border">
            <div class="row" >
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <form action="{{ route('formularios.agregarEditarEncuesta') }}" method="post" id="addform" >
                        @csrf
                        <p class="text-center text-secondary">Completa los campos para crear un nuevo formulario, estos campos son obligatorios</p>
                        <div class="form-group mb-4">
                            <label class="mb-0">Fecha de vencimiento:  <input type="checkbox" id="checkfecha"  onclick="checkFecha()" ></label><input type="hidden" name="valchecked" id="valchecked" value="0" >
                        </div>
                        <div class="form-group mb-4 d-none" id="div_fecha">
                            <input type="text" name="fecha_vencimiento" id="fecha_vencimiento" value="{{old('fecha_vencimiento', date('Y-m-d H:i'))}}" class="form-control input-style-custom">
                        </div>
                        <div class="form-group mb-4">
                            <select name="estatus"  class="form-control  input-style-custom" required>
                                <option value=""  >Seleccione si el formulario es</option>
                                <option value="1" >Activo</option>
                                <option value="2">Inactivo</option>
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <input type="text" class="form-control input-style-custom" name="titulo" placeholder="Ingrese un titulo para el formulario" required>
                        </div>
                        <div class="form-group mb-4">
                            <textarea cols="30" rows="5" name="descripcion"  placeholder="Ingrese una descripción para su formulario" class="form-control input-style-custom" required ></textarea>
                        </div>
                        <!--<div class="font-size-1em  center under-line">--> 
                            <p class="text-center text-secondary">Los siguientes campos son dinámicos de acuerdo con la pregunta que se van generando</p> <!--</div>-->
                        <br>
                        <div class="input-group mb-4">
                            <input type="text" class="form-control  input-style-custom" placeholder="Ingrese una pregunta"id="pregunta" >
                            <div class="input-group-append">
                                <button class="btn bg-color-yellow text-white dropdown-toggle btn-sm custombntipo" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><strong>Tipo</strong></button>
                                <div class="dropdown-menu" id="numberItems">
                                    @foreach ($data as $item)
                                        <a class="dropdown-item nitem" data-id="{{$item->id}}">{{$item->tipo}}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-4">
                            <input type="text" name="valor_preg" onkeypress="return soloNumeros(event)" class="form-control  input-style-custom" id="valor_preg" placeholder="Introduce el valor de la pregunta">
                        </div>
                        <!--<div class="font-size-1-5em  center under-line"></div>-->
                        <div id="contentItems"></div>
                        <br>
                        <div  class="center  button-style  w-10 d-none guardar-encuesta " id="guardar" >Guardar</div>
                    </form>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </div>
    @include('formularios.encuesta.modals.agregar-modal')
    <script src="{{asset('js/helper.js')}}"></script>
    <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
    <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.10.2/Sortable.min.js"></script>
    <script src="{{asset('js/datetimepicker/jquery.datetimepicker.full.min.js')}}"></script>
    <script>

        'use strict'

        let contPreguntas=0;
        let contColRadios=0;
        let countParamsRadios=3;
        let countColCheck=0;
        let countParamsCheck =0;
        let count_rows_select_title=4;
        let tipo_opc_select="";
        
        const obtieneIconos= "{{route('formularios.obtieneIconos')}}"; // Obtiene los nombres de los iconos registrados y se cargan en el select
        const obtieneDetallesIconos= "{{route('formularios.obtieneDetallesIconos')}}";  //Obtiene los iconos del select y los carga dependiendo de la opción seleccionada ta 
        const url ='{{asset("storage/configuracion-formularios/svg/")}}';  // Url general de los iconos


        $(document).ready(function() {

           let v= $('#fecha_vencimiento').datetimepicker({locale: 'es'});

            $('.nitem').on('click', function(){

                let idLista = $(this).data("id");
                let pregunta = document.getElementById('pregunta').value;
                let valorPregunta = document.getElementById('valor_preg').value;
                localStorage.removeItem('tipo_select');

                if( pregunta !=""  &&   valorPregunta !="" ){

                    if(idLista != 1){

                        $('#agregar-modal').modal({backdrop: 'static', keyboard: false});
                        $('#agregar-modal').modal('toggle');
                        document.getElementById('iconos').style.display='none';
                        if(idLista==2){

                            flagSelect(2); 
                        
                        }else if(idLista==3){
                            
                            flagSelect(3);
                        
                        }else{
                            
                            flagSelect(4);
                        }  // Aquí se manda a llamar el metodo flagSelect y dependiendo de lo que seleccione el usuario carga los radios o select por icono/titulos
                    
                    }else{
                    
                        agregarPreguntas(1,pregunta,valorPregunta ,contPreguntas,"",""); // Aquí se agrega el input de tipo texto dependiendo del tipo de pregunta a formular
                        contPreguntas++;
                    }
                }else{

                    swal({ title: "La pregunta o el valor están vacíos, intenta nuevamente.!", icon: "warning", button: "Cerrar" });
                
                }
            });

            $("#addparams_select").on('click',(e)=>{ //Aquí se agregan los items de selección por solo titulos si no escoge el usuario imagenes

                e.preventDefault();
                //if(count_rows_select_title < 6){
                    let rows= `<tr class="row_select_titles" id="row_select_titles${count_rows_select_title}">
                                    <td><div type="button"  data-toggle="tooltip" data-placement="right" title="Eliminar" onclick=eliminarTitulosSelect(${count_rows_select_title});><img src="{{asset('/img/eliminar.png')}}" class="button-style-icon"></td>
                                    <!--<td><input type='text' class='nitem_title custominp' value="${count_rows_select_title}" readonly></td>-->
                                    <td></td>
                                    <td><input type='text' class='form-control input-style-custom ntitles_select' id='title_selectm${count_rows_select_title}' required></td>
                                    <td><input type='text' class='form-control input-style-custom n_values'   id='items_valores${count_rows_select_title}' required></td>
                                </tr>`;
                    count_rows_select_title++;
                    $("#tblitemselect tbody").append(rows);
                //}
            });

            $("#addparams").on('click',(e)=>{ //Aquí se agregan radios buttons para una encuesta

                e.preventDefault();
                //if(contColRadios < 3){
                    let filas= `<tr class="rows_items" id="row${countParamsRadios}">
                                    <td><div type="button" class="btn btn-sm" data-toggle="tooltip" data-placement="right" title="Eliminar" onclick=eliminarParamRadios(${countParamsRadios});><img src="{{asset('/img/eliminar.png')}}" class="button-style-icon"></div></td>
                                    <!--<td><input type="text" class="nradios custom-inp" id="position_radio${countParamsRadios}" value="${countParamsRadios}" readonly></td>-->
                                    <td><input type="radio" disabled></td>
                                    <td> <input type="text" class="title_radios form-control input-style-custom   nradios_title" id="title_radios${countParamsRadios}" required></td>
                                    <td> <input type="text" class="form-control input-style-custom nradios_value" id="nradios_value${countParamsRadios}" required></td>
                                </tr>`;
                    countParamsRadios++;
                    contColRadios++;
                    $("#tblitems tbody").append(filas);
                //}

            });

            $("#addcheck").on('click',(e)=>{ //Aquí se agregan radios buttons para una encuesta

                e.preventDefault();
                //if(contColRadios < 3){
                let filas= `<tr class="rows_items" id="row${countParamsCheck}">
                                <td><div type="button" class="btn btn-sm" data-toggle="tooltip" data-placement="right" title="Eliminar" onclick=eliminarParamCheck(${countParamsCheck});><img src="{{asset('/img/eliminar.png')}}" class="button-style-icon"></div></td>
                                <!--<td><input type="text" class="nradios custom-inp" id="position_radio${countParamsCheck}" value="${countParamsCheck}" readonly></td>-->
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck1" disabled>
                                        <label class="custom-control-label" for="customCheck1"></label>
                                    </div>
                                </td>
                                <td><input type="text" class="title_check form-control input-style-custom  ntitles_check" id="title_check${countParamsCheck}" required></td>
                                <td><input type="text" class="form-control input-style-custom ncheck_value" id="ncheck_value${countParamsCheck}" required></td>
                            </tr>`;
                countParamsCheck++;
                countColCheck++;
          
                $("#tblchecklist tbody").append(filas);
                $("#btnaddparams").removeClass('d-none');
                //}

            });

            $('#iconos').change(function() {

                $("#tblitemselect tbody").empty();
                $.get(obtieneDetallesIconos,{'param':$(this).val()}, data =>{
                    //$("#tblitemselect").append(data);
                    data.map((resp,i)=>{
                        let cont = i+1;
                        const {id,icono,valor }=resp;
                        let rowImg = `<tr>
                                        <td><input type='hidden' class='nitemsicons custom-inp' value="${cont}" readonly>
                                            <input type='hidden' value="${id}" class='idicons'  id='idicono${cont}'>
                                            <input type='hidden'  value="${icono}" class='imgimgicono' id='imgicono${cont}'>
                                        </td>
                                        <td><img src='${url}/${icono}'  class='imgicon custom-icon' id='imgicon${cont}' ></td>
                                        <td><input type='text' class='form-control input-style-custom ntitles_select'  id='title_selectm${cont}' required ></td>
                                        <td><input type='text' class='form-control input-style-custom n_values'   id='items_valores${cont}' value="${valor}"  readonly ></td>
                                    </tr>`;
                         $("#tblitemselect").append(rowImg);
                    });   
                });

                $("#tblitemselect tbody").sortable( {
                    update: function( event, ui ) {
                        $(this).children().each(function(index) {

                            let position=index + 1;
                            $(".idicons",this).attr('id',`idicono${position}`);
                            $(".ntitles_select",this).attr('id',`title_selectm${position}`);
                            $(".imgimgicono",this).attr('id',`imgicono${position}`);
                            $(".imgicon",this).attr('id',`imgicon${position}`);
                            $(".nitemsicons",this).attr('value',position);
                            $(".n_values",this).attr('id',`items_valores${position}`);
                            
                        });
                    }
                });
            });

            $("#opc_select").change(function(){

                $("#tblitemselect tbody").empty();
                let value_conf =$(this).val();
                let select_icons=document.getElementById('iconos');
                tipo_opc_select=document.getElementById('tipo_opc_select').value=value_conf;
                localStorage.setItem("tipo_select",value_conf);
                if(value_conf==1){

                    select_icons.style.display='block';
                    select_icons.setAttribute('required',''); 
                    recargarSelect();

                }else{

                    $("#addparams_select").show();
                    select_icons.style.display='none';
                    select_icons.removeAttribute('required');    
                    for(let i=1; i < 4 ; i++ ){
                        let rows=`<tr>
                                    <td></td>
                                    <!--<td><input type='text' class='nitem_title custom-inp' value="${i}" readonly></td>-->
                                    <td></td>
                                    <td><input type='text' class='form-control input-style-custom ntitles_select' id='title_selectm${i}' required></td>
                                    <td><input type='text' class='form-control input-style-custom n_values'  id='items_valores${i}' required></td>
                                </tr>`;
                        $("#tblitemselect tbody").append(rows);
                    }       
                }
            });

            $('#btnaddparams').on('click',function(){
          
                let form = $("#addata");
                if (form.parsley().isValid()){
                        
                     
                    let titulo="", nparamsadd="", val_radios="", tipo_opciones, pregunta,val_preg, numero_aleatorio,numero_aleatorio2,val_check;
                    
                    tipo_opciones=document.getElementById('tipo_opciones').value;
                    console.log(tipo_opciones);
                    pregunta=document.getElementById('pregunta').value;
                    val_preg=document.getElementById('valor_preg').value;
                    numero_aleatorio= Math.floor(Math.random()*100);
                    numero_aleatorio2=Math.floor(Math.random()*100);
                    
                    if( tipo_opciones==1){

                        let cont_valores=0;
                        nparamsadd = document.getElementsByClassName("nradios_title"); 
                        agregarPreguntas(2,pregunta,val_preg,contPreguntas,numero_aleatorio,numero_aleatorio2);
                    
                        for(let x=0; x < nparamsadd.length; x++ ){
                            cont_valores=x+1;
                    
                            titulo= document.getElementById(`title_radios${cont_valores}`).value;
                            val_radios=document.getElementById(`nradios_value${cont_valores}`).value;
                            agregarColumnas(titulo,numero_aleatorio,val_radios,contPreguntas,numero_aleatorio2);

                        }
                    }else if(tipo_opciones==2){
                        
                        let count_nselect=0;
                        nparamsadd = document.getElementsByClassName("ntitles_select"); //ok
                        agregarPreguntas(3,pregunta,val_preg,contPreguntas,numero_aleatorio,numero_aleatorio2); 
                        for(let i=0; i < nparamsadd.length; i++ ){
                            count_nselect=i+1;
                            titulo= document.getElementById(`title_selectm${count_nselect}`).value;
                            let items_valores=document.getElementById(`items_valores${count_nselect}`).value;
                            let img="", idicon="";
                            if(tipo_opc_select!=2){
                                img=`<img src="${url}/${document.getElementById(`imgicono${count_nselect}`).value}" class="iconcustom">`;
                                idicon=document.getElementById(`idicono${count_nselect}`).value;
                            }
                            agregarItems(titulo,items_valores,img,idicon,numero_aleatorio,contPreguntas,numero_aleatorio2);                          
                        }
                    }else if(tipo_opciones==4){


                        //let contcheck = 0;
                        nparamsadd = document.getElementsByClassName("ntitles_check"); //ok
                        agregarPreguntas(4,pregunta,val_preg,contPreguntas,numero_aleatorio,numero_aleatorio2);
                        for(let x=0; x < nparamsadd.length; x++ ){
                            //contcheck=x+1;
                            //console.log(contcheck);
                            titulo= document.getElementById(`title_check${x}`).value;
                            val_check=document.getElementById(`ncheck_value${x}`).value;
                            agregarColCheck(titulo,numero_aleatorio,val_check,contPreguntas,numero_aleatorio2);

                        }
                
                    }

                    contPreguntas++;
                    const lista=document.getElementById('contentItems');
                    Sortable.create(lista,{});

                }else{
                    form.parsley().validate();
                }
            });

            $('.guardar-encuesta').click(function(){
       
                let form = $("#addform");
                if(form.parsley().isValid()){
                    $(this).text('Espere...');
                    $(this).prop('disabled', true);
                    $('#addform').submit();
                }else{
                    form.parsley().validate();
                }
            
            });
        });
        const agregarColumnas = (titulos,numero_aleatorio,val_radios,contador_preguntas,numero_aleatorio2)=>{

            let tabla=`<tr>
                            <td><input type="text" class="form-control input-style-custom" name='titulos_items_${numero_aleatorio}_2_${numero_aleatorio2}[]' value='${titulos}' required></td>
                            <td><input type="text" class="form-control input-style-custom" name='valores_items_${numero_aleatorio}_2_${numero_aleatorio2}[]' value='${val_radios}' required></td>
                            <td><input type="radio" disabled></td>
                        </tr>`;
            $(`#orden_radios${contador_preguntas}`).append(tabla);   
        };
        const agregarColCheck = (titulos,numero_aleatorio,val_check,contador_preguntas,numero_aleatorio2)=>{

            let tabla=`<tr>
                            <td><input type="text" class="form-control input-style-custom" name='titulos_items_${numero_aleatorio}_4_${numero_aleatorio2}[]' value='${titulos}' required></td>
                            <td><input type="text" class="form-control input-style-custom" name='valores_items_${numero_aleatorio}_4_${numero_aleatorio2}[]' value='${val_check}' required></td>
                            <td>
                                <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="customCheck1" disabled><label class="custom-control-label" for="customCheck1"></label></div>

                            </td>
                            </div>
                        </tr>`;
            $(`#orden_checklist${contador_preguntas}`).append(tabla);   
        };
            
        const agregarPreguntas = (tipo,pregunta,valor_preg,count,num_aleatorio,numero_aleatorio2)=>{
            
            let componente_principal = "",subcomponente = "",lleva_icono = "",componente_pregunta, input_pregunta="" ;
            switch (tipo){

                case 2: 
                    subcomponente=`<div><table class="table col-md-6" id="orden_radios${count}"></table></div>`;
                break;
                case 3:
                    lleva_icono=localStorage.getItem('tipo_select');
                    subcomponente=`<div><table class="table col-md-12" id="orden_items${count}"><tr></tr></table></div>`;
                break;
                case 4:
            
                    subcomponente=`<div><table class="table col-md-6" id="orden_checklist${count}"></table></div>`;
                break;
            }
            componente_principal=`<div class="content items div_componente" id="item${count}" >
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Pregunta</label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <input type="text" class="form-control input-style-custom" name="pregunta[]" value='${pregunta}' placeholder='Pregunta'required >
                                                <div class="input-group-prepend">
                                                    <div type="button" class="btn float-right" data-toggle="tooltip" data-placement="right" title="Eliminar" onclick=borrarItem(${count})><img src="{{asset('/img/eliminar.png')}}" class="button-style-icon"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Valor pregunta</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control input-style-custom" name="valor_preg[]" placeholder='valor de la pregunta'  value='${valor_preg}' required >
                                        </div>
                                    </div>
                                    <div class="form-group row d-none">
                                        <label class="col-sm-2 col-form-label">Tipo pregunta</label>
                                        <div class="col-sm-10">
                                            <input type="hidden" class="form-control input-style-custom" name="tipo_pregunta[]" value='${tipo}'>
                                        </div>
                                    </div>
                                    <div class="form-group row d-none">
                                        <label class="col-sm-2 col-form-label">Numero_aleatorio</label>
                                        <div class="col-sm-10">
                                            <input type="hidden" class="form-control input-style-custom" name="naleatorio1[]" value='${num_aleatorio}'>
                                        </div>
                                    </div>
                                    <div class="form-group row d-none">
                                        <label class="col-sm-2 col-form-label">Numero_aleatorio 2</label>
                                        <div class="col-sm-10">
                                            <input type="hidden" class="form-control input-style-custom" name="naleatorio2[]" value='${numero_aleatorio2}'>
                                        </div>
                                    </div>
                                    <div class="form-group row d-none">
                                        <label class="col-sm-2 col-form-label">Lleva incono</label>
                                        <div class="col-sm-10">
                                            <input type="hidden" class="form-control input-style-custom"  name="icono[]" value="${lleva_icono}">
                                        </div>
                                    </div>
                                    ${subcomponente}
                                    <div class="font-size-1-5em   under-line-custom"></div>
                                    <br>
                                </div>`;
                        
            $("#contentItems").append(componente_principal);
            
            document.getElementById('pregunta').value ="";
            document.getElementById('valor_preg').value ="";
            
            $("#agregar-modal").modal('hide'); 
            $("#guardar").removeClass('d-none'); 
        };

        const borrarItem=(index)=>{
    
            $("#item"+index).remove();
            contPreguntas--;
        
            if(contPreguntas == 0){
                contPreguntas=0;

                $("#guardar").addClass('d-none'); 
            }
        };
        const flagSelect=  param  =>{
            
            let div_radios=document.getElementById('div_radios');
            let div_selectm=document.getElementById('div_selectm');
            let div_checklist=document.getElementById('div_checklist');
            let instructions=document.getElementById('instructions');
            let tipo_opciones =document.getElementById('tipo_opciones');
            let config_select=document.getElementById('config_select');
            let opc_select=document.getElementById('opc_select');
            let iconos=document.getElementById('iconos');
            

            $("#tblitems tbody").empty();
            $("#tblitemselect tbody").empty();
            $("#btnaddparams").removeClass('d-none');

            if(param == 3){

                $('#opc_select').empty();
                $('#opc_select').append(`<option selected disabled hidden>Selecciona una configuración</option>
                                        <option value="1">Opción por iconos</option>
                                        <option value="2">Opción por títulos</option>`);

                $("#addparams_select").hide();
                opc_select.setAttribute('required','');
                config_select.style.display='block';
                instructions.innerHTML=` Ingrese los items correspondientes para su formulario puede tener 3 por defecto y agregar máximo 5 de acuerdo con la pregunta formulada, así mismo puede seleccionar sus iconos o solo seleccionar los títulos .`;
                document.getElementById('title_type').innerHTML='Personalización por selección múltiple';
                div_selectm.style.display="block";
                div_checklist.style.display="none";
                div_radios.style.display="none";
                tipo_opciones.value=2;
                
            }else if(param == 2){
                
                config_select.style.display='none';
                opc_select.removeAttribute('required');
                iconos.removeAttribute('required');
                document.getElementById('title_type').innerHTML='Personalización de radios';
                instructions.innerHTML=` Ingrese los radios correspondientes para tu formulario puedes tener 2 por defecto y agregar máximo 5 de acuerdo con la pregunta formulada .`;
                div_radios.style.display="block";
                div_checklist.style.display="none";
                div_selectm.style.display="none";
                tipo_opciones.value=1;

                for(let i=1;i < 3; i++ ){
                    let rows=`<tr><td></td>
                                    <!--<td><input type="text" class="custom-inp" id="position_radio${i}" value="${i}" readonly></td>-->
                                    <td><input type="radio"  name="r[]" disabled></td>
                                    <td><input type="text" class="form-control input-style-custom  nradios_title" id="title_radios${i}" required></td>
                                    <td><input type="text" class="form-control input-style-custom " id="nradios_value${i}" onkeypress="return soloNumeros(event)" required></td>
                                </tr>`;
                    $("#tblitems tbody").append(rows);
                }
            
            }else if(param == 4){

                $("#btnaddparams").addClass('d-none');    
                config_select.style.display='none';
                opc_select.removeAttribute('required');
                iconos.removeAttribute('required');
                instructions.innerHTML=` Ingrese los items correspondientes para su formulario puede tener n numero de preguntas para su selección.`;
                document.getElementById('title_type').innerHTML='Personalización por checklist';
                div_selectm.style.display="none";
                div_radios.style.display="none";
                div_checklist.style.display="block";
                tipo_opciones.value=4;

            }
        };

        const recargarSelect = async() =>{
    
            $("#spinner").removeClass('ocultar');
            $("#addparams_select").hide();                   
            
            let resp = await fetch(obtieneIconos);
            let data = await resp.json();
            
            $("#iconos").empty();
            $("#iconos").append("<option selected disabled hidden>Selecciona un icono</option>");
            $("#iconos").append(data);
            
            $("#spinner").addClass('ocultar');
        }



        // REORDENAMIENTO CUANDO UN RADIO SE ELIMINA DEL MODAL AGREGAR RADIOS 
        const eliminarParamRadios = index =>{

            $("#row"+index).remove();
            countParamsRadios--;
            contColRadios--;

            let nradios = document.getElementsByClassName("nradios");
            for(let x=0; x < nradios.length; x++ ){
                let item_position=3+x; 
                nradios[x].setAttribute('id',`position_radio${item_position}`);
                nradios[x].setAttribute('value',item_position);
            }

            let ntitles_radios=document.getElementsByClassName('title_radios');
            for(let x=0; x < ntitles_radios.length; x++ ){
                let item_position=3+x; 
                ntitles_radios[x].setAttribute('id',`title_radios${item_position}`);
            }

            let nradios_value=document.getElementsByClassName('nradios_value');
            for(let x=0; x< nradios_value.length; x++ ){
                let item_position_radios=3+x;
                nradios_value[x].setAttribute('id',`nradios_value${item_position_radios}`);
            }
        };
        const eliminarTitulosSelect= index =>{

            $("#row_select_titles"+index).remove();
            count_rows_select_title --;

            let nitem_title = document.getElementsByClassName("nitem_title");
            for(let x=0; x < nitem_title.length; x++ ){
                nitem_title[x].setAttribute('value',1+x);
            }
            let ntitles_select = document.getElementsByClassName("ntitles_select");
            for(let x=0; x < ntitles_select.length; x++ ){
                ntitles_select[x].setAttribute('id',`title_selectm${1+x}`);
            }
            let n_values = document.getElementsByClassName("n_values");
            for(let x=0; x < n_values.length; x++ ){
                n_values[x].setAttribute('id',`items_valores${1+x}`);
            }
        };

        const eliminarParamCheck = index =>{
            $("#row"+index).remove();
            countColCheck --;
            countParamsCheck --;
            if(countParamsCheck == 0){
                countParamsCheck=0;

                $("#btnaddparams").addClass('d-none'); 
            }
        }
        
        const agregarItems = (titulos,items_valores,img,idicon,numero_aleatorio,count_question,numero_aleatorio2)=>{
            let divImg ="";
            let col  ="col-md-12";
            if(img!=""){
                divImg=`<div class="form-group col-md-3">${img}</div>`;
                col  ="col-md-9";
            } 
            let tabla= `<td>              
                            <div class="form-row">
                                <div class="form-group ${col}">
                                    <input type="text" class="form-control input-style-custom" name='titulos_items_${numero_aleatorio}_3_${numero_aleatorio2}[]' placeholder="nombre" value='${titulos}' required >
                                </div>
                                ${divImg}
                            </div>
                            <input type="text" class="form-control input-style-custom " placeholder="valor"  name='valores_items_${numero_aleatorio}_3_${numero_aleatorio2}[]'   value='${items_valores}' required >
                            <input type="hidden" class="form-control input-style-custom " name='icons_${numero_aleatorio}_3_${numero_aleatorio2}[]' value='${idicon}'>
                            </td>`;
            
            $(`#orden_items${count_question}`).append(tabla);  
        };

    </script>
</div>
@include('includes.footer')
</body>
</html>