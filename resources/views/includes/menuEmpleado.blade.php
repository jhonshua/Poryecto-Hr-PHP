<div id="navbarBack" class="navigation-back" onclick="closeMenu()"></div>
<div id="navbar" class="navigation">
    <i id="iconOpenMenu" class="fas fa-angle-right right" onclick="openMenu()"></i>
    <i id="iconCloseMenu" class="fas fa-times right display-none" onclick="closeMenu()"></i>
    <br>
    <img src="{{asset("/img/logo-navbar.png")}}" class="center top w-px-200 mb-4 navbar-underline" alt="HRSystem"
         title="HRSystem">
    <div class="navigation-scroll">

        <p id="img-empleado">
            @if(!empty(Session::get('empleado')['file_fotografia']) && file_exists('storage/repositorio/'.Session::get('empresa')['id'].'/'.Session::get('empleado')['id'].'/'.Session::get('empleado')['file_fotografia']))
                <img src="{{asset('storage/repositorio/'.Session::get('empresa')['id'].'/'.Session::get('empleado')['id'].'/'.Session::get('empleado')['file_fotografia'])}}"
                     class="mt-3 rounded-circle img-fluid" alt="{{Session::get('empleado')['file_fotografia']}}"
                     style="max-width: 45%;margin-left:50px;">
            @else
                <img src="{{asset('img/avatar.png')}}" class="rounded-circle img-flui mt-3" alt="sin imagen"
                     style="max-width: 45%;margin-left:50px;">
            @endif

        </p>

        <div>
            <img id="iconNavbar" src="/img/icono-notificaciones.png" class="icon-navbar">
            <a href="{{ route('empleado.inicio') }}" class="as-none" rel="Home">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Bandeja de
                        notificaciones</b></label>
            </a>
        </div>
       
        <div class="navbar-underline">
            <img id="iconNavbar" src="/img/icono-usuario.png" class="icon-navbar">
            <label class="text-left name-navbar font-size-0-5em ml-2"><b>{{Session::get('empleado')['nombre']}} {{Session::get('empleado')['apaterno']}}</b></label>
        </div>

        @if(Session::get('empresa')['id'] != '236')
        <div>
            <img id="iconNavbar" src="/img/icono-usuario.png" class="icon-navbar">
            <a href="{{route('empleado.miPerfil')}}" class="as-none">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Mi perfil</b></label>
            </a>
        </div>
       
        <div>
            <img id="iconNavbar" src="/img/icono-orden-compra.png" class="icon-navbar">
            <a href="{{route('empleado.solicitudes.inicio')}}" class="as-none">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Solicitudes de
                        beneficios</b></label>
            </a>
        </div>
       
        <div>
            <img id="iconNavbar" src="/img/icono-orden-compra.png" class="icon-navbar">
            <a href="{{route('empleado.recibosss')}}" class="as-none">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Recibos de
                        nómina</b></label>
            </a>
        </div>
      
        <div>
            <img id="iconNavbar" src="/img/icono-terminos.png" class="icon-navbar">
            <a href="{{route('empleado.encuesta.inicio')}}" class="as-none">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Formularios</b></label>
            </a>
        </div>
        @endif
        <div>
            <img id="iconNavbar" src="/img/icono-terminos.png" class="icon-navbar">
            <a href="{{route('empleado.norma')}}" class="as-none">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Norma 035</b></label>
            </a>
        </div>

        <div>
            <img id="iconNavbar" src="/img/icono-aviso.png" class="icon-navbar">
            <a href="{{route('empleado.avisoprivacidadempleado')}}" class="as-none">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Aviso de
                        Privacidad</b></label>
            </a>
        </div>
        <div class="d-flex align-items-center navbar-underline">
            <img id="iconNavbar" src="/img/icono-cerrar-sesion.png" class="icon-navbar">
            <form action="{{ route('empleado.logout') }}" method="post">
                @csrf
                <input type="submit" class="navbar-logout ml-2" value="Cerrar sesión">
            </form>
        </div>

    </div>
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

@push('css')
    <style>
        .img-fluid {
            max-width: 45% !important;
            height: auto !important;
        }

        #img-empleado img {
            margin-left: 50px;
        }
    </style>
@endpush
