@if (isset(Auth::user()->autofacturador) && Auth::user()->autofacturador && isset(Auth::user()->admin) && Auth::user()->admin)

    @if(Auth::user()->timbrar)
        <div>
            <img id="iconNavbar" src="/img/icono-usuario.png" class="icon-navbar">
            <a href="{{ route('sistema.usuarios.usuariosistema') }}" class="as-none" rel="Usuarios del sistema">
                <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Usuarios del
                        sistema</b></label>
            </a>
        </div>
    @endif

    <div>
        <img id="iconNavbar" src="/img/icono-terminos.png" class="icon-navbar">
        <a href="{{route('autofacturador.administracion.recursosVew')}}" class="as-none" rel="Recursos">
            <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Recursos</b></label>
        </a>
    </div>


    <div>
        <img id="iconNavbar" src="/img/icono-orden-compra.png" class="icon-navbar">
        <a href="{{route('autofacturador.administracion.index')}}" class="as-none" rel="Ordenes de Compra">
            <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Ordenes de compra</b></label>
        </a>
    </div>



    <div>
        <img id="iconNavbar" src="/img/icono-editar-black.png" class="icon-navbar">
        <a href="{{route('autofacturador.index')}}" class="as-none" rel="Editar/Crear OC">
            <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer"><b>Editar/Crear OC</b></label>
        </a>
    </div>
@else

    <div>
        <img id="iconNavbar_orden_compra" src="/img/icono-orden-compra.png" class="icon-navbar">
        <a href="{{route('autofacturador.index')}}" class="as-none" rel="Sección de data inicial de la empresa">
            <label class="text-left name-navbar font-size-0-5em ml-2 cursor-pointer label-white"><b class="color-white">Ordenes de compra</b></label>
        </a>
    </div>
@endif


