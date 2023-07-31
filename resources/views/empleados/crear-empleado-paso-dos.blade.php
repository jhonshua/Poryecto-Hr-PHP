<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<style type="text/css">
    input[type="date"]::-webkit-calendar-picker-indicator{
        filter: invert(74%) sepia(11%) saturate(6958%) hue-rotate(2deg) brightness(104%) contrast(104%);
    }

	
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
    #fecha_antiguedad:not(.has-value):before{
      color: lightgray;
      content: attr(placeholder);
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


    <div class="row">
        <div class="col-md-12 text-center mt-4">
            <div class="article-nav border">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link disabled" id="generles-tab" data-toggle="tab" href="#generles" role="tab"aria-controls="generles" aria-selected="true">Datos generales</a>

                        <a class="nav-item nav-link active" id="salario-tab" data-toggle="tab" href="#salario" role="tab" aria-controls="salario" aria-selected="false">Salario</a>
                        <a class="nav-item nav-link disabled" id="personales-tab" data-toggle="tab" href="#personales" role="tab"aria-controls="personales" aria-selected="false">Datos personales</a>

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

	<form action="{{ route('empleados.pasodos') }}" method="post" id="submit_pasodos">
	@csrf
	    <input type="hidden" name="id" value="{{$id_empleado}}">
	    <input type="hidden" name="forma_de_pago" value="Transferencia">
	    <div class="row">
	    	<div class="col-md-2 mt-3">
                    <div class="article border">
                        <div class="row">
                            <div class="col-md-12 mt-3">
                                <img src="{{asset('/img/avatar.png')}}" alt="" class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="button" class="center button-style" id="add_pasodos" value="Guardar">
                            </div>
                        </div>
                        <br>
                        <br>
                    </div>
	    	</div>
	    	<div class="col-md-10 mt-3">
	    		<div class="article border">
	    			<div class="row">
                            <div class="col-md-4 mt-1">
                                <div class="group">
                                    <select name="tipo_de_nomina" id="tipo_de_nomina" class="form-control input-style-custom mb-2" required>
                                        <option value="" disabled selected></option>
                                        @foreach ($tipos_nomina as $tn)
                                            <option value="{{ strtoupper($tn)}}" {{(strtoupper($tn) == strtoupper(old('tipo_de_nomina'))) ? 'selected' : ''}}>{{ strtoupper($tn)}}</option>
                                        @endforeach
                                    </select>
                                    <label class="labeldes">Tipo de Nomina</label>    
                                </div>
                                <div class="group">
                                    @foreach ($parametros as $parametro)
                                        <input type="number" name="salario_diario" id="salario_diario" value="{{ old('salario_diario') }}" class="form-control input-style-custom mb-2" required min="{{$parametro->salario_minimo}}" max="{{$parametro->salario_maximo}}">
                                    @endforeach
                                    {!! $errors->first('salario_diario','<p class="text-danger">Error: El campo salario diario es requerido y debe ser del tipo numerico.</p>') !!}
                                    <label class="labeldes">Salario Diario</label>    
                                </div>
                               
                                    {{-- <label class="mb-0">Salario Diario Integrado:</label>
                                    <input type="number" name="salario_diario_integrado" id="salario_diario_integrado" value="{{old('salario_diario_integrado')}}" class="form-control mb-2" required> --}}
                                
                                <div class="group">
                                    <input type="number" name="sueldo_neto" id="sueldo_neto" value="{{old('sueldo_neto')}}" class="form-control input-style-custom mb-2" required>
                                    {!! $errors->first('sueldo_neto','<p class="text-danger">Error: El campo sueldo neto del periodo es requerido y debe ser del tipo numerico.</p>') !!}
                                    <label class="labeldes">Sueldo Neto del Periodo</label>    
                                </div>
                                <div class="group">
                                    <input type="number" name="salario_digital" id="salario_digital" value="{{old('salario_digital')}}" class="form-control input-style-custom mb-2" required>
                                    {!! $errors->first('salario_digital','<p class="text-danger">Error: El campo sueldo diario real es requerido y debe ser del tipo numerico.</p>') !!}
                                    <label class="labeldes">Sueldo Diario Real</label>    
                                </div>
                            </div>


	                        <div class="col-md-4 mt-1">
	                            {{-- <label class="mb-0">Fecha de Antiguedad:</label> --}}
	                            <input type="date" name="fecha_antiguedad" id="fecha_antiguedad" placeholder="Fecha Antiguedad" value="{{old('fecha_antiguedad', date('Y-m-d'))}}" class="form-control input-style-custom mb-2" required>
	                        </div>
	                        {!! $errors->first('fecha_antiguedad','<p class="text-danger">Error: El campo fecha de antiguedad es requerido</p>') !!}
	                        <div class="col-md-4 mt-1">
                                <div class="group">
                                    <select name="id_banco" id="id_banco" class="form-control input-style-custom select-clase mb-2" required>
                                        <option disabled selected></option>
                                        @foreach ($bancos as $banco)
                                        <option value="{{ $banco->id}}">
                                            {{ strtoupper($banco->nombre)}}</option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('id_banco','<p class="text-danger">Error: El campo banco es requerido</p>') !!}
                                    <label class="labeldes">Banco</label>    
                                </div>
                            
	                            <div class="mt-1 group">
                            		<input type="number" required name="cuenta_bancaria" id="cuenta_bancaria" value="{{ old('cuenta_bancaria') }}" class="form-control input-style-custom mb-2" >
                                    <label class="labeldes">Cuenta Banco</label>    
                                </div>
                                <div class="group">
                            	    <select name="tipo_cuenta" required id="tipo_cuenta" class="form-control input-style-custom mb-2 select-clase">
                                        <option value="" disabled selected></option>
                                        <option value="01">CHEQUES</option>
                                        <option value="03" >TARJETA DEDÉBITO</option>
                                        <option value="40">CLABE</option>
                                    </select>
                                    <label class="labeldes">Tipo Cuenta</label>    
                                </div>
	                            <div class="mt-2 group">
                            		<input type="number" name="clabe_interbancaria" id="clabe_interbancaria" required value="{{ old('clabe_interbancaria') }}" class="form-control input-style-custom mb-2" maxlength="20">
                                    <label class="labeldes">Clabe Interbancaria</label>
                                </div>
                                <div class="mt-2 group">
                                    <input type="number" name="num_tarjeta" id="num_tarjeta" required value="{{old('num_tarjeta')}}" class="form-control input-style-custom mb-2">
                                    <label class="labeldes">No. Tarjeta</label>
                                </div>
                            </div>
	    			</div>
	    			<div class="row">
	    				<div class="mt-2"></div>
	    			</div>
	    		</div>
	    	</div>
	    </div>



</div>
@include('includes.footer')

<script type="text/javascript">

    $("#add_pasodos").click(function(){
        var salario_diario = document.getElementById("salario_diario").value;
        if(salario_diario== ""){
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
    function submitForm() { document.getElementById("submit_pasodos").submit() }

    $(function() {
        $('.select-clase').select2();
    });

</script>