<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
            <a href="{{ route('configuracion.formularios.inicio') }}" data-toggle="tooltip" title="Regresar"  ref="Agregar iconos formularios">
                @include('includes.back')
            </a>
            <label class="font-size-1-5em mb-5 under-line font-weight-bold">Configuración formularios / Modificar iconos</label>
            @if(session()->has('success'))
                <div class="row">
                    <div class="alert alert-success" style="width: 100%;" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>Notificación: </strong>
                        {{ session()->get('success') }}
                    </div>
                </div>
            @endif
            <div class="article border">
                @php 
                    $url = asset('storage/img-default/default.png');
                    $numeroItems =$consultas->count() + 1 ;   
                @endphp
                <form action="{{route('configuracion.formularios.agregarEditar')}}" method="POST" id="addform" enctype="multipart/form-data" >
                    @csrf
                    <input type="text" name="titulo" id="titulo" class="form-control input-style-custom" value="{{$titulo}}" placeholder="Introduce un título para los iconos" required >
                    <br>
                    <table class="table w-100" id="tbl" >
                        <thead>
                            <tr>
                                <th>
                                    <div class="btn bg-color-yellow btn-sm" id="agregar-iconos" data-toogle="tooltip" title="Agregar iconos "  data-placement="left" > <li class="fas fa-plus" ></li></div>
                                    <div class="btn bg-color-yellow btn-sm" id="refresh" data-toogle="tooltip" title="Cancelar proceso"  data-placement="right" > <li class="fas fa-sync" ></li></div>
                                </th>
                                <th scope="col" class="gone text-center">Numero icono</th>
                                <th scope="col" class="gone text-center">Seleccionar icono</th>
                                <th scope="col" class="gone text-center">Valor del icono</th>
                                <th scope="col" class="text-center">Icono</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($consultas as $key=> $consulta)
                                @php $contador = $key+1 @endphp
                                <tr id="rowsicons{{$key}}" >
                                    <td class="d-none" ><input type='hidden' name='idicon[]' value="{{ Crypt::encrypt($consulta->id) }}"></td>
                                    <td>@if( $contador >=4 ) <div type="button" class="btn bg-danger btn-sm" onclick="eliminar({{$key}},'{{Crypt::encrypt($consulta->id)}}' );" data-toogle="tooltip" title="Eliminar icono" data-placement="right"  ><span><i class="fas fa-trash-alt text-light"></i></span></div>@endif</td>
                                    <td class="text-center">{{$contador}}</td>
                                    <td>
                                        <div class="custom-file ">
                                            <input type="file" name="file[]" class="custom-file-input" id="file{{$contador}}" onclick="cambiarIcono({{$contador}});">
                                            <label class="custom-file-label" >Seleccionar Archivo</label>
                                        </div> 
                                    </td>
                                    <td class="text-center">
                                        <input type="text" name="valor[]" id="valor" value="{{$consulta->valor}}" class="input-style-custom" onkeypress="return soloNumeros(event)" required/>
                                    </td>
                                    <td class="text-center">
                                        <img src="{{asset("storage/configuracion-formularios/svg/$consulta->icono")}}" width="45" height="45"   id="imgicon{{$contador}}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    <input type="hidden" name="id" value="{{ Crypt::encrypt($consulta->idconfigform)}}">
                    <div  class="center button-style  w-10 " id="guardar" >Guardar</div>
                </form>
            </div>
        </div>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
        <script> let urlImg ='@php echo $url  @endphp'; </script>
        <script src="{{asset('js/helper.js')}}"></script>
        <script>
            
            let count_params_icons = '@php echo $numeroItems @endphp' ;
            $(document).ready(function() {
                
                $("#agregar-iconos").on('click',function(){  
     
                    if(count_params_icons < 6 ){
                        let table= `<tr class="rows_items_icons" id="rowsicons${count_params_icons}">
                                        <td><div type="button" class="btn bg-danger btn-sm" onclick="eliminar(${count_params_icons});" data-toogle="tooltip" title="Eliminar icono" data-placement="right"  ><span><i class="fas fa-trash-alt text-light"></i></span></div></td>
                                        <td class="d-none" ><input type='hidden' name='idicon[]' value=""></td>
                                        <td><input type="text" class="custom-inp nicon center" value="${count_params_icons}" readonly></td>
                                        <td>
                                            <div class="custom-file ">
                                                <input type="file" name="file[]" class="custom-file-input" id="file${count_params_icons}" onclick="cambiarIcono(${count_params_icons});" required>
                                                <label class="custom-file-label" >Seleccionar Archivo</label>
                                            </div>         
                                        </td>
                                        <td class="text-center" >
                                            <input type="text" name="valor[]" id="valor" class="input-style-custom" onkeypress="return soloNumeros(event)" required/>
                                        </td>
                                        <td class="text-center" ><img src="{{$url}}" width="45" height="45" id="imgicon${count_params_icons}" ></td>
                                    </tr>`;
                        count_params_icons++;
                        $("#tbl tbody " ).append(table);
                    }    
                });
                
                $("#guardar").on('click',function(e){

                    e.preventDefault();
                    let form = $("#addform");
                    (form.parsley().isValid()) ?  $('#addform').submit() : form.parsley().validate();
                
                });

                $('#refresh').click(function() {
                    location.reload();
                });
            });

            const eliminar = async (index ,id) =>{
   
                if(id != undefined ){
                    swal({
                            title: "¿Estás seguro de eliminar este registro ?",
                            text: "Al eliminarlo no podrás recuperar los cambios !",
                            icon: "warning",
                            buttons:  ["Cancelar", true],
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) {
                                
                                eliminarDatos(id).then(data=>{
                                    swal("El dato se eliminó correctamente!", {
                                        icon: "success",
                                    });
                                });
                                eliminarIcono(index);
                                location.reload();
                            }
                        });
                }else{
                    
                    eliminarIcono(index);
                }
            } 
            const eliminarDatos= async id =>{
                
                const  url ="{{route('configuracion.formularios.eliminarItem')}}";
                const  response =await fetch(`${url}/?id=${id}`);
                let data= await response.json();
                return data;
           
            }
        </script>
    </body>
</html>
