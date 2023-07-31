<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

    @include('includes.header',['title'=>$prestacion->nombre,
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-prestaciones.png',
        'route'=>'parametria.prestaciones.inicio'])
 
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
        <div>
            @php $id = Crypt::encrypt( $prestacion->id) @endphp
            <button type="button" class="button-style" data-toggle="modal" data-target="#prestacionModal" data-id=""><img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
            <button type="button" class="button-style" data-toggle="modal" data-target="#importarModal"> <img src="/img/icono-importar.png" class="button-style-icon">Importar</button>
            <a href="{{route('parametria.prestaciones.exportar',$id )}}" ref="exportar tipo de prestaciones" target="_blank">
                <button type="button" class="button-style">
                    <img src="/img/icono-exportar.png" class="button-style-icon">
                    Exportar
                </button>
            </a>

            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>
        </div>

        <br>
        <div class="article border">
            <table id="tbl" class="table col-md-12  prestaciones ">
                <thead>
                    <tr>
                        <th scope="col" class="gone">Antiguedad</th>
                        <th scope="col" class="gone">Vacaciones</th>
                        <th scope="col" class="text-center">% De prima vacacional</th>
                        <th scope="col" class="text-center">Aguinaldo</th>
                        <th scope="col" class="text-center">Factor de Integración</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prestaciones as $p)
                    @php
                    $id = Crypt::encrypt($p->id);
                    $data = array('id' =>$id,
                    'antiguedad' => $p->antiguedad,
                    'vacaciones' => $p->vacaciones,
                    'prima_vacacional' => $p->prima_vacacional,
                    'aguinaldo' => $p->aguinaldo,
                    'bono_vacaciones' => $p->bono_vacaciones,
                    'bono_prima_vacacional' => $p->bono_prima_vacacional,
                    'bono_aguinaldo' => $p->bono_aguinaldo,
                    'id_categoria' => Crypt::encrypt($prestacion->id)
                    );
                    @endphp
                    <tr id="{{$id}}" data-antiguedad="{{$p->antiguedad}}">
                        <td class="text-center">{{$p->antiguedad}}</td>
                        <td class="text-center">{{$p->vacaciones}} días</td>
                        <td class="text-center">{{$p->prima_vacacional}}%</td>
                        <td class="text-center">{{$p->aguinaldo}} días</td>
                        <td class="text-center">${{$p->factor_integracion}}</td>
                        <td class="text-center" width="150px">
                            <button class="editar btn btn-sm mr-2" alt="Editar prestación" title="Editar prestación" data-toggle="modal" data-target="#prestacionModal" data-id="{{$p->id}}" data-antiguedad="{{$p->antiguedad}}" data-vacaciones="{{$p->vacaciones}}" data-prima_vacacional="{{$p->prima_vacacional}}" data-aguinaldo="{{$p->aguinaldo}}" data-bvacaciones="{{$p->bono_vacaciones}}" data-bprima_vacacional="{{$p->bono_prima_vacacional}}" data-baguinaldo="{{$p->bono_aguinaldo}}"><img src="/img/icono-editar.png" class="button-style-icon"></button>
                            <button data-id="{{$id}}" class="borrar btn btn-sm mr-2" alt="Eliminar prestación" title="Eliminar prestación"><img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('includes.footer')
    @include('parametria.prestaciones.modal.importar-modal')
    @include('parametria.prestaciones.modal.crear-modal')
    <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
    <script src="{{asset('js/typeahead.js')}}"></script>
    <script>

        let dataSrc = [];
        let table = $('#tbl').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros',
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

        $(document).ready(function() {


            $(document).on('change', 'input[type="file"]', function() {

                let fileName = this.files[0].name;

                if (fileName != "") {

                    let ext_archivo = fileName.split('.').pop();
                    ext_archivo = ext_archivo.toLowerCase();
                    let extension = ['xlsx', 'xls'];

                    if (!extension.includes(ext_archivo)) {


                        this.value = '';
                        swal("El archivo no es valido , intentalo nuevamente !", {
                            icon: "error",
                        });
                    }

                } else {

                    swal("No se a seleccionado ningún archivo !", {
                        icon: "error",
                    });
                }
            });

            $("#importar-excel").on('click', function() {
                let form = $("#addform");
                if (form.parsley().isValid()) {

                    swal({
                        title: `Aviso Importante`,
                        text: 'Al importar esta información se borrarán "TODOS" los datos existentes siendo sustituidos por los nuevos. ¿Deseas continuar?',
                        icon: "warning",
                        buttons: ["Cancelar", true],
                        dangerMode: true,

                    }).then((willDelete) => {
                        if (willDelete) {

                            $(this).text('Espere...');
                            $(this).prop('disabled', true);
                            form.submit();
                        }
                    });
                } else {
                    form.parsley().validate();
                }
            });

            $(".btn.borrar").click(function() {
                var id = $(this).data('id');
                swal({
                    title: `¿Está seguro de eliminar esta prestación?`,
                    text: "Al realizar la acción ya no podrá recuperarla !",
                    icon: "warning",
                    buttons: ["Cancelar", true],
                    dangerMode: true,

                }).then((willDelete) => {
                    if (willDelete) {
                        eliminarDatos(id).then(data => {


                            if (data.respuesta) {

                                $(".prestaciones tr#" + id).remove();
                                swal("Datos actualizados  correctamente!", {
                                    icon: "success",
                                });

                            } else {

                                swal("Error al desactivar los datos comunicate con tu adminstrador!", {
                                    icon: "error",
                                });
                            }
                        });
                    }
                });
            });
        });
        const eliminarDatos = async id => {

            let url = "{{route('parametria.prestaciones.borrarPrestacion')}}";

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    'id': id
                })
            });

            const res = await response.json();
            return res;
        }
    </script>
</body>

</html>
