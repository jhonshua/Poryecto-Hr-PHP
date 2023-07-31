<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">

@include('includes.header',['title'=>'Asignar encuesta',
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
    @elseif(session()->has('danger'))

        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
    @endif
    <div class="article border">
        <div class="col-md-12 form-inline my-3">
            <button class="btn bg-color-yellow d-none btn-sm  asignar" type="button">Asignar encuesta  </button>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <h5 class="text-center" >Empleados sin asignación de encuesta</h5>
                <form action="{{route('formularios.agregaEmpleado')}}" method="post" id="empleados_form">
                    @csrf
                    <input type="hidden" name="id_encuesta" value="{{$id}}" >
                    <table id="tbl" class="table w-100 asignar_empleados">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="" id="all"></th>
                                <th scope="col" class="gone">Nombre</th>
                                <th scope="col" class="gone">Departamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($empleados) > 0 )
                                @foreach ($empleados as $empleado)
                                    <tr>
                                        <td><input type="checkbox" name="empleados[]" value="{{Crypt::encrypt($empleado->id)}}"></td>
                                        <td><label>{{$empleado->nombre . " " . $empleado->apaterno}}</label></td>
                                        <td>{{$empleado->departemento}}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th><input class="input_tbl" type="text"/></th>                            
                            </tr>
                        </tfoot>
                    </table>
                </form>
            </div>
            <div class="col-md-6 col-sm-12 ">
                <h5 class="text-center" >Empleados asignados</h5>
                <table id="tbl-empleados" class="table w-100">
                    <thead>
                        <tr>
                            <th scope="col" class="gone">Nombre</th>
                            <th scope="col" class="gone">Departamento</th>
                            <th scope="col" class="gone">Remover</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($empleados_asignados)>0)
                            @foreach($empleados_asignados as $empleado)
                                <tr>
                                    <td>{{$empleado->nombre . " " . $empleado->apaterno}}</td>
                                    <td>{{$empleado->departemento}}</td>
                                    <td width="110px">
                                        <form action="{{route('formularios.desasignarFormulario')}}" method="post">
                                            @csrf
                                            <input type="hidden" name="id_encuesta" value="{{$id}}" >
                                            <input type="hidden" name="id_empleado" value="{{Crypt::encrypt($empleado->id)}}" >
                                            <button class="borrar btn btn-danger btn-sm mr-2"  data-toggle="tooltip" data-placement="right" title="Remover asignación" type="submit">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><input class="input_empleados" type="text"/></th>                            
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script>

    $(document).ready(function() {
        
        $('#tbl').DataTable({
            pageLength : 5,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function () {
                // Apply the search
                this.api().columns().every( function () {
                    let that = this; 
                    $( '#tbl .input_tbl', this.footer() ).on( 'keyup change clear', function () {
                        if ( that.search() !== this.value ) {
                            that
                                .columns(1)
                                .search( this.value )
                                .draw();
                        }
                    } );
                    $('#tbl .input_tbl').attr("placeholder", "Buscar");
                    $("#tbl .input_tbl").addClass("input-style-custom");
                });
            },
            /*columnDefs: [{ 
                className: "text-center", "targets": [0,1]
            }],*/
        });
         
        $('#tbl-empleados').DataTable({
            pageLength : 8,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function () {
                // Apply the search
                this.api().columns().every( function () {
                    let that = this; 
                    $( '#tbl-empleados .input_empleados', this.footer() ).on( 'keyup change clear', function () {
                        if ( that.search() !== this.value ) {
                            that
                                .columns(0)
                                .search( this.value )
                                .draw();
                        }
                    } );
                    $('#tbl-empleados .input_empleados').attr("placeholder", "Buscar");
                    $("#tbl-empleados .input_empleados").addClass("input-style-custom");
                });
            },
            /*columnDefs: [{ 
                className: "text-center", "targets": [0,1]
            }],*/
        });

        $('#all').click(function(){
        
            $('.asignar_empleados tr:visible input[type=checkbox]').prop('checked', $(this).prop('checked'));
            ($(this).prop('checked')) ? $('.asignar').removeClass('d-none') : $('.asignar').addClass('d-none');

        });

        
        $('.asignar_empleados input[type=checkbox]').click(function(){

            ($(".asignar_empleados input:checkbox:checked").length > 0) ? $('.asignar').removeClass('d-none') : $('.asignar').addClass('d-none');
        
        });

         //Asignar btn
        $('.asignar').click(function(){
            
            $(this).text('Espere...');
            $(this).prop('disabled', true);
            $('#empleados_form').submit();
        })

    });
</script>
</body>
</html>
