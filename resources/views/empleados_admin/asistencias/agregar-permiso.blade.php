<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">

        @include('includes.header',['title'=>'Permiso general',
        'subtitle'=>'Empleados', 'img'=>'/img/Recurso 16.png',
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
            <div class="article border box-shadow">
                <form method="post" action="{{route('empleado.asistencias.permisoGeneral')}}" class="mt-3 container-fluid" id="form" >
                    <div class="modal-body row pb-2">
                        @csrf
                        <input type="hidden" name="dia" value="{{$dia}}">
                        <div class="col-md-6 mt-1">
                            <label>Fecha de inicio del permiso: </label>
                            <input type="text" name="fecha_inicio" id="fecha_inicio" required class="form-control input-style-custom mb-2 datepicker" value="{{$dia}}" autocomplete="off">
                            <label>Fecha de fin del permiso: </label>
                            <input type="text" name="fecha_fin" id="fecha_fin" required class="form-control input-style-custom mb-2 datepicker" value="{{$dia}}" autocomplete="off">
                            <div class="required">
                                <div>
                                    <input type="checkbox" name="inasistencia" id="inasistencia_" value="1">
                                    <label for="inasistencia_">Inasistencia</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="p_entrada" id="p_entrada_" value="1" >
                                    <label for="p_entrada_">Permiso entrada:</label>
                                </div>
                                <div class="h_entrada d-none" >
                                    <div class="d-flex align-items-end" >
                                        <label class="text-nowrap">Nueva hora de entrada:</label>
                                        <input type="time" name="h_entrada" id="hora_entrada" value="09:00" class="form-control input-style-custom ml-2" required>
                                    </div>
                                </div>
                                <div>
                                    <input type="checkbox" name="p_salida" id="p_salida_" value="1" >
                                    <label for="p_salida_">Permiso salida:</label>
                                </div>
                                <div class="h_salida d-none" >
                                    <div class="d-flex align-items-end">
                                        <label class="text-nowrap">Nueva hora de salida:</label>
                                        <input type="time" name="h_salida" id="hora_salida" value="19:00" class="form-control input-style-custom ml-2" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label>Motivo del permiso:</label>
                                <textarea class="form-control input-style-custom" name="motivo" rows="5" placeholder="Por favor detalle el motivo del permiso otorgado al empleado" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-1 mt-1"></div>
                        <div class="col-md-5 mt-1">
                            <table id="tbl" class="table asignar">
                                <thead>
                                    <tr>
                                        <th scope="col"><input type="checkbox" name="selCheckboxes" id="all"  ></th>
                                        <th scope="col"><strong>MARCAR / DESMARCAR</strong> Selecciona los empleados</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <div class="empleados">
                                        @foreach ($empleados as $empleado)
                                        <tr>
                                            <td><input type="checkbox" name="empleados[]" value="{{$empleado->id}}" id="e{{$empleado->id}}"></td>
                                            <td><label for="e{{$empleado->id}}">{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}</label></td>
                                        </tr>
                                        @endforeach
                                    </div>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <input type="hidden" name="dia_" value="{{$dia}}">
                    <button type="submit" class="button-style center guardar d-none">Guardar</button>
                </form>
            </div>
        </div>
        @include('includes.footer')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
        <script src="{{asset('js/datapicker-es.js')}}"></script>
        <script>

            $(document).ready(function() {

                $("#fecha_inicio").datepicker("setDate", '{{$dia}}');
                $("#fecha_fin").datepicker("setDate", '{{$dia}}');
            
                $("#fecha_inicio").datepicker({ 
                                       
                    dateFormat: 'yy-mm-dd',                                           
                    changeYear: true,
                    changeMonth: true,
                    onSelect: function (date) {      

                        let fechaFinal=date.split("-");
                                
                        $("#fecha_fin").prop('disabled', false);     
                        $("#fecha_fin" ).val('');
                        $("#fecha_fin" ).datepicker( "option", "minDate", date );
                        $("#fecha_fin").datepicker({          
                                        changeYear: true,
                                        changeMonth: true,
                                        dateFormat: 'yy-mm-dd',             
                                        minDate: date
                        });
                                                    
                    }
                });

                $('#p_entrada_').click(function(){
                    $('.h_entrada').toggleClass('d-none');
                    $('#inasistencia_').prop('checked', false);
                });

                $('#p_salida_').click(function(){
                    $('.h_salida').toggleClass('d-none');
                    $('#inasistencia_').prop('checked', false);
                });

                $('#all').click(function(){
        
                    $('.asignar tr:visible input[type=checkbox]').prop('checked', $(this).prop('checked'));
                    ($(this).prop('checked')) ? $('.guardar').removeClass('d-none') : $('.guardar').addClass('d-none');

                });

                $('.asignar input[type=checkbox]').click(function(){

                    ($(".asignar input:checkbox:checked").length > 0) ? $('.guardar').removeClass('d-none') : $('.guardar').addClass('d-none');

                });

                $('#inasistencia_').click(function(){
                    $('#p_entrada_, #p_salida').prop('checked', false);
                    $('.h_entrada, .h_salida').addClass('d-none');
                });

                $('#tbl').DataTable({
                    "order": [[ 0, 'asc' ]],
                    "scrollY": "300px",
                    "scrollCollapse": true,
                    "paging":         false,
                    "language": {
                        search: '',
                        searchPlaceholder: 'Buscar registros',
                        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                    },
                    columnDefs: [{ 
                        className: "text-center", "targets": [0]
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
                
                $('.guardar').click(function(){
          
                    let form = $("#form");
                    if(form.parsley().isValid()){
                        $(this).text('Espere...');
                        $(this).prop('disabled', true);
                        form.submit();
                    }else{
                        form.parsley().validate();
                    }
   
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
