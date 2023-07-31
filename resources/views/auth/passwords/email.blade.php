<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body class="bg-color-yellow">
<div class="login bg-color-yellow">
    <div class="article bg-color-yellow">
        <div class="row">
            <div class="col-lg-12 bg-white border">
                <br>
                <br>
                <img src="{{asset("/img/logo.png")}}" class="w-px-250 center" alt="HRSystem" title="HRSystem">
                <br>
                <br>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <p class="text-center text-gray">Ingresa tu dirección de correo electrónico, con la cual inicias sesión en HR-System</p>
                    <input id="email" type="email" class="input-style center  @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="e mail" autofocus>
                    <br>
                    <button type="submit" class="button-style center">
                        {{ __('Enviar') }}
                    </button>
                    <br>
                    <br>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
</body>
</html>
