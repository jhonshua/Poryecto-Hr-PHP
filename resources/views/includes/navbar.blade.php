<div id="navbarBack" class="navigation-back" onclick="closeMenu()"></div>
<div id="navbar" class="navigation">
    <i id="iconOpenMenu" class="fas fa-angle-right right" onclick="openMenu()"></i>
    <i id="iconCloseMenu" class="fas fa-times right display-none" onclick="closeMenu()"></i>
    <br>
    <img id="logo_navbar" src="{{asset("/img/logo-navbar.png")}}" class="center top w-px-200 mb-4 navbar-underline" alt="HRSystem"
         title="HRSystem">
    <div class="navigation-scroll">
        @if(isset(Session::get('empresa')['razon_social']))
            <div class="navbar-underline">
                <img id="iconNavbar" src="/img/icono-empresa.png" class="icon-navbar">
                <label class="text-left name-navbar font-size-0-5em ml-2"><b>{{ (Session::get('empresa')['razon_social']) ? substr(Session::get('empresa')['razon_social'], 0, 22)."..." :'No existe sesión'}}</b></label>
            </div>
        @endif
        @if(isset(Session::get('empresa')['razon_social']))
            <div>
                <img id="iconNavbar" src="/img/icono-notificaciones.png" class="icon-navbar">
                <a href="{{ url('/bandeja') }}" class="as-none" rel="Home">
                    <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Bandeja de
                            notificaciones</b></label>
                </a>
            </div>
        @endif
        <div class="navbar-underline">
            <img id="iconNavbar_usuario" src="/img/icono-usuario.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2"><b class="color-white">{{ ucwords(Auth::user()->nombre_completo) }}</b></label>
        </div>
        @if(Auth::user()->admin == 1 && isset(Auth::user()->autofacturador) && !Auth::user()->autofacturador)
            <div>
                <img id="iconNavbar" src="/img/icono-admin-hr.png" class="icon-navbar">
                <label class="text-left name-navbar name-navbar font-size-0-5em ml-2" data-toggle="collapse"
                       href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample"
                       onclick="arrowOpen('administracion')"><b>Administración de HR-System</b></label>
                <img src="/img/icono-flecha.png" id="administracion" value="0" class="navbar-arrow"
                     data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false"
                     aria-controls="collapseExample" onclick="arrowOpen('administracion')">
                <div class="collapse bg-color-yellow" id="collapseExample">
                    <div class="card card-body bg-color-yellow border-navbar ml-4 mr-4">
                            <a href="{{ route('sistema.usuarios.usuariosistema') }}" class="as-none list-navbar"
                               rel="Usuarios del sistema">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Usuarios del
                                    sistema</label>
                            </a>
                            <a href="{{ route('sistema.credencial.index') }}" class="as-none list-navbar"
                               rel="Usuarios del sistema">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Generar de Credenciales</label>
                            </a>

                            <a href="{{ route('usuarios.timbrado') }}" class="as-none list-navbar"
                               rel="Timbrado de Usuarios">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Usuarios
                                    timbrado</label>
                            </a>

                            <a href="{{ route('contratos.contratosHr') }}" class="as-none list-navbar"
                               rel="Contratos de HR-System">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Contratos de
                                    HR-System</label>
                            </a>

                            <a href="{{ route('conceptos.nominaconceptos') }}" class="as-none list-navbar"
                               rel="Conceptos de Nomina">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Conceptos de
                                    nómina</label>
                            </a>

                            <a href="{{ route('empresar.empresareceptora') }}" class="as-none list-navbar"
                               rel="Empresa Receptora">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Empresa
                                    Receptora/Clientes</label>
                            </a>

                            <a href="{{ route('empresae.empresaemisora') }}" class="as-none list-navbar"
                               rel="Empresa Emisora">
                                <img src="/img/icono-circulo.png" class="circle-navbar">
                                <label class="text-left font-size-0-5em font-weight-bold cursor-pointer">Empresas
                                    Pagadoras/Emisoras</label>
                            </a>
                    </div>
                </div>
            </div>

        @elseif(isset(Auth::user()->autofacturador) && Auth::user()->autofacturador)
            @include('includes.navbar_autofacturador')
        @endif

        @if (isset(Auth::user()->autofacturador) && !Auth::user()->autofacturador)
            <div>
                <img id="iconNavbar" src="/img/icono-cambiar-empresa.png" class="icon-navbar">
                <a href="{{ url('/home') }}" class="as-none" rel="Home">
                    <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Cambiar
                            empresa</b></label>
                </a>
            </div>

        @endif
        <div class="d-flex align-items-center navbar-underline">
            <img id="iconNavbar_cerrar_sesion" src="/img/icono-cerrar-sesion.png" class="icon-navbar">
            <a href="{{ route('logout.system') }}" class="as-none"><label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer label-white">
                <b class="color-white">Cerrar sesión</b></label>
            </a>
        </div>

        @include('includes.navbar_hrsystem')

        <div id="div_icon_terminos">
            <img id="iconNavbar_terminos" src="/img/icono-terminos.png" class="icon-navbar">
            <a href="{{ url('/terminos-y-condiciones') }}" class="as-none" rel="Términos y condiciones">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer label-white"><b class="color-white">Términos y
                        condiciones</b></label>
            </a>
        </div>
        <div id="div_icon_terminos">
            <img id="iconNavbar_aviso" src="/img/icono-aviso.png" class="icon-navbar">
            <a href="{{ url('/aviso-de-privacidad') }}" class="as-none" rel="Aviso de privacidad">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer label-white"><b class="color-white">Aviso de
                        privacidad</b></label>
            </a>
        </div>
        <br>
        <br>
        <br>
        <br>
    </div>
</div>
<div class="flotante">
    <a href="https://wa.me/message/C2RE2PKMD2QHK1" rel="WhatsApp" target="_blank">
        <img src="{{asset('img/icon-whatsapp.png')}}" width="50px">
    </a>
</div>
<script>
    document.getElementById("navbar").onmouseover = function () {
        mouseOver()
    };
    document.getElementById("navbar").onmouseout = function () {
        mouseOut()
    };

    function mouseOver() {
        openMenu()
    }

    function mouseOut() {
        closeMenu()
    }
</script>