<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">
    
@include('includes.header',['title'=>'Modificar encuesta',
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
                            <label class="mb-0">Fecha de vencimiento:  <input type="checkbox" id="checkfecha" onclick="checkFecha()" ></label><input type="hidden" name="valchecked" id="valchecked" value="0" >
                        </div>
                        <div class="form-group mb-4 d-none" id="div_fecha">
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="{{old('fecha_vencimiento', date('Y-m-d'))}}" class="form-control input-style-custom">
                        </div>
                        <div class="form-group mb-4">
                            <input type="text" class="form-control input-style-custom" name="titulo" value="{{$datos['datos_generales']['titulo']}}" placeholder="Ingrese un titulo para el formulario" required>
                        </div>
                        <div class="form-group mb-4">
                            <textarea cols="30" rows="5" name="descripcion"  placeholder="Ingrese una descripción para su formulario" class="form-control input-style-custom" required >{{$datos['datos_generales']['descripcion']}}</textarea>
                        </div>
                        <input type="hidden" class="form-control" name="estatus" value="1" id="estatus" >
                        <input type="hidden" class="form-control" name="id_encuesta" id="id_encuesta" value="{{Crypt::encrypt($datos['datos_generales']['id'])}}" >
                        <!--<div class="font-size-1-5em  center under-line"></div>-->
                        <div id="contentItems">

                            @foreach ($datos['preguntas'] as $key=>$pregunta)
                            @php 
                                $datos_preguntas = explode("*#--#", $pregunta);
                                $nrandom="";
                                $nrandom1="";
                                if($datos_preguntas[3]!=="1" ){
                                 
                                    $nrandom=Str::random(99);
                                    $nrandom1=rand(0,99);
                                }
                                
                                $id = Crypt::encrypt($datos['datos_generales']['id']);
                                $id_pregunta =$datos_preguntas[0]; 
                                $pregunta = $datos_preguntas[1];
                                $valor = $datos_preguntas[2];
                                $tipo_pregunta = $datos_preguntas[3];
                                $icono = $datos_preguntas[4];

                            @endphp
                             <div class="content items div_componente" id="item${count}" >
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Pregunta</label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <input type="text" class="form-control input-style-custom" name="pregunta[]" value='{{$pregunta}}' placeholder='Pregunta'required >
                                            <div class="input-group-prepend">
                                                <div type="button" 
                                                    class="btn float-center eliminar-item" 
                                                    data-toggle="tooltip" 
                                                    data-placement="right" 
                                                    title="Eliminar" 
                                                    data-id="{{$id}}"
                                                    data-id-pregunta ="{{Crypt::encrypt($id_pregunta)}}"
                                                    data-pregunta ="{{$pregunta}}"
                                                ><img src="{{asset('/img/eliminar.png')}}" class="button-style-icon"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row d-none">
                                    <label class="col-sm-2 col-form-label">id</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control input-style-custom" name="id_preg[]"  value="{{ Crypt::encrypt($id_pregunta) }}" >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Valor pregunta</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control input-style-custom input-number" name="valor_preg[]" placeholder='valor de la pregunta'     value='{{$valor}}' required >
                                    </div>
                                </div>
                                <div class="form-group row d-none">
                                    <label class="col-sm-2 col-form-label">Tipo pregunta</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control input-style-custom" name="tipo_pregunta[]" value='{{$tipo_pregunta}}'>
                                    </div>
                                </div>
                                <div class="form-group row d-none">
                                    <label class="col-sm-2 col-form-label">Numero_aleatorio</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control input-style-custom" name="naleatorio1[]" value='{{$nrandom}}'>
                                    </div>
                                </div>
                                <div class="form-group row d-none">
                                    <label class="col-sm-2 col-form-label">Numero_aleatorio 2</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control input-style-custom" name="naleatorio2[]" value='{{$nrandom1}}'>
                                    </div>
                                </div>
                                <div class="form-group row d-none">
                                    <label class="col-sm-2 col-form-label">Lleva incono</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control input-style-custom"  name="icono[]" value="{{$icono}}">
                                    </div>
                                </div>
                                @if($tipo_pregunta==="2")
                                    <div>
                                        <table class="table col-md-6">
                                            @foreach ($datos['datos_opc_preguntas'][$id_pregunta] as $opc_preguntas)
                                                @php  
                                                    $datos_opc_preg = explode("*#--#", $opc_preguntas);
                                                    $id_opc_preg = $datos_opc_preg[0];
                                                    $titulo_opc_preg = $datos_opc_preg[1];
                                                    $valor_opc_preg = $datos_opc_preg[2];
                                                    $id_icono =$datos_opc_preg[3];

                                                @endphp
                                                <tr>
                                                    <td><input type="hidden" class="form-control input-style-custom" name='id_items_{{$nrandom}}_2_{{$nrandom1}}[]' value='{{Crypt::encrypt($id_opc_preg)}}' required></td>
                                                    <td><input type="text" class="form-control input-style-custom" name='titulos_items_{{$nrandom}}_2_{{$nrandom1}}[]' value='{{$titulo_opc_preg}}' required></td>
                                                    <td><input type="text" class="form-control input-style-custom  input-number" name='valores_items_{{$nrandom}}_2_{{$nrandom1}}[]' value='{{$valor_opc_preg}}' onclick="soloNumeros()" required></td>
                                                    <td><input type="hidden" class="form-control input-style-custom" name='idpregunta_items_{{$nrandom}}_2_{{$nrandom1}}[]' value='{{Crypt::encrypt($id_icono)}}' required></td>
                                                    <td><input type="radio" disabled></td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                @elseif($tipo_pregunta==="3") 
                                    <div>
                                        <table class="table col-md-12">
                                            <tr>
                                                @foreach ($datos['datos_opc_preguntas'][$id_pregunta] as $opc_preguntas)
                                                    @php 

                                                        $datos_opc_preg = explode("*#--#", $opc_preguntas);
                                                        $id_opc_preg = $datos_opc_preg[0];
                                                        $titulo_opc_preg = $datos_opc_preg[1];
                                                        $valor_opc_preg = $datos_opc_preg[2];
                                                        $id_icono =$datos_opc_preg[3];
                                                        $id_pregunta_item = $datos_opc_preg[4];
                                                    
                                                    @endphp
                                                    <td>              
                                                        <div class="form-row">                   
                                                            @if($id_icono=="1")
                                                                <div class="form-group col-md-9">
                                                                    <input type="hidden" class="form-control input-style-custom" name='id_items_{{$nrandom}}_3_{{$nrandom1}}[]' value='{{Crypt::encrypt($id_opc_preg)}}' required>
                                                                    <input type="text" class="form-control input-style-custom" name='titulos_items_{{$nrandom}}_3_{{$nrandom1}}[]' placeholder="nombre" value='{{$titulo_opc_preg}}' required >
                                                                </div>
                                                                @foreach ($datos['det_iconos'][$id_opc_preg] as $icono)
                                                                    @php  $i = explode("*#--#", $icono); @endphp
                                                                    <div class="form-group col-md-3">
                                                                        <img src="{{asset("storage/configuracion-formularios/svg")}}/{{$i[1]}}" class="custom-icon">
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <div class="form-group col-md-12">
                                                                    <input type="hidden" class="form-control input-style-custom" name='id_items_{{$nrandom}}_3_{{$nrandom1}}[]' value='{{Crypt::encrypt($id_opc_preg)}}' required>
                                                                    <input type="text" class="form-control input-style-custom" name='titulos_items_{{$nrandom}}_3_{{$nrandom1}}[]' placeholder="nombre" value='{{$titulo_opc_preg}}' required >
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <input type="text" class="form-control input-style-custom  input-number" placeholder="valor" onclick="soloNumeros()"  name='valores_items_{{$nrandom}}_3_{{$nrandom1}}[]' value='{{$valor_opc_preg}}' required >
                                                        <input type="hidden" class="form-control input-style-custom" name='icons_{{$nrandom}}_3_{{$nrandom1}}[]' value='{{$id_icono}}'>
                                                        <input type="hidden" class="form-control input-style-custom" name='idpregunta_items_{{$nrandom}}_2_{{$nrandom1}}[]' value='{{Crypt::encrypt($id_pregunta_item)}}' required>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </table>
                                    </div>
                                @elseif($tipo_pregunta==="4") 
                                    <div>
                                        <table class="table col-md-6">
                                            @foreach ($datos['datos_opc_preguntas'][$id_pregunta] as $opc_preguntas)
                                                @php  
                                                    $datos_opc_preg = explode("*#--#", $opc_preguntas);
                                                    $id_opc_preg = $datos_opc_preg[0];
                                                    $titulo_opc_preg = $datos_opc_preg[1];
                                                    $valor_opc_preg = $datos_opc_preg[2];
                                                    $id_icono =$datos_opc_preg[3];

                                                @endphp
                                                <tr>
                                                    <td><input type="hidden" class="form-control input-style-custom" name='id_items_{{$nrandom}}_4_{{$nrandom1}}[]' value='{{Crypt::encrypt($id_opc_preg)}}' required></td>
                                                    <td><input type="text" class="form-control input-style-custom" name='titulos_items_{{$nrandom}}_4_{{$nrandom1}}[]' value='{{$titulo_opc_preg}}' required></td>
                                                    <td><input type="text" class="form-control input-style-custom  input-number" name='valores_items_{{$nrandom}}_4_{{$nrandom1}}[]' value='{{$valor_opc_preg}}' onclick="soloNumeros()" required></td>
                                                    <td><input type="hidden" class="form-control input-style-custom" name='idpregunta_items_{{$nrandom}}_4_{{$nrandom1}}[]' value='{{Crypt::encrypt($id_icono)}}' required></td>
                                                    <td>
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="customCheck1">
                                                            <label class="custom-control-label" for="customCheck1" disabled ></label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                @endif
                            <hr class='customhr'>
                        </div>
                        @endforeach
                        </div>
                        <br>
                        <div  class="center button-style w-10  guardar-encuesta " id="guardar" >Guardar</div>
                    </form>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </div>
    @include('formularios.encuesta.modals.agregar-modal')
    <script> let fecha_vencimiento= '@php echo $datos["datos_generales"]["fecha_vencimiento"] @endphp'; </script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
    <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.10.2/Sortable.min.js"></script>
    <script>

        'use strict'

        let contPreguntas=0;
        let contColRadios=0;
        let countParamsRadios=3;
        let tipo_opc_select="";
        

        const url ='{{asset("storage/configuracion-formularios/svg/")}}/';  // Url general de los iconos
        const eliminarPreg="{{route('formularios.eliminarPregunta')}}";

        $(document).ready(function() {
            if(fecha_vencimiento!='No aplica' ){
        
                $("#valchecked").val(1);
                $("fecha_vencimiento").val(fecha_vencimiento);
                $("#div_fecha").removeClass('d-none');
                $("#checkfecha").prop('checked', true);

            }else{

                $("#valchecked").val(0);
                $("#div_fecha").addClass('d-none');
                $("#checkfecha").prop('checked', false);
                
            }

            $(".eliminar-item").on('click',function(){

                let id_encuesta = $(this).data('id');
                let id_pregunta = $(this).data('id-pregunta');
                let pregunta = $(this).data('pregunta');
            
                    swal({
                            title: `¿Estás seguro de eliminar este registro con el nombre ${pregunta}  ?`,
                            text: "Al eliminarlo no podrás recuperar los cambios !",
                            icon: "warning",
                            buttons:  ["Cancelar", true],
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) {
                                eliminarDatos(id_encuesta,id_pregunta).then(data=>{
                                    const { respuesta,estado} = data;
                                    if(respuesta){    
                                        let ruta="";
                                        (estado) ? ruta= "{{route('formularios.inicio')}}"  : ruta = "{{route('formularios.obtenerDatosPorEncuesta',['id',Crypt::encrypt($datos['datos_generales']['id'])])}}";
                                            
                                            swal("Datos actualizados  correctamente!", {
                                                icon: "success",
                                        });
                                        window.location.href = ruta.replace('&amp;', '=');
                                    }else{

                                        swal("Error al eliminar los datos comunicate con tu adminstrador!", {
                                            icon: "error",
                                        });
                                    }
                                }); 
                            }
                        });
            });

            $('.input-number').on('input', function () { 
                this.value = this.value.replace(/[^0-9]/g,'');
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

        const  eliminarDatos = async(id_encuesta,id_pregunta)=>{
            const resp = await fetch(`${eliminarPreg}/?id=${id_pregunta}&idencuesta=${id_encuesta}`);
            const data = await resp.json();
            return data;
        };
    </script>
</div>
@include('includes.footer')
</body>
</html>