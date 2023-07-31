<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">

@include('includes.header',['title'=>'Empleados asignados',
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
    <div class="row d-flex justify-content-between">
        <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
            <div class="">
                <!-- <a type="button" class="button-style ml-3 mb-3" href="{{--{{route('parametria.puestos.crear.editar')}}--}}">Crear nuevo</a>        -->
                <!--<button type="button" class="button-style ml-3 mb-3" data-toggle="modal" data-id="" data-target="#puestosModal"> <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>-->
                <div class="dropdown">
                    <button class="button-style ml-3 mb-2 dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Exportar resultados
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="{{route('configuracion.formularios.exportarExcelResultados',['id'=>$id,'estatus'=>5] )}}">Todos</a>
                        <a class="dropdown-item" href="{{route('configuracion.formularios.exportarExcelResultados',['id'=>$id,'estatus'=>3])}}">Exportar contestados</a>
                        <a class="dropdown-item" href="{{route('configuracion.formularios.exportarExcelResultados',['id'=>$id,'estatus'=>1])}}">Exportar no contestados</a>
                        <a class="dropdown-item" href="{{route('configuracion.formularios.exportarExcelResultados',['id'=>$id,'estatus'=>4] )}}">Exportar pendientes</a>
                    </div>
                </div>  
            </div>
        </div>
        <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
    </div>
    <div class="article border">
        <table id="tbl" class="table w-100">
            <thead>
                <tr>
                    <th scope="col" class="gone">Nombre</th>
                    <th scope="col" class="gone">Departamento</th>
                    <th scope="col" class="gone">Proceso</th>
                    <th scope="col" class="gone">Ver resultados</th>
                    <th scope="col" class="gone">Exportar</th>
                </tr>
            </thead>
            <tbody>
                @php $idencuesta="" @endphp
                @if(count($empleados_asignados)>0)
                    @foreach ($empleados_asignados as $key => $empleado)
                    @php $idencuesta=Crypt::encrypt($empleado->id_encuesta) @endphp
                    <tr>
                        <td>{{$empleado->nombre}} {{$empleado->amaterno}} {{$empleado->apaterno}}</td>
                        <td>{{$empleado->departemento}}</td>
                        <td>@if($empleado->estatus == 1) <span class="text-danger">No contestada</span>
                            @elseif ($empleado->estatus == 4) <span class="text-warning-custom">En proceso</span>
                            @elseif ($empleado->estatus == 3) <span class="text-success">Completado / cerrado</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{route('formularios.obtenerResultadosEmpleado',['idemplado' => Crypt::encrypt($empleado->id_empleado),'idencuesta'=>$idencuesta,'nombreEmpleado'=>$empleado->nombre.' '. $empleado->amaterno.' '.$empleado->apaterno,'departamento' => $empleado->departemento,'estatus' => $empleado->estatus ])}}">
                                <div type="button" class="btn btn-sm obtener_resultados" 
                                    data-toggle="tooltip" 
                                    data-placement="right"
                                    title="Visualizar resultados">
                                    <img src="{{asset('/img/ver-documentos-empleado.png')}}" class="button-style-icon">
                                </div>
                            </a>
                        </td>
                        @if($empleado->estatus===3)
                            <td>
                                <form action="{{route('formularios.exportarEncuesta')}}" method="POST" >
                                    @csrf
                                    <input type="hidden" name="id" value="{{$idencuesta}}">
                                    <input type="hidden" name="idempleado" value="{{Crypt::encrypt($empleado->id)}}" >
                                    <input type="hidden" name="nomempleado" value="{{$empleado->nombre}} {{$empleado->amaterno}} {{$empleado->apaterno}}">
                                    <input type="hidden" name="depart" value="{{$empleado->departemento}}">
                                    <input type="hidden" name="fecha_finalizacion" value="{{$empleado->fecha_finalizacion}}">
                                    <input type="hidden" name="fecha_nacimiento" value="{{$empleado->fecha_nacimiento}}">
                                    <input type="hidden" name="correo" value="{{$empleado->correo}}">
                                    <input type="hidden"   name="aux" id="aux{{$key}}" >
                                    <button class="btn btn-sm " data-toggle="tooltip" data-placement="right" title="Exportar pdf" type="submit"   onclick="exportar({{$key}},1)"><img src="{{asset('/img/icono-pdf.png')}}" class="button-style-icon"></button>
                                    <button class="btn btn-sm " data-toggle="tooltip" data-placement="right" title="Exportar excel" type="submit" onclick="exportar({{$key}},2)" ><img src="{{asset('/img/icono-excel.png')}}" class="button-style-icon"></button>
                                </form>
                            </td>
                        @else
                            <td><button class="btn btn-sm " data-toggle="tooltip" data-placement="right" title="Exportar pdf" type="submit" disabled ><img src="{{asset('/img/icono-pdf.png')}}" class="button-style-icon"></button>
                                <button class="btn btn-sm " data-toggle="tooltip" data-placement="right" title="Exportar excel" type="submit" disabled><img src="{{asset('/img/icono-excel.png')}}" class="button-style-icon"></button></td>                            
                        @endif
                    </tr>
                    @endforeach
                @else
                    <tr><td colspan="4" ><p class="text-center">No hay resultados.</p> </td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>


<script src="{{asset('js/typeahead.js')}}"></script>
<script>
    let dataSrc = [];
    let table = $('#tbl').DataTable({
        scrollY: '65vh',
        scrollCollapse: true,
        "language": {
            search: '',
            searchPlaceholder: 'Buscar registros por puesto',
            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
        },
        initComplete: function() {

            let api = this.api();

            api.cells('tr', [1]).every(function() {
                let data = $('<div>').html(this.data()).text();
                if (dataSrc.indexOf(data) === -1) {
                    dataSrc.push(data);
                }
            });
            dataSrc.sort();

            $('.dataTables_filter input[type="search"]', api.table().container())
                .typeahead({
                    source: dataSrc,
                    afterSelect: function(value) {
                        api.search(value).draw();
                    }
                });
            // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
            let elementos = $(".dataTables_filter > label > input").detach();
            elementos.appendTo('#div_buscar');
            $("#div_buscar > input").addClass("input-style-custom");
        },
        "drawCallback": function(settings) {
            $(".btn.borrar").click(function() {
                let id = $(this).data('id');
                validarBorrado(id);
            });
        },
    });

    const exportar = (index,aux) =>{
    
        document.getElementById(`aux${index}`).value=aux;
    }

</script>
@include('includes.footer')
</body>
</html>
