@if(isset(Session::get('empresa')['razon_social']))
    @if (array_key_exists('parametria', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['parametria'] == 1)
        <div>
            <img id="iconNavbar" src="/img/icono-parametria.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#collapseParametriaInicial" role="button" aria-expanded="false"
                   aria-controls="collapseParametriaInicial" onclick="arrowOpen('parametria')"><b>Parametría
                    Inicial</b></label>
            <img src="/img/icono-flecha.png" id="parametria_img" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#collapseParametriaInicial" role="button" aria-expanded="false"
                 aria-controls="collapseParametriaInicial" onclick="arrowOpen('parametria')">
            <div class="collapse bg-color-yellow" id="collapseParametriaInicial">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">
                    @if (array_key_exists('impuestos', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['tabla_isr'] == 1)
                        <a href="{{ route('parametria.isr') }}" class="as-none list-navbar" rel="Tabla ISR">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Alta y edición de los importes del ISR">Tabla ISR</label>
                        </a>
                    @endif
                    @if (array_key_exists('subsidios', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['tabla_subsidios'] == 1)
                        <a href="{{ route('parametria.subsidio') }}" class="as-none list-navbar"
                           rel="Tabla subsidio">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Alta y edición de los importes del subsidio">Tabla subsidio</label>
                        </a>
                    @endif
                    @if( array_key_exists('puestos_empresa', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['puestos_empresa'] == 1)
                        <a href="{{ route('parametria.puestos') }}" class="as-none list-navbar"
                           rel="Tabla subsidio">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Alta, baja y modificación de puestos">Puestos de la empresa</label>
                        </a>
                    @endif
                    @if( array_key_exists('departamentos', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['departamentos_empresa'] == 1)
                        <a href="{{ route('parametria.departamentos.inicio') }}" class="as-none list-navbar"
                           rel="Tipos de prestaciones">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Alta y edición de tipos de prestaciones">Departamentos de la
                                empresa</label>
                        </a>
                    @endif
                    @if( array_key_exists('categorias', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['tipo_prestaciones'] == 1)
                        <a href="{{ route('parametria.prestaciones.inicio') }}" class="as-none list-navbar"
                           rel="Tipos de prestaciones">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Alta y edición de tipos de prestaciones">Tipos de
                                prestaciones </label>
                        </a>
                    @endif
                    @if( array_key_exists('conceptos_nomina', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['conceptos_nomina'] == 1)
                        <a href="{{ route('parametria.conceptos-nomina') }}" class="as-none list-navbar"
                           rel="Conceptos de  nomina">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Vista y edición de los conceptos de nomina">Conceptos de
                                nómina</label>
                        </a>
                    @endif
                    @if (array_key_exists('periodos_nomina', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['periodos_nomina'] == 1)
                        <a href="{{ route('nomina.periodos') }}" class="as-none list-navbar"
                           rel="Periodos de nomina">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Periodos de
                                nómina</label>
                        </a>
                    @endif
                    @if (array_key_exists('periodos_nomina', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['kit_baja'] == 1)
                        <a href="{{ route('kitbaja.tabla') }}" class="as-none list-navbar"
                           rel="Configuración kit de baja de empleado">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Configuración
                                kit de baja</label>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if (array_key_exists('procesos_calculo', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['procesos_calculo'] == 1)
        <div>
            <img id="iconNavbar" src="/img/icono-calculo.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#collapseProcesosCalculo" role="button" aria-expanded="false"
                   aria-controls="collapseProcesosCalculo" onclick="arrowOpen('calculo')"><b>Procesos de
                    Cálculo</b></label>
            <img src="/img/icono-flecha.png" id="calculo" value="0" class="navbar-arrow" data-toggle="collapse"
                 href="#collapseProcesosCalculo" role="button" aria-expanded="false"
                 aria-controls="collapseProcesosCalculo" onclick="arrowOpen('calculo')">
            <div class="collapse bg-color-yellow" id="collapseProcesosCalculo">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">
                    @if (array_key_exists('captura_incidencias', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['captura_incidencias'] == 1)
                        <a href="{{ route('procesos.periodos.nomina.prenomina') }}" class="as-none list-navbar"
                           rel="Captura de incidencias">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Departamentos disponibles para timbrado">Captura de
                                incidencias</label>
                        </a>
                    @endif
                    @if (array_key_exists('timbrado_nomina', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['timbrado_nomina'] == 1)
                        <a href="{{ route('timbrar.nomina') }}" class="as-none list-navbar"
                           rel="Tabla Subsidio">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Departamentos disponibles para timbrado">Timbrado de Nómina</label>
                        </a>
                    @endif
                    @if (array_key_exists('timbrado_asimilados', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['timbrado_asimilados'] == 1)
                        <a href="{{ route('timbrar.nomina') }}" class="as-none list-navbar"
                           rel="Tabla Subsidio">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Departamentos disponibles para timbrado">Timbrado de
                                Asimilados</label>
                        </a>
                    @endif

                    @if (array_key_exists('abrir_nomina', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['abrir_nomina'] == 1)
                        <a href="{{ route('calculo.nomina') }}" class="as-none list-navbar"
                           rel="Periodos de nomina">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Cálculo de
                                nómina</label>
                        </a>
                    @endif

                    @if (array_key_exists('aguinaldo', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['aguinaldo'] == 1)

                        <a href="{{ route('procesos.calculo.aguinaldo') }}" class="as-none list-navbar"
                           rel="Periodos de nomina">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Reporte de aguinaldo por departamento">Cálculo de aguinaldo</label>
                        </a>
                    @endif

                    @if (array_key_exists('timbrado_aguinaldo', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['timbrado_aguinaldo'] == 1)

                        <a href="{{ route('timbrar.aguinaldo.paso1') }}" class="as-none list-navbar"
                           rel="Periodos de nomina">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Departamentos disponibles para timbrado de aguinaldo">Timbrado de
                                aguinaldo</label>
                        </a>
                    @endif

                    @if (array_key_exists('finiquitos', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['finiquitos'] == 1)

                        <a href="{{ route('procesos.finiquito') }}" class="as-none list-navbar"
                           rel="Periodos de nomina">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Reporte de aguinaldo por departamento">Cálculo de finiquito</label>
                        </a>
                    @endif

                    @if (array_key_exists('timbrado_finiquito', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['timbrado_finiquito'] == 1)

                        <a href="{{ route('timbrar.finiquito.inicio') }}" class="as-none list-navbar"
                           rel="Periodos de nomina">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Departamentos disponibles para timbrado de finiquito">Timbrado de
                                finiquito</label>
                        </a>
                    @endif

                    @if (array_key_exists('dispersion_bancaria', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['dispersion_bancaria'] == 1)

                        <a href="{{ route('procesos.dispersion.inicio') }}" class="as-none list-navbar"
                           rel="Dispersiones">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Dispersiones ">Dispersiones</label>
                        </a>
                    @endif

                </div>
            </div>
        </div>
    @endif

    @if (array_key_exists('empleados', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['empleados'] == 1)

        <div>
            <img id="iconNavbar" src="/img/icono-empleados.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#empleados_navbar" role="button" aria-expanded="false" aria-controls="empleados_navbar"
                   onclick="arrowOpen('empleado')"><b>Empleados</b></label>
            <img src="/img/icono-flecha.png" id="empleado" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#empleados_navbar" role="button" aria-expanded="false"
                 aria-controls="empleados_navbar" onclick="arrowOpen('empleado')">
            <div class="collapse bg-color-yellow" id="empleados_navbar">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">


                    @if (array_key_exists('control_empleados', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['control_empleados'] == 1)
                        <a href="{{route('empleados.empleados')}}" class="as-none list-navbar"
                           rel="Usuarios del sistema">

                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Control
                                de empleados</label>
                        </a>
                    @endif

                    @if (array_key_exists('cuentas_bancarias', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['cuentas_bancarias'] == 1)
                        <a href="{{ route('cuentas.ver') }}" class="as-none list-navbar"
                           rel="Cuentas bancarias">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Cuentas
                                bancarias</label>
                        </a>
                    @endif
                    @if (array_key_exists('reingresos', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['reingresos'] == 1)
                        <a href="{{ route('reingresos.tabla') }}" class="as-none list-navbar"
                           rel="Reingresos">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Reingresos</label>
                        </a>
                    @endif
                    @if (array_key_exists('kit_baja', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['kit_baja'] == 1)
                        <a href="{{ route('empleados.kitBajaTabla') }}" class="as-none list-navbar"
                           rel="Kit de baja">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Kit de
                                baja</label>
                        </a>
                    @endif
                    @if (array_key_exists('asistencia', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['asistencia'] == 1)
                        <a href="{{ route('empleado.asistencias.inicio') }}" class="as-none list-navbar"
                           rel="Usuarios del sistema">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Asistencias</label>
                        </a>
                    @endif

                    @if (array_key_exists('prestaciones_extras', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['prestaciones_extras'] == 1)

                        <a href="{{ route('prestaciones.extras.inicio') }}" class="as-none list-navbar"
                           rel="Usuarios del sistema" title="Gestión de usuarios con prestaciones">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Prestaciones
                                extras</label>
                        </a>
                    @endif
                </div>
            </div>

        </div>
    @endif



    @if (array_key_exists('imss', Session::get('usuarioPermisos')) && !Session::get('empresa')['sss'] && Session::get('usuarioPermisos')['imss'] == 1)
        <div>
            <img id="iconNavbar" src="/img/icono-administracion.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse" href="#imss_navbar"
                   role="button" aria-expanded="false" aria-controls="imss"
                   onclick="arrowOpen('herramienta')"><b>IMSS</b></label>
            <img src="/img/icono-flecha.png" id="imss_nav" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#imss_navbar" role="button" aria-expanded="false"
                 aria-controls="imss_navbar" onclick="arrowOpen('imss')">
            <div class="collapse bg-color-yellow" id="imss_navbar">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">

                    @if (array_key_exists('registro_incapacidades', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['registro_incapacidades'] == 1)

                        <a href="{{ route('incapacidades.inicio') }}" class="as-none list-navbar"
                           rel="Formulario">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Control de
                                incapacidades</label>
                        </a>
                    @endif
                    @if (array_key_exists('movi_afiliatorios', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['movi_afiliatorios'] == 1)

                        <a href="{{ route('afiliaciones.inicio') }}" class="as-none list-navbar"
                           rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Movimientos
                                afiliatorios</label>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if (array_key_exists('contabilidad', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['contabilidad'] == 1)
        <div>

            <img id="iconNavbar" src="/img/icono-calculo.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#contabilidad_navbar"
                   role="button" aria-expanded="false" aria-controls="contabilidad_navbar"
                   onclick="arrowOpen('calculo')"><b>Contabilidad</b></label>
            <img src="/img/icono-flecha.png" id="contabilidad_nav" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#contabilidad_navbar" role="button" aria-expanded="false"
                 aria-controls="contabilidad_navbar" onclick="arrowOpen('calculo')">
            <div class="collapse bg-color-yellow" id="contabilidad_navbar">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">

                    @if (array_key_exists('control_polizas', Session::get('usuarioPermisos')))
                        <a href="{{route('poliza.index')}}" id="linkm" class="as-none list-navbar"
                           title="Generación de pólizas de nómina" hidden>
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Control
                                de pólizas</label>

                        </a>

                    @endif
                    @if (array_key_exists('ctrl_fac', Session::get('usuarioPermisos')))
                        <a href="{{route('poliza.facturas')}}" id="linkm" class="as-none list-navbar"
                           title="Generación de importes a facturar" hidden>
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Control
                                de facturas</label>
                        </a>
                    @endif
                    @if (array_key_exists('facturador', Session::get('usuarioPermisos')))
                        <a href="{{route('factura.index')}}" id="linkm" class="as-none list-navbar"
                           title="Gestión de facturas por cliente">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Facturador</label>
                        </a>

                    @endif
                </div>
            </div>

        </div>

    @endif

    @if (array_key_exists('reportes', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['consultas'] == 1)
        <div>
            <img id="iconNavbar" src="{{asset('/img/icono-consultas.png')}}" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#consultas_navbar" role="button" aria-expanded="false" aria-controls="consultas_navbar"
                   onclick="arrowOpen('consultas')"><b>Consultas</b></label>
            <img src="{{asset('/img/icono-flecha.png')}}" id="consultas_nav" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#consultas_navbar" role="button" aria-expanded="false"
                 aria-controls="consultas_navbar" onclick="arrowOpen('consultas')">

            <div class="collapse bg-color-yellow" id="consultas_navbar">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">
                    @if (array_key_exists('reporte_asistencias', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['reporte_asistencias'] == 1)
                        <a href="{{ route('consultas.reporte-asistencias') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Reporte
                                de asistencias</label>
                        </a>
                    @endif
                    @if (array_key_exists('reporte_acumulados_nomina', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['reporte_acumulados_nomina'] == 1)
                        <a href="{{ route('reporte.acumuladoNomina') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Reporte
                                acumulados de nómina</label>
                        </a>
                    @endif
                    @if (array_key_exists('recibos_nomina', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['recibos_nomina'] == 1)
                        <a href="{{ route('consultas.recibos.nomina.inicio') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Recibos de nómina trimbrados por periodo">Recibos de
                                nómina</label>
                        </a>
                    @endif
                    @if (array_key_exists('docu_empleados', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['docu_empleados'] == 1)
                        <a href="{{ route('doc-empleados.tabla') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Documentos de empleados">Documentos de empleados</label>
                        </a>
                    @endif
                    @if (array_key_exists('recibos_asimilados', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['recibos_asimilados'] == 1)
                        <a href="{{ route('consultas.recibos.nomina.inicio') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Recibos de nómina trimbrados por periodo">Recibos de
                                asimilados</label>
                        </a>
                    @endif
                    @if (array_key_exists('reporte_movi_personal', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['reporte_movi_personal'] == 1)
                        <a href="{{ route('reporte.movimientoPersonal') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Reporte de índice de movimientos de personal">Reporte movimientos
                                de personal</label>
                        </a>
                    @endif
                    @if (array_key_exists('indice_rotacion_personal', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['indice_rotacion_personal'] == 1)
                        <a href="{{ route('reporte.rotacionPersonal') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Reporte de índice de rotación de personal">Índice de rotación de
                                personal</label>
                        </a>
                    @endif
                    @if (array_key_exists('reporte_nominas_periodo', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['reporte_nominas_periodo'] == 1)
                        <a href="{{ route('reporte.nominasPeriodo') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Reporte de nóminas por periodo">Reporte de nóminas por
                                periodo</label>
                        </a>
                    @endif
                    @if (array_key_exists('organigrama', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['organigrama'] == 1)
                        <a href="{{ route('organigrama.inicio') }}" class="as-none list-navbar"
                           rel="consulta">
                            <img src="{{asset('/img/icono-circulo.png')}}" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Organigrama de la empresa">Organigrama</label>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if (array_key_exists('formularios', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['formularios'] == 1)
        <div>
            <img id="iconNavbar" src="/img/icono-administracion.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#formularios_navbar" role="button" aria-expanded="false"
                   aria-controls="formularios_navbar"
                   onclick="arrowOpen('herramienta')"><b>Formulario</b></label>
            <img src="/img/icono-flecha.png" id="herramienta_navbar" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#formularios_navbar" role="button" aria-expanded="false"
                 aria-controls="formularios_navbar" onclick="arrowOpen('formularios')">
            <div class="collapse bg-color-yellow" id="formularios_navbar">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">
                    @if (array_key_exists('encuesta_salida', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['encuesta_salida'] == 1)

                        <a href="{{ route('formularios.inicio') }}" class="as-none list-navbar"
                           rel="Formulario">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Encuestas</label>
                        </a>
                    @endif

                    @if (array_key_exists('encuesta_salida', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['conf_formularios'] == 1)

                        <a href="{{ route('configuracion.formularios.inicio') }}" class="as-none list-navbar"
                           rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Gestión y administración de los contratos">Configuración de
                                formularios</label>
                        </a>
                    @endif

                </div>
            </div>
        </div>
    @endif

    @if (array_key_exists('utilerias', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['herramientas'] == 1)
        <div>
            <img id="iconNavbar" src="/img/icono-administracion.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#herramientas_navbar" role="button" aria-expanded="false"
                   aria-controls="herramientas_navbar"
                   onclick="arrowOpen('herramienta')"><b>Herramientas</b></label>
            <img src="/img/icono-flecha.png" id="herramientas_nav" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#herramientas_navbar" role="button" aria-expanded="false"
                 aria-controls="herramientas_navbar" onclick="arrowOpen('herramienta')">
            <div class="collapse bg-color-yellow" id="herramientas_navbar">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">
                    @if (array_key_exists('configuracion_empresa', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['configuracion_empresa'] == 1)
                        <a href="{{ route('herramientas.parametros') }}" class="as-none list-navbar"
                           rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Vista y edición de los parámetros de la empresa">Parámetros de la
                                empresa</label>
                        </a>
                    @endif

                    @if (array_key_exists('horarios_empleados', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['horarios_empleados'] == 1)

                        <a href="{{route('herramientas.horarios')}}" class="as-none list-navbar" rel="Horarios">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Creación y asignación de horarios para los empleados">Horarios
                                para empleados</label>
                        </a>
                    @endif

                    @if (array_key_exists('vigencia_contratos', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['vigencia_contratos'] == 1)

                        <a href="{{ route('contratos.vigenciacontratos') }}" class="as-none list-navbar"
                           rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Gestión y administración de los contratos">Vigencia de
                                contratos</label>
                        </a>
                    @endif
                    @if (array_key_exists('solicitud_beneficiarios', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['solicitud_beneficiarios'] == 1)

                        <a href="{{ route('prestamos.tabla') }}" class="as-none list-navbar" rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Gestión y administración de solicitudes de beneficiarios">Solicitudes
                                de beneficiarios</label>
                        </a>
                    @endif
                    @if (array_key_exists('categoria_activos', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['categoria_activos'] == 1)

                        <a href="{{ route('categoriaActivo.tabla') }}" class="as-none list-navbar"
                           rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Alta y edición de catrgorias">Categoria de activos</label>
                        </a>
                    @endif
                    @if (array_key_exists('asignar_activos', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['asignar_activos'] == 1)

                        <a href="{{ route('asignaActivos.tabla') }}" class="as-none list-navbar"
                           rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Alta y edición de activos">Asignar activos</label>
                        </a>
                    @endif
                    @if (array_key_exists('avisos_rh', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['avisos_rh'] == 1)
                        <a href="{{ route('herramientas.avisos.multimedia.inicio') }}"
                           class="as-none list-navbar" rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Avisos recursos humanos">Avisos RH</label>
                        </a>
                    @endif

                    @if (array_key_exists('vcard', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['vcard'] == 1)
                        <a href="{{ route('herramientas.inicioVcard') }}"
                           class="as-none list-navbar" rel="Herramientas">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Avisos recursos humanos">Configuración de Vcard</label>
                        </a>
                    @endif
                        @if (array_key_exists('vcard', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['biometricos_confg'] == 1)
                            <a href="{{ route('sistema.biometricos.inicio') }}"
                               class="as-none list-navbar" rel="Herramientas">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                       title="Sección para el alta de biometricos">Configuración de Biometricos</label>
                            </a>
                        @endif
                </div>
            </div>
        </div>

    @endif


    @if (array_key_exists('juridico', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['juridico'] == 1)
        <div>
            <img id="iconNavbar" src="/img/icono-empresa.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                   href="#juridico_navbar" role="button" aria-expanded="false" aria-controls="juridico_navbar"
                   onclick="arrowOpen('herramienta')"><b>Juridico</b></label>
            <img src="/img/icono-flecha.png" id="herramienta" value="0" class="navbar-arrow"
                 data-toggle="collapse" href="#juridico_navbar" role="button" aria-expanded="false"
                 aria-controls="juridico_navbar" onclick="arrowOpen('herramienta')">
            <div class="collapse bg-color-yellow" id="juridico_navbar">
                <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">
                    @if (array_key_exists('demandas', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['demandas'] == 1)
                        <a href="{{ route('demandas.inicio') }}" class="as-none list-navbar" rel="juridico">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Demandas">Demandas</label>
                        </a>
                    @endif


                    @if (array_key_exists('calendario_demandas', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['calendario_demandas'] == 1)
                        <a href="{{ route('demandas.calendario') }}" class="as-none list-navbar"
                           rel="juridico">
                            <img src="/img/icono-circulo.png" class="circle-navbar">
                            <label class="text-left font-size-0-5em font-weight-bold cursor-pointer"
                                   title="Demandas">Calendario de demandas</label>
                        </a>
                    @endif

                </div>
            </div>
        </div>
    @endif
    @if (array_key_exists('norma035', Session::get('usuarioPermisos')) && Session::get('usuarioPermisos')['norma035'] == 1)

        <div>
            <img id="iconNavbar" src="/img/icono-nom.png" class="icon-navbar">
            <a href="{{ route('norma.normaTabla') }}" class="as-none" rel="Norma 035">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Norma
                        035</b></label>
            </a>
        </div>

    @endif
    <div class="navbar-underline"></div>
@endif