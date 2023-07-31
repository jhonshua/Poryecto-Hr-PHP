<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<style>
    label{
        font-weight: bold;
        margin-top: 15px;
    }

    .bg-gray{
        background-color: #eee;
    }

    .file {
        box-shadow: 0px 0px 0px 2px #dadada;
        cursor: pointer;
        width: 46%; }

    .file label {
        cursor: pointer; }

    .file input[type=file] {
        width: 1px; }

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
                            <a class="nav-item nav-link disabled" id="infonavit-tab" data-toggle="tab" href="#infonavit" role="tab"aria-controls="infonavit" aria-selected="false">INFONAVIT/FONACOT</a>
                        @endif

                        {{-- <a class="nav-item nav-link disabled" id="params-tab" data-toggle="tab" href="#params" role="tab" aria-controls="params" aria-selected="false">Parámetros</a> --}}
                        
                        <a class="nav-item nav-link active" id="expediente-tab" data-toggle="tab" href="#expediente" role="tab"aria-controls="expediente" aria-selected="false">Expediente</a>
                        
                        {{-- @if ((Session::get('empresa.parametros')[0]['biometrico']) == '1') --}}
                        <a class="nav-item nav-link disabled" id="biometrico-tab" data-toggle="tab" href="#biometrico" role="tab"aria-controls="biometrico" aria-selected="false">Biométrico</a>
                        {{-- @endif --}}

                    </div>
                </nav>
            </div>
        </div>
    </div>

    <form action="{{ route('empleados.pasocinco') }}" method="post" id="empleado_datos" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$id_empleado}}">
        <div class="row">
            <div class="col-md-2 mt-3">
                <div class="article border">
                    <div class="row">
                        <div class="col-md-12 text-center mt-2">
                            <img src="{{asset('/img/avatar.png')}}" alt="" class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="center button-style guardar">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10 mt-3">
                <div class="article border">
                <div class="row">
                    <div class="col-md-1"></div>
                        <div class="col-md-10 d-flex flex-wrap files text-center">
                            @foreach ($archivos as $key => $archivo)

                                <div class="file rounded p-2 mr-3 mb-3" for="{{$key}}">
                                    <label class="name tooltip_ d-flex  align-items-center" data-toggle="tooltip" title="Subir archivo" for="{{$key}}">
                                        <img src="{{asset('/img/icono-contrato-receptora.png')}}" alt="Periodo de implementación" style="width: 30px;">  
                                        <label for="{{$key}}" id="{{$key}}_text" style="padding-left: 10px;"> {{$archivo}}</label>
                                        <input type="file" name="{{$key}}" id="{{$key}}" onchange="file('{{$key}}')" class="invisible" accept=".pdf, .png, .jpg, .doc, .docx">

                                    </label> 
                                </div>
                            @endforeach
                        </div>
                </div>
                </div>
            </div>
        </div>
    </form>

</div>
@include('includes.footer')
<script>
$(function(){

    // Validar
    $( "#empleado_datos" ).validate({
        ignore: [],
        submitHandler: function(form) {

          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          }); 
            
            form.submit();
            $('.btn.guardar').text('Espere...');
            $('.btn').attr('disabled', true);
            $('.btn.regresar').attr('src', '#');
        },
    });

});
</script>

<script type="text/javascript">
    function file(val){

        var text = val+"_text";
        document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;
    }
</script>