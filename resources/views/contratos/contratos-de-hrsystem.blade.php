<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>
<body>
    @include('includes.navbar')
    @include('contratos.crear-contrato-de-hrsystem')
    @include('contratos.editar-contrato-de-hrsystem')

    <div class="container">
        @include('includes.header',['title'=>'Contratos de HR-System','subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-contratos.png','route'=>'bandeja'])

        <div class="row">
            <div class="col-md-12">
                <a data-toggle="modal" data-target="#crearcontratoModal" rel="Crear contrato" title="Crear contrato">
                    <button type="button" class="button-style">
                        <img src="/img/icono-crear.png" class="button-style-icon">Crear
                    </button>
                </a>

                <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-3" id="div_buscar"> </div>
                <br>
                <br>
            </div>
        </div>

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
            <table class="table w-100 text-center" id="contratos-hrsystem">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">Nombre</th>
                        <th width="15%">Alias</th>
                        <th class="text-center" width="10%">Tipo</th>
                        <th class="text-center" width="10%">Temporalidad</th>
                        <th class="text-center" width="15%">Archivo</th>
                        <th class="text-center" width="20%">Opciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($contratos as $contrato)

                    <tr id="{{$contrato->id}}">
                        <td width="5%">{{$contrato->id}}</td>
                        <td width="15%">{{$contrato->nombre}}</td>
                        <td width="15%">{{$contrato->alias}}</td>
                        <td class="text-center">{{($contrato->tipo == 'D') ? 'DETERMINADO' : 'INDETERMINDADO'}}</td>
                        <td class="text-center">{{($contrato->temporalidad == 'M') ? 'MESES' : 'DÍAS' }}</td>
                        <td class="text-center" width="20%" id="{{$contrato->id}}-archivo">{{($contrato->archivo) ?: 'N/A'}}</td>
                        <td class="text-center" width="20%">
                            {{-- <button
                                data-id="{{$contrato->id}}"
                            data-nombre="{{$contrato->nombre}}"
                            data-alias="{{$contrato->alias}}"
                            data-archivo="{{$contrato->archivo}}"
                            data-tipo="{{$contrato->tipo}}"
                            data-temporalidad="{{$contrato->temporalidad}}"
                            class="editar btn btn-sm mr-2" alt="Editar Contrato" title="Editar Contrato" data-toggle="modal" data-target="#contratosModal">
                            <img src="/img/icono-editar.png" class="button-style-icon">
                            </button> --}}

                            <a data-toggle="modal" data-target="#editarcontratoModal" data-id="{{$contrato->id}}" data-nombre="{{$contrato->nombre}}" data-alias="{{$contrato->alias}}" data-tipo="{{ $contrato->tipo }}" data-temporalidad="{{ $contrato->temporalidad}}" data-archivo="{{$contrato->archivo}}" class="editar" alt="Ver Contrato" title="Editar Contrato">
                                <img src="/img/icono-editar.png" class="button-style-icon">
                            </a>

                            <button data-id="{{$contrato->id}}" data-nombre="{{$contrato->nombre}}" data-alias="{{$contrato->alias}}" data-archivo="{{$contrato->archivo}}" data-tipo="{{$contrato->tipo}}" data-temporalidad="{{$contrato->temporalidad}}" {{-- data-archivo-url = "{{route('contratos.obtenerContratoDeArchivo',[$contrato->id,$contrato->archivo])}}" --}} data-pdf-url="{{ route('contratos.pdfContrato',$contrato->id ) }}" id="btn-{{$contrato->id}}-pdf" class="pdf-geturl btn  btn-sm mr-2" alt="Contenido Contrato" title="Ver Contrato" data-toggle="modal" data-target="#archivoModal">
                                <img src="/img/icono-pdf.png" class="button-style-icon">
                            </button>

                            {{-- <button class="borrar btn btn-sm mr-2" alt="Borrar contrato" title="Borrar contrato"
                                data-id="{{$contrato->id}}"
                            data-archivo="{{$contrato->archivo}}">
                            <img src="/img/eliminar.png" class="button-style-icon">
                            </button> --}}

                            <a ref="Eliminar contrato" data-id="{{$contrato->id}}" class="borrar" alt="Eliminar Contrato">
                                <img src="/img/eliminar.png" class="button-style-icon">
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
    @include('includes.footer')
    @include('contratos.archivo_modal')


    <form method="post" id="submit_contrato_eliminar" action="{{ route('contrato.eliminarcontrato') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="id_delete" value="" name="id">
    </form>

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script type="text/javascript">

        let dataSrc = [];
        let table = $('#contratos-hrsystem').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [1]).every(function(){

                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }});
                dataSrc.sort();
                
                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value){
                            api.search(value).draw();
                        }
                    }
                );

                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
        });

        $(".borrar").click(function() {
            id = $(this).data('id');
            document.getElementById("id_delete").value = id;
            swal({
                    title: "Estas seguro de eliminar el registro",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {

                        swal("Espere un momento, la información esta siendo procesada", {
                            icon: "success",
                            buttons: false,
                        });

                        document.getElementById("submit_contrato_eliminar").submit();

                    } else {
                        swal("La accion fue cancelada!");
                    }
                });

        });

        $(".editar").click(function() {
            id = $(this).data('id');
            nombre = $(this).data('nombre');
            alias = $(this).data('alias');
            archivo = $(this).data('archivo');
            tipo = $(this).data('tipo');
            temporalidad = $(this).data('temporalidad');

            console.log(archivo);

            document.getElementById("id_contrato_upd").value = id;
            document.getElementById("nombre_upd").value = nombre;
            document.getElementById("alias_upd").value = alias;
            document.getElementById("fiel_upd").value = archivo;
            $('#tipo_upd').val(tipo);
            $('#temporalidad_upd').val(temporalidad);

        });
    </script>