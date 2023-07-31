<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
    @include('includes.navbar')
    <div class="container">
        <div
            @if(empty(session()->get('bienvenido-titulo')))
                class="home-screen-welcome"
                {{ session()->put('bienvenido-titulo', 1) }}
            @else
                class="d-none"
            @endif
        >
            <h1 class="text-center display-1">Bienvenido</h1>
        </div>
        <div
            @if(empty(session()->get('bienvenido-contenido')))
            class="home-screen-content"
            {{ session()->put('bienvenido-contenido', 1) }}
            @else
            class="d-block"
            @endif
        >
            <img src="{{asset("/img/logo.png")}}" class="center w-px-250 mb-4" alt="HR-System">
            <br>
            <div class="article border">
                <form action="{{ route('empresa.cambiar') }}" method="post">
                    @csrf
                    <p class="text-center">
                        En este apartado podrás elegir la empresa en la que trabajarás y realizaras tus operaciones, cada empresa o proyecto son independientes por lo que las conﬁguraciones deberan ser aplicadas para cada una de ellas.
                    </p>
                    <p class="text-center font-size-1-5em">
                        Selecciona la empresa que quieres gestionar:
                    </p>
                    <div class="col-md-9 offset-3" >
                        <select name="enterprise" class="center input-style form-control select-clase  full-size text-left" style="width: 60%" >
                            <option>Selecciona una empresa...</option>
                            @foreach($enterprises as $enterprise)
                                <option value="{{ $enterprise->id }}">{{ strtoupper($enterprise->razon_social) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <br>
                    <input type="submit" class="center button-style" value="Enviar">
                </form>
            </div>
        </div>
    </div>
    @include('includes.footer')
    <script>
        $(function() {
            $('.select-clase').select2();
        });
    </script>
</body>
</html>
