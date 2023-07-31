<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
            
        @include('includes.header',['title'=>'Asistencias',
        'subtitle'=>'Empleados', 'img'=>'/img/Recurso 16.png'',
        'route'=>'empleado.asistencias.inicio'])

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
                <button type="button" class="button-style" data-toggle="tooltip" title="Sincronizar registros de biometricos"><i class="fas fa-sync"></i></button> 
                <a href="{{ url('/asistencias-agregar-permiso') }}" ref="Agregar permiso">
                    <button type="button" class="button-style " alt="Asignar permiso a todo el listado" title="Asignar permiso a todo el listado">
                        Agregar
                    </button>
                </a>
                <button type="button" class="button-style" data-toggle="modal" data-target="#exportarPModal" title="Exportar">Exportar</button> 
                <button type="button" class="button-style" data-toggle="modal" data-target="#importarModal" title="Importar">Importar</button> 
            </div>
            <br>
            <div class="article border box-shadow">
                <h5 class="text-center"><strong>Asistencias del día: {{ dia(date('N', strtotime($dia))).' '.formatoAFecha($dia)}}</strong></h5>
                <table id="tbl" class="table col-md-12">
                    <thead>
                        <tr>
                            <th scope="col" class="gone">ID</th>
                            <th scope="col" class="gone">Empleado</th>
                            <th scope="col" class="gone">Nombre</th>
                            <th scope="col" class="gone">Departamento</th>
                            <th scope="col" class="gone">Horario entrada</th>
                            <th scope="col" class="gone">Horario salida</th>
                            <th scope="col" class="gone">Horas trabajadas</th>
                            <th scope="col" class="gone">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>
            </div>
        </div>

        @include('includes.footer')
        
        <script>

            $(document).ready(function() {
                  
                $('#tbl').DataTable({
                    "order": [[ 2, 'asc' ]],
                    "language": {
                        search: '',
                        searchPlaceholder: 'Buscar registros',
                        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                    },
                    columnDefs: [{ 
                        className: "text-center", "targets": [0,1,6,7]
                    }],
                });

                $(".btn.borrar").click(function(){
                    var id = $(this).data('id');
                    swal({
                        title: `¿Está seguro de eliminar este tipo de  prestación?`,
                        text: "Al realizar la acción ya no podrá recuperarla !",
                        icon: "warning",
                        buttons:  ["Cancelar", true],
                        dangerMode: true,
                    
                    }).then((willDelete) => {
                        if (willDelete) {
                            eliminarDatos(id).then(data=>{
                                
                                if(data.respuesta){
                                    swal("Datos actualizados  correctamente!", {
                                        icon: "success",
                                    });
                                    
                                    location.reload();
                                }else{

                                    swal("Error al desactivar los datos comunicate con tu adminstrador!", {
                                        icon: "error",
                                    });
                                }
                            }); 
                        }
                    });
                });
                
            });

            const eliminarDatos = async id =>{
                
                let url = "{{route('parametria.prestaciones.borrar')}}";

                const response = await  fetch(url,{
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content'),
                              'Content-Type': 'application/json'},
                    body: JSON.stringify({'id' : id})
                });
                
                const res = await response.json();
                return res;
            }
        </script>
    </body>
</html>
