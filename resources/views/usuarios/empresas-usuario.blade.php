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
    @include('includes.header',['title'=>'Empresas del usuario', 'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'sistema.usuarios.usuariosistema'])

        <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
        <br>
        <br>
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
            <table id="empresasusuario" class="table w-100 text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Razón social</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($empresas as $empresa)
                    <tr id="{{ $empresa->id }}">
                        <td>{{ $empresa->id }}</td>
                        <td>{{ $empresa->razon_social }}</td>
                        <td class="text-center">
                            <div class="btn  btn-sm link desasociar_emp {{( in_array($empresa->id, $usuario_empresas) ) ? '':'d-none' }}" data-id="{{$empresa->id}}" data-sss="{{$empresa->sss}}" alt="Asociar Empresa" title="Desasociar Empresa">
                                <img src="/img/icono-asociar-e.png" class="button-style-icon">
                            </div>
                            <div class="btn btn-success btn-sm asociar_emp {{( !in_array($empresa->id, $usuario_empresas) ) ? '':'d-none'}}" data-id="{{$empresa->id}}" data-sss="{{$empresa->sss}}" alt="Asociar Empresa" title="Asociar Empresa">
                                <i class="fas fa-check-circle" style="font-size:17px"></i> Asociar Emp.
                            </div>
                            
                            @if ($empresa->sss === 0)
                            <a href="{{ route('usuarios.depto',[$empresa->id,$usuario]) }}" ref="Asignar departamentos a la empresa">
                                <div class="btn btn-sm deptos {{( !in_array($empresa->id, $usuario_empresas) ) ? 'd-none':''}}" alt="Asignar Departamentos" title="Asignar Departamentos" data-toggle="modal" data-target="#deptosModal" data-empresaBase="{{$empresa->base}}" data-empresaid="{{$empresa->id}}">
                                <img src="/img/icono-departamentos.png" class="button-style-icon">
                                </div>
                            </a>
                            @if($empresa->sede == 1)
                            <a href="{{ route('usuarios.sede',[$empresa->id,$usuario]) }}" ref="Asignar departamentos a la empresa">
                                <div class="btn btn-sm sedes {{( !in_array($empresa->id, $usuario_empresas) ) ? 'd-none':''}}" alt="Asignar Sedes" title="Asignar Sedes" data-toggle="modal" data-target="#sedesModal" data-empresaBase="{{$empresa->base}}" data-empresaid="{{$empresa->id}}">
                                <img src="/img/icono-sedes.png" class="button-style-icon">
                                </div>
                            </a>
                            @endif
                            @endif

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <form method="post" id="submit_sedesdeptos" action="{{route('usuarios.asociarempresa')}}">
        @csrf
        <input type="hidden" name="empresa" id="emrpesa_id">
        <input type="hidden" name="sss" id="sss_id">
        <input type="hidden" name="usuario" value="{{ $usuario }}">
    </form>
    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script type="text/javascript">
        let dataSrc = [];
        let table = $('#empresasusuario').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por razón social',
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


        $(".asociar_emp").click(function() {
            asociar = $(this).data("id");
            sss = 0;
            document.getElementById("emrpesa_id").value = asociar;
            document.getElementById("sss_id").value = sss;

            document.getElementById("submit_sedesdeptos").submit();
        });


        $(".desasociar_emp").click(function() {
            asociar = $(this).data("id");
            sss = 1;
            document.getElementById("emrpesa_id").value = asociar;
            document.getElementById("sss_id").value = sss;

            document.getElementById("submit_sedesdeptos").submit();
        });
    </script>
