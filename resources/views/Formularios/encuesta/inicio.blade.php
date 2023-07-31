<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
            
        @include('includes.header',['title'=>'Encuestas',
        'subtitle'=>'Formularios', 'img'=>'img/header/norma/icono-norma.png',
        'route'=>'bandeja'])
           
            @if(session()->has('success'))
                <div class="row">
                    <div class="alert alert-success" style="width: 100%;" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>Notificación: </strong>
                        {{ session()->get('success') }}
                    </div>
                </div>
            @elseif(session()->has('danger'))
                <div class="row">
                    <div class="alert alert-danger" style="width: 100%;" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>Notificación: </strong>
                        {{ session()->get('danger') }}
                    </div>
                </div>
            @endif
            <div class="row d-flex justify-content-between">
                <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                    <div class="">
                        <a href="{{ route('formularios.agregar') }}" ref="Usuarios del sistema">
                            <button type="button" class="button-style ml-3 mb-3">
                                <img src="{{asset('/img/icono-crear.png')}}" class="button-style-icon"> Crear nuevo
                            </button>
                        </a>
                    </div>                    
                </div>
                <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>
            </div>

            <div class="article border">
                <table id="tbl" class="table w-100">
                    <thead>
                        <tr>
                            <th scope="col" class="gone">Titulo</th>
                            <th scope="col" class="gone">Descripción</th>
                            <th scope="col" class="gone">Estatus</th>
                            <th scope="col" class="gone">Creación</th>
                            <th scope="col" class="text-center">Vencimiento</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($consultas) > 0)
                            @php $data=[] @endphp
                            @foreach($consultas as $consulta )
                                
                                @php 
                                    $descripcion=$consulta->descripcion;
                                    $descripcion_completa=str_replace(array('\'', '"'), '', $descripcion);
                                    $id = array('id'=>Crypt::encrypt($consulta->id));
                                    $todos_los_privilegios='<a href='.route('formularios.obtenerDatosPorEncuesta',$id).' data-toggle="tooltip" data-placement="right" title="Modificar encuesta"><img src="'.asset('/img/icono-editar.png').'" class="button-style-icon"></a> <a href='.route('formularios.asignarEncuesta',$id).' class="btn btn-sm" data-toggle="tooltip" data-placement="right" title="Asignar encuesta"><img src="'.asset('/img/header/administracion/icono-usuario.png').'" class="button-style-icon"></a> <a href='.route('formularios.obtenerEmpleadosAsignados',$id).' class="btn mr-1 btn-sm" data-toggle="tooltip" data-placement="right" title="Ver resultados"><img src="'.asset('/img/icono-resultados.png').'" class="button-style-icon"></a><a href='.route('formularios.visualizarEncuesta',$id).' class="btn btn-sm" data-toggle="tooltip" data-placement="right" title="Visualizar encuesta"><img src="'.asset('/img/icono-reporte.png').'" class="button-style-icon"></a> 
                                    <div type="button" class="borrar btn btn-sm mr-2" rel="Eliminar formulario '.$consulta->titulo.'"data-toggle="modal"  data-toggle="tooltip" title="Desabilitar Encuesta" data-target="#modaldesable" onclick="deshabilitarItem(\''.Crypt::encrypt($consulta->id).'\',\''.$consulta->estatus.'\');" > <img src="'.asset('/img/eliminar.png').'" class="button-style-icon"> </a></div> ';
                                    $solo_visualizar=' <div type="button" class="borrar btn btn-sm mr-2" rel="Eliminar formulario '.$consulta->titulo.'"data-toggle="modal"  data-toggle="tooltip" title="Activar Encuesta" data-target="#modaldesable" onclick="deshabilitarItem(\''.Crypt::encrypt($consulta->id).'\',\''.$consulta->estatus.'\');" > <img src="'.asset('/img/eliminar.png').'" class="button-style-icon"> </div>';
                                    $vista_formulario_encuesta='<a href='.route('formularios.visualizarEncuesta',$id).' class="btn  bg-color-yellow  btn-sm" data-toggle="tooltip" data-placement="right" title="Visualizar encuesta"><img src="'.asset('/img/icono-reporte.png').'" class="button-style-icon"></a> ';

                                    $privilegios=""; $estado_encuesta="";

                                    if($admin==1 && $consulta->estatus==1 || $tipo_usuario==1){

                                        $privilegios=$todos_los_privilegios ; // todos los privilegios asiignados
                                        $estado_encuesta='<h6><span class="estatus font-weight-bold text-success pull-right">Activo</span></h6>';
                                    
                                    }elseif($admin==0 && $consulta->id!==1 ){
                                        
                                        ($consulta->estatus==1)? $privilegios=$todos_los_privilegios: $privilegios=$solo_visualizar;
                                    
                                    }elseif($admin==1 && $consulta->estatus==2){

                                        $privilegios=$solo_visualizar;
                                        $estado_encuesta='<h6><span class="estatus font-weight-bold text-danger pull-right">Inactivo</span></h6>'; 

                                    }elseif($admin==1 && $consulta->estatus==3 || $tipo_usuario==1){
                                    
                                        $privilegios=$vista_formulario_encuesta;
                                        $estado_encuesta='<h6><span class="estatus font-weight-bold text-info pull-right">Informativo</span></h6>';
                                    }

                                    if(strlen($descripcion) > 25 ){
                                        
                                        $descripcion=substr($descripcion, 0, 20);
                                        $descripcion = $descripcion.' ... '.'<div class="btn bg-color-yellow  btn-sm text-white"  onclick="mostrar_descripcion(\''.$descripcion_completa.'\')" >Leer más</div>';
                                    }
                                @endphp

                                <tr>
                                    <td>{{$consulta->titulo}}</td>
                                    <td>@php echo $descripcion @endphp</td>
                                    <td class="gone">@php echo $estado_encuesta @endphp</td>
                                    <td>{{Carbon\Carbon::parse($consulta->created_at)->format('d-m-Y h:i')}}</td>
                                    <td class="text-center gone">{{($consulta->fecha_vencimiento < 1 )? $consulta->fecha_vencimiento:Carbon\Carbon::parse($consulta->fecha_vencimiento)->format('d/m/Y h:i')}}</td>
                                    <td class="text-center">@php echo $privilegios @endphp </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @include('formularios.encuesta.modals.descripcion_modal')

        <script>
            const cambiaStatusEncuesta="{{route('formularios.cambiaStatusEncuesta')}}"; // Aquí se habilitá o deshabilita una encuesta  ok

            // $(document).ready(function() {                
            //     $('#tbl').DataTable({
            //         "order": [[ 3, 'desc' ]],
            //         "language": {
            //             search: '',
            //             searchPlaceholder: 'Buscar registros',
            //             "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            //         },
            //         initComplete: function () {
            //             // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
            //             elementos = $(".dataTables_filter > label > input").detach();  
            //             elementos.appendTo('#div_buscar');
            //             $("#div_buscar > input").addClass("input-style-custom");
            //         },
            //     });
            // });

            const mostrar_descripcion = descripcion =>{

                let descripcion_encuesta=document.getElementById('descripcion_encuesta') ;
                descripcion_encuesta.innerHTML=descripcion;
            
                $("#descripcion_modal").modal("show");
            }

            const deshabilitarItem=(idencuesta,estatus)=>{

                let estado = Number(estatus);
                (estado == 1 ) ? estado ="desactivar" : estado ='activar';
                swal({
                        title: `¿Estás seguro de ${estado} está encuesta?`,
                        text: "Al desactivar está encuesta podrá activarla cuando sea necesario !",
                        icon: "warning",
                        buttons:  ["Cancelar", true],
                        dangerMode: true,
                    
                    }).then((willDelete) => {
                        if (willDelete) {
                            
                            eliminarDatos(idencuesta,estatus).then(data=>{
                                if(data.respuesta){
                                    swal("Datos actualizados  correctamente!", {
                                        icon: "success",
                                    });
                                    window.location.href = "{{route('formularios.inicio')}}";
                                }else{

                                    swal("Error al desactivar los datos comunicate con tu adminstrador!", {
                                        icon: "error",
                                    });
                                }
                            }); 
                        }
                    });
            };

            const eliminarDatos = async(idencuesta,estatus)=>{

                let estado ="";
                (estatus==1) ? estado =2 : estado = 1;
                const resp = await fetch(`${cambiaStatusEncuesta}/?idencuesta=${idencuesta}&estatus=${estado}`);
                const data = await resp.json();
                return data;
            }
        </script>
        @include('includes.footer')
    </body>
</html>
