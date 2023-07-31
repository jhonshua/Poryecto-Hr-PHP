<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')


<style type="text/css">
    nav a{
        color:black; }

    nav a:hover{
        color:#fbba00; }

    .btn-outline-success {
        color: #28a745; 
        border-color: white !important;}

    .article-nav {
        width: 100%;
        height: auto;
        float: left;
        box-sizing: border-box;
        background-color: #fff; }

    .nav-tabs .nav-link {
        border: 2px solid transparent;
        border-left-color: #fbba00; 
        color: gray;}
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
        color: black;
        font-weight: bold;
    }

    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link {
        display: block;
        padding: 0.5rem 2.5rem !important;
    }
    .select-clase{
        margin-top: 5px;
    }
    .select2-selection{
        text-align: center;
    }
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.disabled {
        color: gray;
        font-weight: bold;
        border-right-color: #fbba00;
    }
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
        border-right-color: #fbba00;
    }
    .input-style{
        width: 260px !important;
    }
    .select2-selection{
        text-align: center;
    }
    .group { 
        position:relative; 
        margin-bottom:30px; 
    }
    .labeldes {
        color:#A8A5A4; 
        font-size:18px;
        font-weight:normal;
        position:absolute;
        pointer-events:none;
        left:5px;
        top:10px;
        transition:0.2s ease all; 
        -moz-transition:0.2s ease all; 
        -webkit-transition:0.2s ease all;
    }
    input:focus ~ label, input:valid ~ label {
        top:-20px;
        font-size:15px;
        color:#3D3B3B;
    }
    select:focus ~ label, select:valid ~ label {
        top:-20px;
        font-size:15px;
        color:#3D3B3B;
    }
</style>

<div class="container">
@include('includes.header',['title'=>'Crear Empleado',
        'subtitle'=>'Empleados', 'img'=>'img/header/administracion/icono-usuario.png',
        'route'=>'empleados.empleados'])


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
    


    <div class="row">
        <div class="col-md-12 text-center mt-4">
            <div class="article-nav border">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link disabled" id="generles-tab" data-toggle="tab" href="#generles" role="tab"aria-controls="generles" aria-selected="true">Datos generales</a>

                        <a class="nav-item nav-link disabled" id="salario-tab" data-toggle="tab" href="#salario" role="tab" aria-controls="salario" aria-selected="false">Salario</a>
                        <a class="nav-item nav-link active" id="personales-tab" data-toggle="tab" href="#personales" role="tab"aria-controls="personales" aria-selected="false">Datos personales</a>

                        @if (Session::get('empresa')['id'] != 111) {{--JEDISAM --}}
                            <a class="nav-item nav-link disabled" id="infonavit-tab" data-toggle="tab" href="#infonavit" role="tab"aria-controls="infonavit" aria-selected="false">INFONAVIT/FONACOT</a>
                        @endif

                        {{-- <a class="nav-item nav-link disabled" id="params-tab" data-toggle="tab" href="#params" role="tab" aria-controls="params" aria-selected="false">Parámetros</a> --}}
                        
                        <a class="nav-item nav-link disabled" id="expediente-tab" data-toggle="tab" href="#expediente" role="tab"aria-controls="expediente" aria-selected="false">Expediente</a>
                        
                        {{-- @if ((Session::get('empresa.parametros')[0]['biometrico']) == '1') --}}
                        <a class="nav-item nav-link disabled" id="biometrico-tab" data-toggle="tab" href="#biometrico" role="tab"aria-controls="biometrico" aria-selected="false">Biométrico</a>
                        {{-- @endif --}}

                    </div>
                </nav>
            </div>
        </div>
    </div>

    <form action="{{route('empleados.pasotres')}} " method="post" id="submit_pasotres">
    @csrf
        <input type="hidden" name="id" value="{{$id_empleado}}">
        <div class="row">
            <div class="col-md-2 mt-3">
                <div class="article border">
                     <br>
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <img src="{{asset('/img/avatar.png')}}" alt="" class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                        </div>
                    </div>  
                   
                    <br>
                    <div class="row">
                        <div class="col-md-12 mt-2">
                            <input type="button" class="center button-style" id="add_pasotres" value="Guardar">
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
            <div class="col-md-10 mt-3">
                <div class="article border">
                    <div class="row">
                        <div class="col-md-4 mt-1">
                            <div class="group">
                                <input type="text" name="nacionalidad" id="nacionalidad" value="{{ old('nacionalidad') }}" class="form-control input-style-custom mb-2 mayusculas" required>
                                {!! $errors->first('nacionalidad','<p class="text-danger">Error: El campo nacionalidad es requerido y debe ser alfabetico.</p>') !!}
                                <label class="labeldes">Nacionalidad</label>    
                            </div>
                            <div class="group">
                               <input type="text" name="calle_numero" id="calle_numero" value="{{ old('calle_numero') }}" class="form-control input-style-custom mb-2 mayusculas"  required>
                                {!! $errors->first('calle_numero','<p class="text-danger">Error: El campo calle y num ext e int es requerido</p>') !!}
                                <label class="labeldes">Calle y Num Ext e Int</label>    
                            </div>
                            <div class="group">
                                <input type="text" name="colonia" id="colonia" value="{{ old('colonia') }}" class="form-control input-style-custom mb-2 mayusculas" required>
                                {!! $errors->first('colonia','<p class="text-danger">Error: El campo colonia es requerido</p>') !!}
                                <label class="labeldes">Colonia</label>    
                            </div>
                            <div class="group">
                                <input type="text" name="delegacion" id="delegacion" value="{{ old('delegacion') }}" class="form-control input-style-custom mb-2 mayusculas" required>
                                {!! $errors->first('delegacion','<p class="text-danger">Error: El campo alcaldía o municipio es requerido</p>') !!}
                                <label class="labeldes">Alcaldía o Municipio</label>    
                            </div>
                            <div class="group">
                                <select name="estado" id="estado" class="form-control input-style-custom mb-2 select-clase" style="text-align: center;" required>
                                    <option selected value="" disabled></option>
                                    <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                    <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA </OPTION>
                                    <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR </OPTION>
                                    <OPTION VALUE="CAMPECHE">CAMPECHE </OPTION>
                                    <OPTION VALUE="CHIAPAS">CHIAPAS </OPTION>
                                    <OPTION VALUE="CHIHUAHUA">CHIHUAHUA </OPTION>
                                    <OPTION VALUE="COAHUILA">COAHUILA </OPTION>
                                    <OPTION VALUE="COLIMA">COLIMA </OPTION>
                                    <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                    <OPTION VALUE="DURANGO">DURANGO </OPTION>
                                    <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO </OPTION>
                                    <OPTION VALUE="GUANAJUATO">GUANAJUATO </OPTION>
                                    <OPTION VALUE="GUERRERO">GUERRERO </OPTION>
                                    <OPTION VALUE="HIDALGO">HIDALGO </OPTION>
                                    <OPTION VALUE="JALISCO">JALISCO </OPTION>
                                    <OPTION VALUE="MICHOACÁN">MICHOACÁN </OPTION>
                                    <OPTION VALUE="MORELOS">MORELOS </OPTION>
                                    <OPTION VALUE="NAYARIT">NAYARIT </OPTION>
                                    <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN </OPTION>
                                    <OPTION VALUE="OAXACA">OAXACA </OPTION>
                                    <OPTION VALUE="PUEBLA">PUEBLA </OPTION>
                                    <OPTION VALUE="QUERETARO">QUERÉTARO </OPTION>
                                    <OPTION VALUE="QUINTANAROO">QUINTANA ROO </OPTION>
                                    <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ </OPTION>
                                    <OPTION VALUE="SINALOA">SINALOA </OPTION>
                                    <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                    <OPTION VALUE="TABASCO">TABASCO </OPTION>
                                    <OPTION VALUE="TAMAULIPAS">TAMAULIPAS </OPTION>
                                    <OPTION VALUE="TLAXCALA">TLAXCALA </OPTION>
                                    <OPTION VALUE="VERACRUZ">VERACRUZ </OPTION>
                                    <OPTION VALUE="YUCATÁN">YUCATÁN </OPTION>
                                    <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                </select>
                                {!! $errors->first('estado','<p class="text-danger">Error: El campo estado es requerido</p>') !!}
                                <label class="labeldes">Estado</label>    
                            </div>
                            <div class="mt-2 group">
                                <input type="number" name="cp" id="cp" value="{{ old('cp') }}" class="form-control input-style-custom mb-2" maxlength="5" required>
                                {!! $errors->first('cp','<p class="text-danger">Error: El campo código postal es requerido y debe ser  numerico.</p>') !!}                                
                                <label class="labeldes">Código Postal</label>    
                            </div>

                        </div>


                        <div class="col-md-4 mt-1 ">
                            <div class="mt-2 group">
                                <input type="email" name="correo" id="correo" value="{{ old('correo') }}" class="form-control input-style-custom mb-2" required>
                                {!! $errors->first('correo','<p class="text-danger">Error: El campo correo es requerido</p>') !!}
                                <label class="labeldes">Correo Electrónico</label>    
                            </div>
                             <div class="mt-2 group">
                                <input type="tel" name="telefono_casa" id="telefono_casa" value="{{ old('telefono_casa') }}" class="form-control input-style-custom mb-2" required>
                                {!! $errors->first('telefono_casa','<p class="text-danger">Error: El campo telefono casa es requerido y debe ser numerico.</p>') !!}
                                <label class="labeldes">Teléfono Casa</label>    
                            </div>
                            <div class="mt-2 group">
                                <input type="tel" name="telefono_movil" id="telefono_movil" value="{{ old('telefono_movil') }}" class="form-control input-style-custom mb-2" required>
                                {!! $errors->first('telefono_movil','<p class="text-danger">Error: El campo telefono movil es requerido y debe ser numerico</p>') !!}
                                <label class="labeldes">Teléfono Movil</label>    
                            </div>
                            <div class="mt-2 group">
                                <select name="estado_civil" id="estado_civil" class="form-control input-style-custom mb-2 select-clase" required>
                                    <option selected value="" disabled></option>
                                    <OPTION VALUE="SOLTERO(A)">SOLTERO(A)</OPTION>
                                    <OPTION VALUE="CASADO(A)">CASADO(A) </OPTION>
                                    <OPTION VALUE="UNION LIBRE">UNION LIBRE</OPTION>
                                    <OPTION VALUE="DIVORCIADO(A)">DIVORCIADO(A) </OPTION>
                                </select>
                                {!! $errors->first('estado_civil','<p class="text-danger">Error: El campo estado civil es requerido</p>') !!}
                                <label class="labeldes">Estado Civil</label>    
                            </div>
                            <div class="mt-2 group">
                                <input type="text" required name="escolaridad" id="escolaridad" value="{{ old('escolaridad') }}" class="form-control input-style-custom mb-2">
                                <label class="labeldes">Escolaridad</label>    
                            </div>
                            <div class="mt-2 group">
                                <input type="text" name="profesion" required id="profesion" value="{{ old('profesion') }}" class="form-control input-style-custom mb-2 mayusculas">
                                <label class="labeldes">Profesión</label>    
                            </div>
                        </div>


                        <div class="col-md-4 mt-1">
                            <div class="mt-2 group">
                                <input type="text" name="avisar_a" id="avisar_a" value="{{ old('avisar_a') }}" required class="form-control input-style-custom mb-2">
                                <label class="labeldes">En caso de Accidente Avisar a</label>    
                            </div>
                            <div class="mt-2 group">
                                <input type="text" name="avisar_a_telefono" id="avisar_a_telefono" value="{{ old('avisar_a_telefono') }}" required class="form-control input-style-custom mb-2">
                                <label class="labeldes">Telefono</label>    
                            </div>
                            <div class="mt-2 group">
                                <input type="text" required name="beneficiario" id="beneficiario" value="{{ old('beneficiario') }}" class="form-control input-style-custom mb-2 mayusculas">
                                <label class="labeldes">Beneficiario</label>    
                            </div>
                            <div class="mt-2 group">
                            <select name="avisar_a_parentesco" required id="avisar_a_parentesco" class="form-control input-style-custom mb-2 select-clase">
                                <option value="" disabled selected></option>
                                <option value="HERMANO(A)">HERMANO(A)</option>
                                <option value="ESPOSO(A)">ESPOSO(A)</option>
                                <option value="HIJO(A)">HIJO(A)</option>
                                <option value="PADRES">PADRES</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                            <label class="labeldes">Parentesco</label>    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>


</div>
@include('includes.footer')

<script type="text/javascript">
    $(".mayusculas").keyup(function () {  
        $(this).val($(this).val().toUpperCase());  
    }); 

    $("#add_pasotres").click(function(){
        var nacionalidad = document.getElementById("nacionalidad").value;
        if(nacionalidad== ""){
            swal({
              title: "Para continuar debes agregar la información requerida",
            });
        }else{
          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          }); 
          setTimeout(submitForm, 1500);     
        }
    });
    function submitForm() { document.getElementById("submit_pasotres").submit() }

    $(function() {
        $('.select-clase').select2();
    });

</script>