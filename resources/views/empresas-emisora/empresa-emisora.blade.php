<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>
<body>
    @include('includes.navbar')
    <div class="container">
    @include('includes.header',['title'=>'Empresas Pagadoras/Emisoras',
        'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'bandeja'])
       
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('empresae.crear') }}" ref="Crear empresareceptora">
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

        <div class="row" id="delete-success" style="display: none;">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: La empresa se elimino correctamente </strong>
            </div>
        </div>

        <div class="article border">
            <table class="table w-100 text-center" id="empresaemisora">
                <thead>
                    <tr>
                        <th class="w-auto">Razón Social</th>
                        <th class="text-center w-auto">Opciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($empresas as $empresa)
                    <tr id="{{$empresa->id}}">
                        <td class="col-lg-4">{{$empresa->razon_social}}</td>
                        <td class="col-lg-4">

                            <a href="{{ route('empresae.editar', $empresa->id) }}" ref="Editar empresa emisora">
                                <button type="button" class="btn btn-sm p-1 mt-2" title="Editar empresa emisora">
                                    <img src="/img/icono-editar.png" class="button-style-icon">
                                </button>
                            </a>
                            <a href="{{ route('empresae.registropatronal', $empresa->id) }}" ref="Reg. Patronal">
                                <button type="button" class="btn btn-sm p-1 mt-2" title="Registro patronal">
                                    <img src="/img/icono-registro-p.png" class="button-style-icon">
                                </button>
                            </a>
                            <button data-id="{{$empresa->id}}" class="borrar btn btn-sm p-1 mt-2" alt="Eliminar" title="Eliminar"><img src="/img/eliminar.png" class="button-style-icon"></button>



                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    </div>
    @include('includes.footer')
    <form method="post" id="delete_form" action="{{ route('empresae.borrarempresaemisora') }}" enctype="multipart/form-data">
        <input type="hidden" name="id" id="id_registro">
        @csrf
    </form>

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script type="text/javascript">
        let dataSrc = [];
        let table = $('#empresaemisora').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por razón social',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [0]).every(function(){

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


        $(".btn.borrar").click(function() {
            $("#delete-success").hide();
            var id = $(this).data('id');

            swal({
                    title: "Estas seguro de eliminar el registro",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        document.getElementById("id_registro").value = id;
                        swal("Espere un momento, la información esta siendo procesada", {
                            icon: "success",
                            buttons: false,
                        });

                        document.getElementById("delete_form").submit();

                    } else {
                        swal("La accion fue cancelada!");
                    }
                });

        });
    </script>
