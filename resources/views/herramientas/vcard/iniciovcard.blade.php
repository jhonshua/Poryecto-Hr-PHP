@extends('layouts.principal')
@section('tituloPagina', "Configuración de vcard para: " . Session::get('empresa')['razon_social'])
@section('content')
<div class="row">
	<div class="col-md-12">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12" >
                    <form id="addinfo">
                        <div class="form-group">
                            <label>Dirección de empresa (*):</label>
                            <input type="text" class="form-control" name="direccion" id="direccion"  placeholder="Ej. Mar de China No 29, Popotla , Miguel Hidalgo CDMX" required/>
                        </div>
                        <div class="form-group">
                            <label>Dirección web (*):</label>
                            <input type="url" class="form-control" name="link_web[]" id="link_web" placeholder="Ej. https://www.dominio.com.mx/" required="">
                            <input type="hidden" class="form-control" name="idlinkweb[]" id="idireccionweb">
                        </div>
                        <!--<div class="form-group">
                            <label>Dirección web 2 (Opcional) :</label>
                            <input type="text" class="form-control" name="link_web[]" placeholder="https://www.singh.com.mx/" >
                        </div>
                        <div class="form-group">
                            <label>Dirección web 3 (Opcional) :</label>
                            <input type="text" class="form-control" name="link_web[]" placeholder="https://www.singh.com.mx/" >
                        </div>-->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Teléfono empresa (*):</label>
                                <input type="text" class="form-control" name="tel_empresa[]" id="telempresa"  placeholder="Ej. 552-435-2233" required data-parsley-regexphone="/^(\([0-9]{3}\)\s*|[0-9]{3}\-)[0-9]{3}-[0-9]{4}$/" >
                                <input type="hidden" name="idem[]" id="idem">
                            </div>
                            <div class="form-group col-md-2" id="extension">
                                <label>Ext :</label>
                                <input type="text" class="form-control" name="ext[]" id="ext"  placeholder="Ej. 555" required >
                            </div>
                            <div class="form-group col-md-4">
                                <label>Teléfono 1 (Opc) :</label>
                                <input type="text" class="form-control" name="tel_empresa[]"  id="telempresa1" placeholder="Ej. 552-435-2233" data-parsley-regexphone="/^(\([0-9]{3}\)\s*|[0-9]{3}\-)[0-9]{3}-[0-9]{4}$/" >
                                <input type="hidden" name="idem[]" id="idem1">
                            </div>
                            <div class="form-group col-md-2" id="extension1">
                                <label>Ext (Opc) :</label>
                                <input type="number" class="form-control" name="ext[]" id="ext1"  placeholder="Ej. 555" >
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Teléfono empresa 2 (Opc) :</label>
                                <input type="text" class="form-control" name="tel_empresa[]" id="telempresa2"  placeholder="Ej. 552-435-2233" data-parsley-regexphone="/^(\([0-9]{3}\)\s*|[0-9]{3}\-)[0-9]{3}-[0-9]{4}$/" >
                                <input type="hidden" name="idem[]" id="idem2">
                            </div>
                            <div class="form-group col-md-2" id="extension2">
                                <label>Ext (Opc) :</label>
                                <input type="number" class="form-control" name="ext[]" id="ext2"  placeholder="Ej. 555" >
                            </div>
                        </div>                      
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <span data-toggle="tooltip" data-placement="top" title="Color de fondo vcard"><div class="color-picker-fnd"></div></span>
                                <input type="hidden" name="colorfndinp" id="colorfndinp">
                            </div>
                            <div class="form-group col-md-2">
                                <span data-toggle="tooltip" data-placement="top" title="Color de fondo botonera"><div class="color-picker-btn"></div></span>
                                <input type="hidden" name="colorfndbtninp" id="colorfndbtninp">
                            </div>
                            <div class="form-group col-md-2">
                                <span data-toggle="tooltip" data-placement="top" title="Color de iconos de  botonera"><div class="color-picker-btnicons"></div></span>
                                <input type="hidden" name="coloricnbtninp" id="coloricnbtninp">
                            </div>
                            <div class="form-group col-md-2">
                                <span data-toggle="tooltip" data-placement="top" title="Color de fondo cuerpo vcard"><div class="color-picker-fndbodyvcard"></div></span>
                                <input type="hidden" name="fndbodyvcardinp" id="fndbodyvcardinp">
                            </div>
                            <div class="form-group col-md-2">
                                <span data-toggle="tooltip" data-placement="top" title="Color de fondo recuadró  lista interior"> <div class="color-picker-recfondlistint"></div></span>
                                <input type="hidden" name="recfondlistintinp" id="recfondlistintinp">
                            </div>
                            <div class="form-group col-md-1">
                                <span data-toggle="tooltip" data-placement="top" title="Color de letra recuadró  lista interior"> <div class="color-picker-recletlistinit"></div></span>
                                <input type="hidden" name="recletlistinitinp" id="recletlistinitinp">
                            </div>
                            <div class="form-group col-md-1">
                                <span data-toggle="tooltip" data-placement="top" title="Color de iconos recuadró  lista interior"> <div class="color-picker-reclistinticons"></div></span>
                                <input type="hidden" name="reclistinticonsinp" id="reclistinticonsinp">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Logo:</label>
                            <input type="file" class="form-control" name="logo_empresa_empleado"  id="logo_empresa_empleado">
                        </div>
                        <input type="hidden" name="idvcard" id="idvcard" value="">
                        <div type="button" class="btn btn-warning guardar mt-4" id='guardar'>Guardar</div>
                    </form>
                </div>
                <div class="col-lg-4 col-sm-12 col-xs-12  colorfondo" id="colorfnd" >
                    <br>
                    <div class="card">
                        <img src="{{asset("img")}}/logoletra.png" class="logocard" id="logocard">
                        <img class="card-img-top" src="https://pinkladies24-7.com/assets/images/defaultimg.png" alt="Hrsystem">
                        <div class="iconos">
                            <ul class="list-group list-group-horizontal col-lg-12 p-0">
                                <li class="list-group-item icono-top col-3 text-center btngroup">
                                    <a href="#">
                                        <i class="fas fa-phone-volume"></i>
                                    </a>
                                </li>
                                <li class="list-group-item icono-top col-3 text-center px-0 btngroup">
                                    <div class="divider">
                                        <a href="#">
                                            <i class="fa fa-envelope"></i>
                                        </a>
                                    </div>
                                </li>
                                <li class="list-group-item icono-top col-3 text-center px-0 btngroup">
                                    <div class="divider2">
                                            <a href="#">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </a>
                                        </div>
                                    </li>
                                <li class="list-group-item icono-top col-3 text-center btngroup">                                    
                                    <a href="#"> 
                                        <i class="far fa-address-card"></i>
                                    </a>
                                </li>
                                <a href="#"></a>
                            </ul>
                        </div>
                        <div class="card-body" id="cardbody">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item groupitem">
                                    <div class="list-icon"><i class="fas fa-user"></i></div>
                                    <div class="list-details">                                                                                       
                                        <div class='divider3'>
                                            <span>Rodrigo Javier . Salazar </span>
                                            <div class="divider3">
                                                <span>Desarrollador web</span>
                                            </div>
                                        </div> 
                                    </div>
                                </li>
                                <li class="list-group-item itemsgroup groupitem">
                                    <div class="list-icon"><i class="fas fa-phone-volume"></i></div>
                                    <div class="list-details">                                                                                       
                                        <div class='divider3'>
                                            <span>
                                                <a class='negro' href="#">556-878-5536</a>
                                            </span>
                                        </div> 
                                        <div class='divider3'>
                                            <span>
                                                <a class='negro' href='#'>556-878-5536</a>
                                            </span>
                                        </div> 
                                    </div>
                                </li>  <li class="list-group-item groupitem">
                                    <div class="list-icon"><i class="fas fa-mobile" ></i></div>
                                    <div class="list-details">
                                        <a class='negro' href='#'>556-878-5536</a>   
                                        <div class="divider3">
                                            <a class="wp" href="#">
                                                <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item groupitem">
                                    <div class="list-icon"> <i class="fa fa-envelope"></i></div>
                                    <div class="list-details">empresa@dominio.com.mx</span></div>
                                </li>
                                <li class="list-group-item groupitem">
                                    <div class="list-icon"> <i class="fa fa-map-marker-alt"></i></div>
                                    <div class="list-details">
                                        <div class="list-details">Paseo de la Emperatriz, Paseo Juárez, Paseo Degollado Ciudad de México</span></div>
                                    </div>
                                </li>
                                <li class="list-group-item groupitem">
                                    <div class="list-icon"> <i class="fa fa-globe"></i></div>
                                    <div class="list-details"> 
                                        <div class="list-details">https://www.empresa.com.mx/</span></div>                                        
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--@ include('herramientas.vcard.vcard_modal')-->
@endsection

@push('css')
<style>
.icono-top{
    background-color: black;
}
.list-group{
    background-color: black;
}
.btngroup a{
    color: #FFFFFF;
}
.groupitem{
    background-color: white;
}
.card-body{
    background-color:white;
}
.list-icon i{
    color:black
}
.logocard{
    width: 30%;
    position: absolute;
    z-index: 1;
    margin-top: 53px;
    margin-left: 66%;
}
.negro{
    color:black;
}
/*Styles default*/

.divider {
    display: inline-block;
    line-height:3px;
    width: 100%;
    border-left: 1px solid #ccc;
    border-right: 1px solid #ccc;
}
.divider2 {
    display: inline-block;
    line-height:3px;
    width: 100%;
    border-right: 1px solid #ccc;
}
.divider3 {
    display: inline-block;
    line-height:15px;
    margin-left:5px;
    border-left: 1px solid #ccc;
    padding-left: 5px;
}
.wp{
    color: green;
    display: inline;
    font-size: 1.5rem;
    margin-left: 10px;
}
.list-icon {
    display: table-cell;
    font-size: 18px;
    vertical-align: middle;
    color: #dee2e6;
    width: 45px;
    text-align: center;
}
.list-details {
	display: table-cell;
	vertical-align: middle;
	font-weight: 600;
    color: #223035;
    font-size: 13px;
    line-height: 15px;
    font-size: 10px;
}
.colorfondo{
    background-color:#E4E5ED;
}
</style>
@endpush

@push('scripts')
    <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
<script>
$(document).ready(()=>{

    $("#extension1").hide();
    $("#extension2").hide();

    const clase = ["color-picker-fnd",
                    "color-picker-fndbodyvcard",
                    "color-picker-btn",
                    "color-picker-recfondlistint",
                    "color-picker-recletlistinit",
                    "color-picker-btnicons",
                    "color-picker-reclistinticons"];
    clase.forEach(tipoclase=>{
        LoadColor(tipoclase);
    });

    document.getElementById('logo_empresa_empleado').onchange =()=> {

        let preview = document.querySelector('.logocard');
        let file    = document.querySelector('input[type=file]').files[0];
        let imgdefault="{{asset('/img')}}/logoletra.png";
        const  reader  = new FileReader();
        reader.onloadend =()=>{
            preview.src = reader.result;
        }
        if(file){ 
            let validacion = /(.png)$/i;
            if(!validacion.exec(document.getElementById('logo_empresa_empleado').files[0].name)){
                preview.src =imgdefault;
                document.getElementById('logo_empresa_empleado').value='';
                alertify.error('El logotipo no es un archivo png, intentalo nuevamente.!!');
            }else{
                reader.readAsDataURL(file);
            } 
        }else{  
            preview.src =imgdefault;
        }
    }
    reloadtata();
});
const LoadColor=(tipoclase)=>{
    const pickr = Pickr.create({
                                el:`.${tipoclase}`,
                                theme: 'classic', // or 'monolith', or 'nano'
                                swatches: [
                                    'rgba(244, 67, 54, 1)',
                                    'rgba(233, 30, 99, 0.95)',
                                    'rgba(156, 39, 176, 0.9)',
                                    'rgba(103, 58, 183, 0.85)',
                                    'rgba(63, 81, 181, 0.8)',
                                    'rgba(33, 150, 243, 0.75)',
                                    'rgba(3, 169, 244, 0.7)',
                                    'rgba(0, 188, 212, 0.7)',
                                    'rgba(0, 150, 136, 0.75)',
                                    'rgba(76, 175, 80, 0.8)',
                                    'rgba(139, 195, 74, 0.85)',
                                    'rgba(205, 220, 57, 0.9)',
                                    'rgba(255, 235, 59, 0.95)',
                                    'rgba(255, 193, 7, 1)'
                                ],
                                components: {
                                    // Main components
                                    preview: true,
                                    opacity: true,
                                    hue: true,
                                    // Input / output Options
                                    interaction: {
                                        hex: true,
                                        rgba: true,
                                        hsla: false,
                                        hsva: false,
                                        cmyk: false,
                                        input: true,
                                        clear: false,
                                        save: true
                                    }
                                }
    });
    pickr.on('change', instance => {

        let colors=instance.toRGBA();
        let iscolor=`rgba(${colors[0]},${colors[1]},${colors[2]},${colors[3]} )`;
        switch(tipoclase){
            case 'color-picker-fnd': //Color de fondo general de la vcard
                this.colorfnd.style.backgroundColor =iscolor;
                document.getElementById('colorfndinp').value=iscolor;
            break;
            case 'color-picker-fndbodyvcard': //Color de fondo cuerpo vcard
                this.cardbody.style.backgroundColor =iscolor;
                document.getElementById('fndbodyvcardinp').value=iscolor;
            break;
            case 'color-picker-btn': //Color de fondo botonera
                $(".icono-top").css("background-color", iscolor);
                $(".list-group").css("background-color",iscolor);
                document.getElementById('colorfndbtninp').value=iscolor;
            break;
            case 'color-picker-recfondlistint': //Color de fondo recuadró  lista interior
                $(".groupitem").css("background-color", iscolor);
                document.getElementById('recfondlistintinp').value=iscolor;
                
            break;
            case 'color-picker-recletlistinit': //Color de letra recuadró  lista interior
                $(".list-details").css("color",iscolor);
                $(".negro").css("color", iscolor);
                document.getElementById('recletlistinitinp').value=iscolor;
            break;
            case 'color-picker-btnicons': //Color de iconos de  botonera
                $(".btngroup a").css("color", iscolor);
                document.getElementById('coloricnbtninp').value=iscolor;
            break;
            case 'color-picker-reclistinticons': //Color de iconos recuadró  lista interior
                $(".list-icon i").css("color",iscolor);
                document.getElementById('reclistinticonsinp').value=iscolor;
            break;
        }
    });
};
$("#telempresa1").on('keyup',()=>{
   if(document.getElementById('telempresa1').value==""){ 
       
        $("#extension1").hide();
        $("#extension1").value('');

    }else{
         
        $("#extension1").show();
    }  
});
$("#telempresa2").on('keyup',()=>{
   if(document.getElementById('telempresa2').value==""){ 
        $("#extension2").hide();
        $("#extension2").value(''); 
    }else{ 
        $("#extension2").show();
    }  
});
$("#guardar").on('click',(e)=>{
    e.preventDefault();
    let form = $("#addinfo");
    if (form.parsley().isValid()){
        let formData = new FormData($("#addinfo")[0]);
        $.ajax({
                    
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:"{{route('herramientas.agregarVcard')}}",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:(data)=>{
                $('#addinfo').parsley('reset');
                alertify.success('Datos guardados exitosamente.!!');
                document.getElementById("addinfo").reset();
                resetInputs();
                reloadtata();
            },
            error:(data)=>{
                alertify.error('Error al registrar los datos, intentalo nuevamente.!!');
            }
        });
    }else{
        form.parsley().validate();
    }
});
const reloadtata=()=>{  //Carga los datos a modificar cuando la vcard ya esta insertada
    $.get("{{route('herramientas.getExistCard')}}",(data)=>{
        if(data!=""){
            
            const {idempresa,
                    idvcard,
                    direccion,
                    logo_empresa_empleado,
                    colorfndbtninp,
                    colorfndinp,
                    coloricnbtninp,
                    fndbodyvcardinp,
                    recfondlistintinp,
                    recletlistinitinp,
                    reclistinticonsinp, 
                    links,
                    telefonos,
                    idtel}=data;
                    
            document.getElementById('idireccionweb').value=links[0];        
            document.getElementById('link_web').value=links[1];
            document.getElementById('idvcard').value=idvcard;
            document.getElementById('direccion').value=direccion;
            document.getElementById('colorfndinp').value=colorfndinp;
            document.getElementById('colorfndbtninp').value=colorfndbtninp;
            document.getElementById('coloricnbtninp').value=coloricnbtninp;
            document.getElementById('fndbodyvcardinp').value=fndbodyvcardinp;
            document.getElementById('recfondlistintinp').value=recfondlistintinp;
            document.getElementById('recletlistinitinp').value=recletlistinitinp;
            document.getElementById('reclistinticonsinp').value=reclistinticonsinp;
            
            $("#colorfnd").css("background-color",colorfndinp);
            $(".icono-top").css("background-color", colorfndbtninp);
            $(".list-group").css("background-color",colorfndbtninp);
            $(".btngroup a").css("color",coloricnbtninp);
            $(".card-body").css("background-color",fndbodyvcardinp);
            $(".groupitem").css("background-color",recfondlistintinp);
            $(".list-details").css("color",recletlistinitinp);
            $(".negro").css("color", recletlistinitinp);
            $(".list-icon i").css("color",reclistinticonsinp);
            
            telefonos.map((data,i)=>{

                let tfns=data.split("#");
                document.getElementById('telempresa').value=tfns[1];
                
                switch (i){
                    case 0:
                        document.getElementById('ext').value=tfns[2];
                        document.getElementById('idem').value=tfns[0];
                    break;
                    case 1:
                        document.getElementById('idem1').value=tfns[0];
                        document.getElementById('telempresa1').value=tfns[1];
                        document.getElementById('ext1').value=tfns[2];
                        $("#extension1").show();
                    break;
                    case 2:
                        document.getElementById('idem2').value=tfns[0];
                        document.getElementById('telempresa2').value=tfns[1];
                        document.getElementById('ext2').value=tfns[2];
                        $("#extension2").show();  
                    break;
                }
            });

            if(logo_empresa_empleado!="logoletra.png"){
                document.getElementById("logocard").src=`{{asset("storage/repositorio")}}/${idempresa}/vcard/${logo_empresa_empleado}`;
            }

        }else{
            resetInputs();
        }
    });
};
const resetInputs=()=>{ //Limpiar campos cuando se da de alta una card y cuando se modifica

    $("#extension1").hide();
    $("#extension2").hide();
    document.getElementById("addinfo").reset();
};
</script>
@endpush
