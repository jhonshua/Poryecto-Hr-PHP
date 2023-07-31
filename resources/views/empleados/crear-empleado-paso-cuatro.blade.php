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
        left:30%;
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


    <div class="row">
        <div class="col-md-12 text-center mt-4">
            <div class="article-nav border">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link disabled" id="generles-tab" data-toggle="tab" href="#generles" role="tab"aria-controls="generles" aria-selected="true">Datos generales</a>

                        <a class="nav-item nav-link disabled" id="salario-tab" data-toggle="tab" href="#salario" role="tab" aria-controls="salario" aria-selected="false">Salario</a>
                        <a class="nav-item nav-link disabled" id="personales-tab" data-toggle="tab" href="#personales" role="tab"aria-controls="personales" aria-selected="false">Datos personales</a>

                        @if (Session::get('empresa')['id'] != 111) {{--JEDISAM --}}
                            <a class="nav-item nav-link active" id="infonavit-tab" data-toggle="tab" href="#infonavit" role="tab"aria-controls="infonavit" aria-selected="false">INFONAVIT/FONACOT</a>
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


    <form action="{{ route('empleados.pasocuatro') }}" method="post" id="submit_pasocuatro">
        @csrf
        <input type="hidden" name="id" value="{{$id_empleado}}">

        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-2 mt-3">
                <div class="article border">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <img src="{{asset('/img/avatar.png')}}" alt="" class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                        </div>
                    </div>  
                    <div class="row">
                        <div class="col-md-12 text-center mt-2">
                            <input type="button" class="center button-style" id="add_pasocuatro" value="Guardar">
                        </div>
                    </div> 
                </div>
            </div>
            <div class="col-md-8 mt-3">
                <div class="article border">
                    <br>
                        <div class="col-md-12 text-center mt-3">
                            <div class="group">
                                <select name="" id="infonavit" required class="center input-style mb-2 select-clase" onchange="infonavitdiv()">
                                    <option selected disabled>¿Cuenta con crédito INFONAVIT?</option>
                                    <option value="1">SI </option>
                                    <option value="0">NO</option>
                                </select>
                                {!! $errors->first('infonavit','<p class="text-danger">Error: El campo infonavit es requerido</p>') !!}
                            </div>
                            <div id="infonavit_div" style="display: none;" class="mt-2">
                                <div class="group">
                                    <input required type="text" name="num_credito_infonavit" id="num_credito_infonavit" class="center input-style mb-2" value="{{ old('num_credito_infonavit') }}">
                                    <label class="labeldes">No. Crédito INFONAVIT</label>    
                                </div>
                                <div class="group">
                                    <select required name="tipo_descuento" id="tipo_descuento" class="center input-style mb-2 select-clase">
                                        <option value=""></option>
                                        <option value="POR PORCENTAJE">POR PORCENTAJE</option>
                                        <option value="VECES EN SALARIO">VECES EN SALARIO</option>
                                        <option value="CUOTA FIJA">CUOTA FIJA</option>
                                    </select>
                                    <label class="labeldes">Tipo de descuento</label>    
                                </div>
                                <div class="mt-2 group">
                                    <input required type="text" name="valor_descuento" id="valor_descuento" class="center input-style mb-2" value="{{ old('valor_descuento') }}">
                                    <label class="labeldes">Valor</label>    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-center mt-2">
                            <div class="group">
                                <select required name="" id="fonacot" class="center input-style mb-2 select-clase" onchange="fonacotdiv()">
                                    <option value="" disabled selected></option>
                                    <option value="1">SI</option>
                                    <option value="0">NO</option>
                                </select>
                                <label class="labeldes">¿Cuenta con crédito FONACOT?</label>    
                            </div>
                            <div id="fonacot_div" style="display: none;" class="mt-2">
                            <div class="group">
                                <input required type="text" name="num_credito_fonacot" id="num_credito_fonacot" class="center input-style mb-2" value="{{ old('num_credito_fonacot') }}">
                                <label class="labeldes">Num.Crédito FONACOT</label>    
                            </div>
                            <div class="group">
                                <input required type="text" name="valor_fonacot" id="valor_fonacot" class="center input-style mb-2" value="{{ old('valor_fonacot') }}">
                                <label class="labeldes">Valor</label>    
                            </div>
                        </div>
                        </div>
                        <br>
                        <br>
                        <br>
                    </div>  
                </div>

            </div>
            <div class="col-md-1"></div>
        </div>


    </form>

</div>

@include('includes.footer')
<script type="text/javascript">
    function infonavitdiv() {
        
        var val = document.getElementById("infonavit").value;
        
        if(val == 1){ $("#infonavit_div").show(); }else{ $("#infonavit_div").hide(); }
    }

    function fonacotdiv() {
        
        var val = document.getElementById("fonacot").value;
        
        if(val == 1){ $("#fonacot_div").show(); }else{ $("#fonacot_div").hide(); }
    }



    $("#add_pasocuatro").click(function(){
        var infonavit = document.getElementById("infonavit").value;
        var fonacot = document.getElementById("fonacot").value;

        if(infonavit== "" || fonacot ==""){
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
    function submitForm() { document.getElementById("submit_pasocuatro").submit() }
    $(function() {
        $('.select-clase').select2();
    });
</script>