@extends('layouts.empleado')
@section('tituloPagina', "Cuestionario para: " . Session::get('empleado')['nombre'].' '.Session::get('empleado')['apaterno'].' '.Session::get('empleado')['amaterno'])
@section('content')
<div class="row">
	<div class="col-md-12">
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col-lg-12 col-sm-12 col-xs-12  colorfondo" id="colorfnd" >
                    <div class="card">
                        <div class="card-header">
                            <div class="form-row">
                                <h5 id="info_titulo"></h5>
                            </div>                    
                        </div>
                        <form id="addinfo">
                        <!--<form action="{ {route('empleado.encuesta.registarRespuestas')}}" method="POST" enctype="multipart/form-data">-->
                            <!--@ csrf-->
                            <div class="card-body">
                                <input type="hidden" name="id_en" id="id_en" >
                                <div class="table-responsive col-md-12 " id="contenttbl" >
                                    <table class="table w-100"  id="tblforms">
                                        <thead>
                                            <tr>
                                                <th>Titulo</th>
                                                <th>Descripción</th>                       
                                                <th>Estatus</th>
                                                <th>Completado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div id="contenido_encuesta">
                                    <div class="form-row">
                                        <h2 id="titulo_encuesta"></h2>
                                    </div>
                                    <div class="form-row">
                                        <p id="descripcion_encustas"></p>
                                    </div>
                                    <div class="form-row">
                                        <div id="aviso_p">
                                            @if($empleado->avisos==0)
                                                <h6><strong>Al contestar la encuesta acepta nuestro aviso de privacidad para más información visita <a href="{{route('empleado.avisoprivacidadempleado')}}" target="_blank" >https://hrsystem.com.mx/aviso-privacidad</a></strong></h6>
                                                <div class="btn btn-warning btn-sm font-weight-bold confirmar-aviso" href="#" role="button">Aceptar Aviso de Privacidad</div>       
                                            @endif
                                        </div>
                                    </div>
                                    <br>
                                    <div id="contentItems">
                                    </div>
                                    <br>
                                    <div class="col-md-12" id="img-recurso" ></div>
                                    <br>
                                </div>
                            </div>
                            <div class="card-footer" id="btnfooter">
                                <div type="button" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="right" title="Regresar al menú" onclick="flag(true);"  id="regresar" >Regresar <span><i class="fas fa-undo"></i></span></div>
                                <div type="button" class="btn btn-warning btn-sm float-right"  id='guardar'>Guardar  <i class="fas fa-save"></i></div>
                                <!--<input type="submit" class="btn btn-warning btn-sm"  value="Guardar">-->
                            </div>
                        </form>
                    </div><!-- card -->
                </div><!--col card-->
            </div><!--row-->
        </div><!--container-->
    </div><!--col-->

</div><!--row-->

@endsection

@push('css')
<style>
.iconcustom{
    width:25px; 
    height:25px;
}
.center-icons{
    position: relative;
    left: 40%;
}
.center-info{
    margin-left: 21px;
}
.custominpt[readonly]{
    background-color: white;
    border-color: white;   
}
.custominpt{
    border: none;
    outline: none;
}
.label-success{
    background-color: rgb(56, 193, 114);
    color: white;
    border-radius: 2px;
}
.label-warning{
    background-color: #c82333;
    color: white;
    border-radius: 2px;
}
.centrar-text{
    margin-left: 35px;
}
</style>
@endpush

@push('scripts')
<script src="{{asset('js/parsley/parsley.min.js')}}"></script>
<!-- Cambiar idioma de parsley -->
<script src="{{asset('js/parsley/i18n/es.js')}}"></script>

<script>

$(document).ready(()=>{
    
    let table = $('#tblforms').dataTable({ // Aquí se carga la tabla que se muestra al inicio de la vista
                                            "aProcessing":true,
                                            "aServerside":true,
                                            "lengthChange": false, //Si se desea que se muestre el paginador hay que quitar esta línea
                                            "language": {
                                                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
                                            },
                                            "ajax":
                                            {
                                                type: "GET",
                                                url:"{{route('empleado.encuesta.obtieneDatosGenerales')}}",
                                                dataType :"json",
                                                error:function(e)
                                                {
                                                    console.log(e.responseText);
                                                }
                                            },
                                            "bDestroy": true,
                                            //"iDisplayLength": 5,//paginacion cada 5 registros
                                            "order":[[0,"desc"]],

                                        }).dataTable();
 
                                  

  
    $("#guardar").on('click',(e)=>{

        e.preventDefault();
        $.get("{{route('empleado.encuesta.obtenerInfoAviso')}}",function(data){
            const { avisos } = data;
            if(avisos != 0){
                let form,formData;
                form=$("#addinfo"); 
                if(form.parsley().isValid()){
            
                    formData = new FormData($("#addinfo")[0]);
                    $.ajax({
                                
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url:"{{route('empleado.encuesta.registarRespuestas')}}",
                        type: "post",
                        dataType: "html",
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: data =>{
                        
                            const {id_encuesta,idempleado,cierra_encuesta}=JSON.parse(data);
                        
                            alertify.success('Datos guardados exitosamente.!!');
                            let table = $('#tblforms').DataTable();
                            table.ajax.reload();
                            $("#contentItems").empty();
                            (cierra_encuesta!=1)? visualizaEncuesta(id_encuesta,idempleado) : window.location ="{{ URL::route('empleado.encuesta.inicio') }}";
                            
                        
                        },
                        error: data =>{
                            alertify.error('Error al registrar los datos, intentalo nuevamente.!!');
                        }
                    });
                }else{
                    form.parsley().validate();
                }
            }else{
                alertify.error('No has aceptado el aviso de privacidad, intentalo nuevamente.!!');
            }
        });
 
    });
    $(".confirmar-aviso").on('click',(e)=>{
        $.get("{{route('empleado.avisoprivacidadempleado')}}",function(data){
            $("#aviso_p").addClass('d-none');
        });
    });
    
    flag(true);
});

const  selecionIconos= arg =>{
    
    let variable,titulo_opc,params,param_pregunta,valor_pregunta,color_div,color_border;
 
    variable= arg.split("#");
    titulo_opc = variable[0];
    params = variable[1];
    param_pregunta=variable[2];
    
    color_border=document.getElementsByClassName(`color-item-${param_pregunta}`);
    for (let i=0; i < color_border.length; i++) color_border[i].style.border="none";
    
    
    if(localStorage.getItem('param_pregunta') == param_pregunta){
        color_div=document.getElementById(`div_select${localStorage.getItem('idcolor')}`);
        color_div.style.border='none';
    }

    valor_pregunta=document.getElementById(`valor_${param_pregunta}`);
    color_div=document.getElementById(`div_select${params}`);
    localStorage.setItem('idcolor',params);
    localStorage.setItem('param_pregunta',param_pregunta);
    
    valor_pregunta.value=titulo_opc;
    color_div.style.border='1px solid #1fc314';
    color_div.style.width='150px'; 
    color_div.style.borderRadius='2px'; 
};

const seleccion_radio = arg =>{
    
    let variable,valor,paramatro;
    variable= arg.split("#");
    valor = variable[0];
    paramatro = variable[1];
    document.getElementById(`valor_${paramatro}`).value=valor;
};

const visualizaEncuesta= (param,idempleado) =>{
    flag(false);

    document.getElementById('id_en').value=param;
    const url ='{{asset("public/svg")}}/';  // Url general de los iconos
    $('#spinner').removeClass('ocultar'); 
    $.get("{{route('empleado.encuesta.datoscuestionario')}}",{'param':param,'idempleado':idempleado} , data =>{
    
        const {titulo_encuesta,descripcion_encuesta} = data.datos_generales;
        
        document.getElementById('titulo_encuesta').innerHTML= titulo_encuesta;
        document.getElementById('descripcion_encustas').innerHTML= descripcion_encuesta;
        //document.getElementById('aviso_p').innerHTML= ``;

         data.preguntas.map((preguntas,i)=>{
            
            let info,pregunta,param_pregunta,valor,tipo,div,contador;

            info = preguntas.split('*#--#');
            contador=i+1;
            pregunta=info[0];
            param_pregunta=info[1];
            tipo=info[2];
       
            switch(tipo){

                case "2":

                    div=`<div class="content" id="radios${contador}">
                            <strong >Pregunta ${contador}: </strong>${pregunta} : 
                            <input type="hidden" name="respuestas[${param_pregunta}][]" id="valor_${param_pregunta}">
                            <input type="hidden" name="param_pregunta[]" value="${param_pregunta}">
                        </div>`;

                    $("#contentItems").append(div);

                    let compara_preguntas=0;  
                    data.datos_opc_preguntas[param_pregunta].map((opc_pregunta,i)=>{

                        let opc_info,titulo,param_opc_pregunta,radio,radios,titulo_opc,argumento,check;
                        
                        opc_info = opc_pregunta.split('*#--#');
                        titulo=opc_info[0];
                        param_opc_pregunta=opc_info[1];
                        
                        if(param_pregunta==param_opc_pregunta){
                            
                            let att="";
                            titulo_opc=titulo.replace(/\s/g,"_"); 
                            argumento=`${titulo_opc}#${param_pregunta}`;
                           
                            if(compara_preguntas==0){ 
                                att =`checked`;
                                seleccion_radio(argumento); 
                            }else{ 
                                att ='';
                            }
                            argumento=`'${titulo_opc}#${param_pregunta}'`;
                            radio=`<input class="form-check-input" type="radio" name="respuesta${param_pregunta}" value="${titulo}" onclick="seleccion_radio(${argumento});"  ${att}>`;
                            compara_preguntas++; 
                        }

           
                        radios=`<div class="form-check"> ${radio} <label class="form-check-label">${titulo}</label></div>`; 
                        $(`#radios${contador}`).append(radios);
                    });
                    
                break;
                case "3":
                    
                    div=`<div class="form-row" id="row_select${contador}"> 
                            <div class="form-group col-md-12 col-sm-12">
                                <strong >Pregunta ${contador}: </strong> ${pregunta}  :<input type="text" name="respuestas[${param_pregunta}][]" id="valor_${param_pregunta}" class=" custominpt" readonly >
                                <input type="hidden" name="param_pregunta[]" value="${param_pregunta}">
                            </div>
                        </div>`;

                    $("#contentItems").append(div);
                    data.datos_opc_preguntas[param_pregunta].map((opc_pregunta,i)=>{
                        
                        let opc_info,titulo,lleva_icono,params,icon,titulo_opc,argumento,select;
                        
                        opc_info = opc_pregunta.split('*#--#');
                        titulo=opc_info[0];
                        lleva_icono=opc_info[1];
                        params=opc_info[2];
                      
                        (lleva_icono==1) ? data.det_iconos[params].map((icons,i)=>{ icon=`<img src="${url}${icons}" class="iconcustom">` }) : icon='';
                        
                        titulo_opc=titulo.replace(/\s/g,"_"); 
                        argumento=`'${titulo_opc}#${params}#${param_pregunta}'`;
                       
                        select =`<div class="form-group col-md-2 col-sm-4"><div class="center-icons item_icono color-item-${param_pregunta}" onclick=selecionIconos(${argumento}); id="div_select${params}"><div class="center-info"> <label> ${titulo} </label> ${icon}</div></div> </div>`;
                        $(`#row_select${contador}`).append(select);               
                    });
                break;
                case "4":
                    div=`<div class="form-row" id="row_check${contador}"> 
                                <div class="form-group col-md-12 col-sm-12">
                                    <strong >Pregunta ${contador}: </strong> ${pregunta}  :<input type="hidden" name="respuestas[]" id="valor_${param_pregunta}" class=" custominpt" readonly >
                                    <input type="hidden" name="param_pregunta[]" value="${param_pregunta}">
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <table class="table" id="tblcheck${contador}" >
                                        <thead>
                                            <tr>
                                                <th>Selección</th>
                                                <th>Respuestas</th>                       
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>`;
                    $("#contentItems").append(div);

          
                    $(`#tblcheck${contador}`).append(`<tr>
                                                        <td><input type="checkbox" name="respuestas[${param_pregunta}][]" value="" disabled ></td>
                                                        <td><b>Seleccione alguna opción </b></td>
                                                    </tr>`);   
                    data.datos_opc_preguntas[param_pregunta].map((opc_pregunta,i)=>{
                        let opc_info,titulo,param_opc_pregunta;
                        
                        opc_info = opc_pregunta.split('*#--#');
                        titulo=opc_info[0];
                        param_opc_pregunta=opc_info[1];
                        let cols = `
                                    <tr>
                                        <td><input type="checkbox" name="respuestas[${param_pregunta}][]" value="${titulo}" ></td>
                                        <td>${titulo}</td>
                                    </tr>`;
                        $(`#tblcheck${contador}`).append(cols);   
                    });
                break;
                default :
                    div=`<div class="content">
                            <strong >Pregunta ${contador}: </strong> ${pregunta} :
                            <input type="hidden" name="param_pregunta[]" value="${param_pregunta}">
                            <input type="text" class="form-control form-control-sm " placeholder="Ingresa tu respuesta" name="respuestas[${param_pregunta}][]"> 
                        </div>
                        <br>`;
                    $("#contentItems").append(div);
                break;
            }
        });
        $("#spinner").addClass('ocultar');  
    });
};
const flag = param =>{

    let contenttbl,info_titulo,regresar,contenido_encuesta,img_humano;

    contenttbl=document.getElementById('contenttbl');
    info_titulo=document.getElementById('info_titulo');
    img_humano=document.getElementById('img-recurso');
    $("#contentItems").empty();
    
    if(param){
      
        contenttbl.style.display='block';
        info_titulo.innerHTML='Cuestionarios  asignados';
        img_humano.innerHTML ='<div class="text-center" > <img src="{{asset("public/img/recurso-humano-hr.png")}}" alt="Recurso humano"  width="50%"></div> ';

        $("#contenido_encuesta").hide();
        $("#btnfooter").hide();
       
        
    }else{
      
        contenttbl.style.display='none';
        info_titulo.innerHTML='Contesta la encuesta correspondiente';
        $("#contenido_encuesta").show();
        $("#btnfooter").show();
   
    }
}; 
</script>
<script>
    $('#spinner').addClass('ocultar');    
</script>
@endpush