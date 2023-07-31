<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
            <a href="{{ route('configuracion.formularios.inicio') }}" data-toggle="tooltip" title="Regresar" ref="Agregar iconos formularios">
                @include('includes.back')
            </a>
            <label class="font-size-1-5em mb-5 under-line font-weight-bold">Configuración formularios / Asignar iconos</label>
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
                @php $url = asset('storage/img-default/default.png'); @endphp
                <form action="{{route('configuracion.formularios.agregarEditar')}}" method="POST" id="addform" enctype="multipart/form-data" >
                    @csrf
                    <input type="text" name="titulo" id="titulo" class="form-control input-style-custom" placeholder="Introduce un título para los iconos" required >
                    <br>
                    <table class="table w-100" id="tbl" >
                        <thead>
                            <tr>
                                <th> <div class="btn bg-color-yellow btn-sm" id="agregar-iconos" > <li class="fas fa-plus" ></li> </div></th>
                                <th scope="col" class="gone text-center">Numero icono</th>
                                <th scope="col" class="gone text-center">Seleccionar icono</th>
                                <th scope="col" class="gone text-center">Valor del icono</th>
                                <th scope="col" class="text-center">Icono</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td class="text-center">1</td>
                                <td> 
                                    <div class="custom-file ">
                                        <input type="file" name="file[]" class="custom-file-input" id="file1" onclick="cambiarIcono(1);" required/>
                                        <label class="custom-file-label" >Seleccionar Archivo</label>
                                    </div> 
                                </td>
                                <td class="text-center">
                                    <input type="text" name="valor[]" id="valor" onkeypress="return soloNumeros(event)" class="input-style-custom" placeholder="Ejemplo: 1" required/>
                                </td>
                                <td class="text-center">
                                    <img src="{{$url}}" width="45" height="45"  id="imgicon1">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="text-center">2</td>
                                <td> 
                                    <div class="custom-file ">
                                        <input type="file" name="file[]" class="custom-file-input" id="file2" onclick="cambiarIcono(2);" required/>
                                        <label class="custom-file-label" >Seleccionar Archivo</label>
                                    </div> 
                                </td>
                                <td class="text-center">
                                    <input type="text" name="valor[]" id="valor" onkeypress="return soloNumeros(event)" class="input-style-custom" placeholder="Ejemplo:  2" required/>
                                </td>
                                <td class="text-center">
                                    <img src="{{$url}}" width="45" height="45"   id="imgicon2">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="text-center">3</td>
                                <td> 
                                    <div class="custom-file ">
                                        <input type="file" name="file[]" class="custom-file-input" id="file3" onclick="cambiarIcono(3);" required/>
                                        <label class="custom-file-label" >Seleccionar Archivo</label>
                                    </div> 
                                </td>
                                <td class="text-center" >
                                    <input type="text" name="valor[]" id="valor" onkeypress="return soloNumeros(event)" class="input-style-custom" placeholder="Ejemplo:  3" required/>
                                </td>
                                <td class="text-center">
                                    <img src="{{$url}}" width="45" height="45"  id="imgicon3">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <div  class="center button-style w-10 " id="guardar" >Guardar</div>
                </form>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script> let urlImg ='@php echo $url  @endphp'; </script>
        <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>
        <script>
            
            let count_params_icons = 4;

            $(document).ready(function() {
                
                $("#agregar-iconos").on('click',function(){  
     
                    if(count_params_icons < 6 ){
                        let table= `<tr class="rows_items_icons" id="rowsicons${count_params_icons}">
                                        <td><div type="button" class="btn bg-danger btn-sm" onclick="eliminarIcono(${count_params_icons});" data-toogle="tooltip" title="Eliminar icono" data-placement="right"  ><span><i class="fas fa-trash-alt text-light"></i></span></div></td>
                                        <td><input type="text" class="custom-inp nicon center" value="${count_params_icons}" readonly></td>
                                        <td>
                                            <div class="custom-file ">
                                                <input type="file" name="file[]" class="custom-file-input" id="file${count_params_icons}" onclick="cambiarIcono(${count_params_icons});" required>
                                                <label class="custom-file-label" >Seleccionar Archivo</label>
                                            </div>         
                                        </td>
                                        <td class="text-center" >
                                            <input type="text" name="valor[]" id="valor" class="input-style-custom" placeholder ="Ejemplo: ${count_params_icons}" onkeypress="return soloNumeros(event)" required/>
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
            });
        </script>
    </body>
</html>
