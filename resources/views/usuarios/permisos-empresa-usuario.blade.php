<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
<style type="text/css">
    .top-line-black {
        width: 19%;
    }
</style>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/semantic.min.css"/>
@include('includes.navbar')
<div class="container">

    @include('includes.header',['title'=>'Permisos del usuario',
    'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-permisos-u.png',
     'route'=>'sistema.usuarios.usuariosistema'])

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

        <br>
        <br>
    </div>

    <div class="article border">
        @if(isset($empresa) && $empresa->id > 0)
            <p class="font-size-1-3em">{{ $empresa->razon_social }}</p>
        @else
            <p class="font-size-1-3em">General</p>
        @endif
        <div class="row d-flex justify-content-between">
            <div class="col-lg-4">
                <p class="font-size-1-3em">[{{ $usuario->id }}] {{ $usuario->nombre_completo }}</p>
                <br>
            </div>
            <div class="col-lg-4">
                <select onchange="permisosUsuarioCambiarEmpresa({{ $usuario->id }})" id="idEmpresa"
                        class="form-control float-right input-style-custom select-clase">
                    <option value="0" @if($empresa->id == 0) selected @endif>General</option>
                    >General</option>
                    @foreach ($empresas->sortBy('razon_social') as $emp)
                        <option value="{{ $emp->id }}" class="text-left"
                                @if($empresa->id == $emp->id) selected @endif>{{ $emp->razon_social }}</option>
                    @endforeach
                </select>
                <br>
            </div>
        </div>


        <div class="row">

            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#parametria_permisos" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">{{ $permisos_nombres['parametria'] }}</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="parametria_permisos" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="parametria" name="parametria"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['parametria'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="parametria">Ver Modulo</label>
                                </p>
                            </li>
                            <div id="parametria_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>


            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#procesos_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">{{$permisos_nombres['procesos_calculo']}}</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="procesos_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="procesos_calculo" name="procesos_calculo"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['procesos_calculo'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="procesos_calculo">Ver Modulo</label>
                                </p>
                            </li>

                            <div id="procesos_calculo_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#empleadosP" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">{{ $permisos_nombres['empleados'] }}</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="empleadosP" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="empleados" name="empleados"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['empleados'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="empleados">Ver Modulo</label>
                                </p>
                            </li>
                            <div id="empleados_empleado"></div>


                        </ul>
                    </div>
                </div>
            </div>


            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#imss_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">IMSS</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="imss_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="imss" name="imss"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['imss'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="imss">Ver Modulo</label>
                                </p>
                            </li>
                            <div id="imss_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>


        </div>
        <div class="row">
            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#consultas_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">Consultas</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="consultas_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="consultas" name="consultas"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['consultas'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="consultas">Ver Modulo</label>
                                </p>
                            </li>
                            <div id="consultas_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#formularios_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">{{ $permisos_nombres['formularios'] }}</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="formularios_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="formularios" name="formularios"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['formularios'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="formularios">Ver Modulo</label>
                                </p>
                            </li>
                            <div id="formularios_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#herramientas_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3 shadow-sm">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">Herramientas</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="herramientas_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="herramientas" name="herramientas"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['herramientas'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="herramientas">Ver Modulo</label>
                                </p>
                            </li>

                            <div id="herramientas_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#juridico_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">{{ $permisos_nombres['juridico'] }}</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="juridico_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="juridico" name="juridico"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['juridico'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="juridico">Ver Modulo</label>
                                </p>
                            </li>
                            <div id="juridico_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#norma_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">Norma 035</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="norma_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="norma035" name="norma035"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['norma035'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="norma035">Ver Modulo</label>
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#contabilidad_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">{{ $permisos_nombres['contabilidad'] }}</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="contabilidad_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="contabilidad" name="contabilidad"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['contabilidad'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="contabilidad">Ver Modulo</label>
                                </p>
                            </li>

                            <div id="contabilidad_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>


            <div class="col-lg-3 mb-3">
                <a data-toggle="collapse" href="#sistema_permiso" role="button" aria-expanded="true"
                   aria-controls="collapseExample4" class="w-100 btn input-style btn-block p2-3">
                    <p class="d-flex align-items-center justify-content-between mb-0 px-3 py-2"><strong
                                class="text-uppercase">{{ $permisos_nombres['sistema'] }}</strong><i
                                class="fa fa-angle-down"></i></p>
                </a>
                <div id="sistema_permiso" class="collapse">
                    <div class="card">
                        <ul class="list-group">
                            <li class="list-group-item m-0">
                                <p class="custom-control custom-switch m-0">
                                    <input class="custom-control-input custom-control-input-warning"
                                           id="sistema" name="sistema"
                                           onchange="chkPermiso($(this))"
                                           type="checkbox"
                                           @if($usuario->permisos['sistema'] == 1) checked
                                            @endif>
                                    <label class="custom-control-label font-italic"
                                           for="sistema">Ver Modulo</label>
                                </p>
                            </li>
                            <div id="sistema_categoria"></div>

                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@include('includes.footer')
</body>

<input type="hidden" name="usuario" id="usuario" value="{{$usuario->id}}">
<input type="hidden" name="empresa" id="empresa" value="{{$empresa->id}}">

<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script type="text/javascript">

    $('#urlPermisos').on('change', function () {
        window.location = $(this).val();
    });

    function chkPermiso(permiso) {
        let valor = 99;
        let id_usuario = document.getElementById("usuario").value;
        let id_empresa = document.getElementById("empresa").value;

        if (document.querySelector(`#${permiso[0].id}`).checked) {
            document.querySelector(`#${permiso[0].id}`).checked = true;
            valor = 1;
        } else {
            document.querySelector(`#${permiso[0].id}`).checked = false;
            valor = 0;
        }

        $.ajax({
            url: "{{route('sistema.usuarios.cambiarp')}}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id_usuario: id_usuario,
                id_empresa: id_empresa,
                permiso: permiso[0].id,
                valor: valor
            },
            type: 'POST',
            dataType: 'json',
            success: function (respuesta) {
                if (respuesta) {

                    if (permiso[0].id == 'parametria')
                        parametriaInicial();

                    if (permiso[0].id == 'procesos_calculo')
                        procesosCalculo();

                    if (permiso[0].id == 'empleados')
                        empleadosEmpleados();

                    if (permiso[0].id == 'imss')
                        imss();

                    if (permiso[0].id == 'formularios')
                        formularios();

                    if (permiso[0].id == 'herramientas')
                        herramientas();

                    if (permiso[0].id == 'juridico')
                        juridico();

                    if (permiso[0].id == 'contabilidad')
                        contabilidad();

                    if (permiso[0].id == 'consultas')
                        consultas();
                    if (permiso[0].id == 'sistema')
                        sistema();

                    alertify.success('El usuario se actualizó correctamentees.');
                }

            },
            error: function (xhr, status) {
                alertify.error('Disculpe, existió un problema');
            }
        });

    }

    $(function () {
        $('.select-clase').select2();
        parametriaInicial();
        procesosCalculo();
        empleadosEmpleados();
        imss();
        formularios();
        herramientas();
        juridico();
        contabilidad();
        consultas();
        sistema();
    });

    function permisosUsuarioCambiarEmpresa(usuario) {
        idEmpresa = document.getElementById("idEmpresa").value;
        window.location.replace("/sistema/permisos-del-usuario/" + usuario + "/empresa/" + idEmpresa);
    }

    function parametriaInicial() {
        let html = `
        @foreach($permisos_categorias['parametria'] as $permisos_parametria)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning chkPermiso"
                id="{{ $permisos_parametria }}" name="{{ $permisos_parametria }}"
                onchange="chkPermiso($(this))"
                type="checkbox"
                @if($usuario->permisos[$permisos_parametria] == 1) checked @endif>
                <label class="custom-control-label font-italic"
                for="{{ $permisos_parametria }}">{{ $permisos_nombres[$permisos_parametria] }}</label>
            </p>
        </li>
        @endforeach
        `;
        if (document.querySelector('#parametria').checked) {
            document.querySelector('#parametria_categoria').innerHTML = html;
        } else {
            document.querySelector('#parametria_categoria').innerHTML = '';
        }
    }

    function procesosCalculo() {
        let html = `
        @foreach($permisos_categorias['procesos_calculo'] as $permisos_procesos_calculo)
        <li class="list-group-item m-0">
           <p class="custom-control custom-switch m-0">
               <input class="custom-control-input custom-control-input-warning"
               id="{{ $permisos_procesos_calculo }}" name="{{ $permisos_procesos_calculo }}"
               onchange="chkPermiso($(this))"
               type="checkbox"
               @if($usuario->permisos[$permisos_procesos_calculo] == 1) checked @endif>
               <label class="custom-control-label font-italic"
               for="{{ $permisos_procesos_calculo }}">{{ $permisos_nombres[$permisos_procesos_calculo] }}</label>
           </p>
        </li>
        @endforeach
        `;

        if (document.querySelector('#procesos_calculo').checked) {
            document.querySelector('#procesos_calculo_categoria').innerHTML = html;
        } else {
            document.querySelector('#procesos_calculo_categoria').innerHTML = '';
        }
    }

    function empleadosEmpleados() {
        let html = `
        @foreach ($permisos_categorias['empleados'] as $permisos_empleados)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning"
                    id="{{ $permisos_empleados }}" name="{{ $permisos_empleados }}"
                     onchange="chkPermiso($(this))"
                    type="checkbox"
                    @if($usuario->permisos[$permisos_empleados] == 1) checked @endif >
                    <label class="custom-control-label font-italic"
                    for="{{ $permisos_empleados }}">{{ $permisos_nombres[$permisos_empleados] }}</label>
            </p>
        </li>
        @endforeach
        `;

        if (document.querySelector('#empleados').checked) {
            document.querySelector('#empleados_empleado').innerHTML = html;
        } else {
            document.querySelector('#empleados_empleado').innerHTML = '';
        }
    }

    function imss() {
        let html = `
        @foreach($permisos_categorias['imss'] as $permisos_imss)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning"
                    id="{{ $permisos_imss }}" name="{{ $permisos_imss }}"
                     onchange="chkPermiso($(this))"
                    type="checkbox"
                    @if($usuario->permisos[$permisos_imss] == 1) checked @endif>
                    <label class="custom-control-label font-italic"
                    for="{{ $permisos_imss }}">{{ $permisos_nombres[$permisos_imss] }}</label>
            </p>
        </li>
        @endforeach
        `;

        if (document.querySelector('#imss').checked) {
            document.querySelector('#imss_categoria').innerHTML = html;
        } else {
            document.querySelector('#imss_categoria').innerHTML = '';
        }
    }

    function herramientas() {
        let html = `
        @foreach ($permisos_categorias['herramientas'] as $permisos_utilerias)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
            <input class="custom-control-input custom-control-input-warning"
                onchange="chkPermiso($(this))"
                id="{{ $permisos_utilerias }}" name="{{ $permisos_utilerias }}"
                    type="checkbox"
                    @if($usuario->permisos[$permisos_utilerias] == 1) checked @endif>
                <label class="custom-control-label font-italic"
                    for="{{ $permisos_utilerias }}">{{ $permisos_nombres[$permisos_utilerias] }}</label>
                </p>
            </li>
        @endforeach
        `;

        if (document.querySelector('#herramientas').checked) {
            document.querySelector('#herramientas_categoria').innerHTML = html;
        } else {
            document.querySelector('#herramientas_categoria').innerHTML = '';
        }
    }

    function formularios() {
        let html = `
         @foreach ($permisos_categorias['formularios'] as $permisos_formularios)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning"
                id="{{ $permisos_formularios }}" name="{{ $permisos_formularios }}"
                onchange="chkPermiso($(this))"
                type="checkbox"
                @if($usuario->permisos[$permisos_formularios] == 1) checked @endif >
                <label class="custom-control-label font-italic"
                for="{{ $permisos_formularios }}">{{ $permisos_nombres[$permisos_formularios] }}</label>
            </p>
        </li>
        @endforeach
        `;

        if (document.querySelector('#formularios').checked) {
            document.querySelector('#formularios_categoria').innerHTML = html;
        } else {
            document.querySelector('#formularios_categoria').innerHTML = '';
        }
    }

    function juridico() {
        let html = `
          @foreach ($permisos_categorias['juridico'] as $permisos_juridico)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning"
                id="{{ $permisos_juridico }}" name="{{ $permisos_juridico }}"
                onchange="chkPermiso($(this))"
                type="checkbox"
                @if($usuario->permisos[$permisos_juridico] == 1) checked @endif>
                <label class="custom-control-label font-italic"
                for="{{ $permisos_juridico }}">{{ $permisos_nombres[$permisos_juridico] }}</label>
            </p>
         </li>
         @endforeach
        `;

        if (document.querySelector('#juridico').checked) {
            document.querySelector('#juridico_categoria').innerHTML = html;
        } else {
            document.querySelector('#juridico_categoria').innerHTML = '';
        }
    }

    function contabilidad() {
        let html = `
          @foreach ($permisos_categorias['contabilidad'] as $permisos_contabilidad)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning"
         id="{{ $permisos_contabilidad }}" name="{{ $permisos_contabilidad }}"
                                 onchange="chkPermiso($(this))"
                                 type="checkbox"
                                 @if($usuario->permisos[$permisos_contabilidad] == 1) checked @endif>
                          <label class="custom-control-label font-italic"
                                 for="{{ $permisos_contabilidad }}">{{ $permisos_nombres[$permisos_contabilidad] }}</label>
                      </p>
                  </li>
        @endforeach
        `;

        if (document.querySelector('#contabilidad').checked) {
            document.querySelector('#contabilidad_categoria').innerHTML = html;
        } else {
            document.querySelector('#contabilidad_categoria').innerHTML = '';
        }
    }

    function consultas() {
        let html = `
          @foreach ($permisos_categorias['consultas'] as $permisos_consultas)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning"
         id="{{ $permisos_consultas }}" name="{{ $permisos_consultas }}"
                                 onchange="chkPermiso($(this))"
                                 type="checkbox"
                                 @if($usuario->permisos[$permisos_consultas] == 1) checked @endif>
                          <label class="custom-control-label font-italic"
                                 for="{{ $permisos_consultas }}">{{ $permisos_nombres[$permisos_consultas] }}</label>
                      </p>
                  </li>
        @endforeach
        `;

        if (document.querySelector('#consultas').checked) {
            document.querySelector('#consultas_categoria').innerHTML = html;
        } else {
            document.querySelector('#consultas_categoria').innerHTML = '';
        }
    }

    function sistema() {
        let html = `
          @foreach ($permisos_categorias['sistema'] as $permisos_sistema)
        <li class="list-group-item m-0">
            <p class="custom-control custom-switch m-0">
                <input class="custom-control-input custom-control-input-warning"
                       id="{{ $permisos_sistema }}" name="{{ $permisos_sistema }}"
                          onchange="chkPermiso($(this))"
                          type="checkbox"
                          @if($usuario->permisos[$permisos_sistema] == 1) checked @endif >
                   <label class="custom-control-label font-italic"
                          for="{{ $permisos_sistema }}">{{ $permisos_nombres[$permisos_sistema] }}</label>
               </p>
           </li>
        @endforeach
        `;

        if (document.querySelector('#sistema').checked) {
            document.querySelector('#sistema_categoria').innerHTML = html;
        } else {
            document.querySelector('#sistema_categoria').innerHTML = '';
        }
    }



</script>

</html>
