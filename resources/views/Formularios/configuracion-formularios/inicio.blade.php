<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
            @include('includes.header',['title'=>'Formularios', 'subtitle'=>'Configuración formularios', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])

            @if(session()->has('success'))
                <div class="row">
                    <div class="alert alert-success" style="width: 100%;" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>Notificación: </strong>
                        {{ session()->get('success') }}
                    </div>
                </div>
            @endif
            <div>
                <a href="{{ url('/configuracion-formulario-agregar') }}" ref="Agregar configuracion formulario">
                    <button type="button" class="button-style">
                        Crear nuevo
                    </button>
                </a>
                <a href="{{ url('/formulario-inicio') }}" ref="Crear formulario">
                    <button type="button" class="button-style">
                        Crear formulario
                    </button>
                </a>
                <br>
                <br>
            </div>
            <div class="article border">
                <table id="tbl" class="table w-100">
                    <thead>
                        <tr>
                            <th scope="col" class="gone">Titulo</th>
                            <th scope="col" class="gone">Fecha creación</th>
                            <th scope="col" class="gone">Estatus</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (sizeof($consultas) > 0)
                          @foreach ($consultas as $consulta)
                              <tr>
                                  <td>{{$consulta->titulo}}</td>
                                  <td>{{$consulta->created_at}}</td>
                                  <td>
                                        @if($consulta->estatus==1)
                                            <span class="text-success pull-right">Activo</span>
                                        @else
                                            <span class="text-danger  pull-right">Inactivo</span>
                                        @endif
                                  </td>
                                  <td>
                                        @if($consulta->estatus==1)
                                            @php $arr = array('id' => Crypt::encrypt($consulta->id) ,'titulo'=> $consulta->titulo ) @endphp
                                            <a href="{{route('configuracion.formularios.obtenerFormularios',$arr )}}">
                                                <div type="button" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar icono" data-placement="right" title="editar icono formulario" rel="Editar icono formulario">
                                                    <span><i class="fas fa-edit"></i></span>
                                                </div>
                                            </a>
                                            <div type="button" 
                                                class="btn btn-danger btn-sm deshabilitar-item"  
                                                data-toggle="tooltip"
                                                title="Eliminar icono" 
                                                data-placement="right" 
                                                rel="Eliminar icono formulario"  
                                                data-titulo="{{$consulta->titulo}}"
                                                data-id="{{Crypt::encrypt($consulta->id)}}"
                                                data-estatus="2" >
                                                <span><i class="fas fa-trash-alt text-light"></i></span>
                                            </div> 
                                        @else
                                            <div type="button" 
                                                class="btn btn-warning btn-sm deshabilitar-item" 
                                                data-toggle="tooltip" 
                                                title="Visualizar icono" 
                                                data-placement="right" 
                                                rel="Visualizar icono formulario"
                                                data-titulo="{{$consulta->titulo}}"
                                                data-id="{{Crypt::encrypt($consulta->id)}}"
                                                data-estatus="1" 
                                            ><span><i class="far fa-eye"></i></span></div>
                                        @endif
                                  </td>
                              </tr>
                          @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <script>

            $(document).ready(function() {
                  
                $('#tbl').DataTable({
                    "order": [[ 1, 'desc' ]],
                    "language": {
                        search: '',
                        searchPlaceholder: 'Buscar registros',
                        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                    },
                    columnDefs: [{ 
                        className: "text-center", "targets": [3]
                    }],
                });

                $(".deshabilitar-item").on('click',function(){
                    
                    let data = { 'id':$(this).data('id'), 'titulo': $(this).data('titulo'), 'estatus' :$(this).data('estatus') };
               
                    swal({
                            title:($(this).data('estatus')==2) ?"¿Estás seguro de desactivar este registro ?" :"¿Estás seguro de activar este registro ?" ,
                            text: "Podrá volver a activarlo cuando lo llegue a requerir  !",
                            icon: "warning",
                            buttons:  ["Cancelar", true],
                            dangerMode: true,

                        }).then((willDelete) => {
                            if (willDelete) {

                                const url = "{{route('configuracion.formularios.deshabilitarIconos')}}";
                                $.get(url,data,function(res){
                                    location.reload();
                                });
                            }
                        });
                   
                });
            });
        </script>
    </body>
</html>
