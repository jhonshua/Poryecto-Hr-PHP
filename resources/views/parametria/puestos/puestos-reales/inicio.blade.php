<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">
        @include('includes.header',['title'=>'Anexar puestos con alias','subtitle'=>'Parametría inicial / Puestos', 'img'=>'img/header/parametria/icono-puestos.png','route'=>'parametria.puestos'])
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
                <div>
                    <button type="button" class="button-style ml-3 mb-3 crear"  data-toggle="modal" data-target="#puestosRealesModal"> <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
                    <button type="button" class="button-style ml-3 mb-3" title="Importar puestos reales masivos" data-toggle="modal" data-target="#importarPuestos"> <img src="/img/icono-importar.png" class="button-style-icon">Importar puestos</button>
                </div>
            </div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>
        <div class="article border">
            <table class="table w-100 text-center" id="tabla_puestos">
                <thead class="text-center">
                    <tr>
                        <th>Id alias</th>
                        <th>Puesto con alias</th>
                        <th>Id puesto</th>
                        <th>Puesto</th>
                        <th>Dependencia</th>
                        <th>Jerarquia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($puestos as $puesto)
                        <tr>
                            <td>{{$puesto->id_alias}}</td>
                            <td>{{$puesto->alias}}</td>
                            <td>{{$puesto->id_puesto}}</td>
                            <td>{{$puesto->puesto}}</td>
                            <td>{{@$puestos[$puesto->dependencia]->puesto .' -- ' }} {{@$puestos[$puesto->dependencia]->alias}}</td>
                            <td>{{$puesto->jerarquia}}</td>
                            <td>
                            <button 
                                data-id="{{$puesto->id_alias}}" 
                                data-id-alias="{{$puesto->id_alias}}"
                                data-nombre="{{$puesto->alias}}"
                                data-id-puesto="{{$puesto->id_puesto}}" 
                                data-puesto="{{$puesto->puesto}}" 
                                data-id-dependencia="{{$puesto->dependencia}}" 
                                data-dependencia="{{@$puestos[$puesto->dependencia]->puesto}}"
                                data-jerarquia="{{$puesto->jerarquia}}" 
                                data-rama="{{$puesto->rama}}" 
                                data-id-detalle ="{{$puesto->id_detalle}}"
                                data-alias-dep ="{{@$puestos[$puesto->dependencia]->alias}}"
                                class="editar btn" alt="Editar Puesto" 
                                title="Editar puesto real" data-toggle="modal" 
                                data-target="#puestosRealesModal"><img src="/img/icono-editar.png" class="button-style-icon"></button>
                            <button data-id="{{$puesto->id}}" class="borrar btn" onclick="validarBorrado('{{Crypt::encrypt($puesto->id)}}');" alt="Eliminar Puesto" title="Eliminar puesto real"><img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('parametria.puestos.puestos-reales.crear-editar-puesto-real-modal')
        @include('parametria.puestos.puestos-reales.importar-puestos-modal')
    </div>
    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_puestos').DataTable({
            scrollY:'65vh',
            "order": [[ 0,"desc" ]],
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por puesto',
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
    

        function validarBorrado(id){            
            swal({
                    title: "",
                    text: "¿Esta seguro de eliminar este puesto?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        borrarPuesto(id);
                    }
                });
        }

        function borrarPuesto(id) {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {
                'id': id,
                '_token': CSRF_TOKEN
            }

            $.ajax({
                url: `{{route('parametria.puesto.real.borrar')}}`,
                type: 'POST',
                data: data,
                dataType: 'JSON',
                beforeSend: function() {},
                success: function(response) {
                    if (response.ok == 1) {
                        swal("El puesto se eliminó correctamente.", {
                            icon: "success",
                        });
                        setTimeout('location.reload()', 500);
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("", "Ocurrió un error al eliminar el registro!", "error");
                    // console.log(errorThrown);
                }
            });           
        }
        
    </script>

