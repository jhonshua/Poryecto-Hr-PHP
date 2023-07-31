<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body class="" style="background-color: #67140F;">
    <div class="login" style="background-color: #67140F;">
        <div class="article" style="background-color: #67140F;">
            <div class="row">
                <div class="col-lg-6 p-5 gone">
                    <h1 class="text-white font-size-2-5em line-height-50-px">
                        Bienvenid@s a <b>El QUERETANO</b>
                        <br>
                        {{-- <b></b> --}}
                        {{-- <br> --}}
                         Por favor introduce tus datos solicitados para continuar.
                    </h1>
                </div>
                <div class="col-lg-6 bg-white border">
                    <br>
                    <br>
                    <img src="{{asset("/img/inicio-elqueretano.png")}}" class="w-px-250 center" alt="HR-System" title="HR-System">
                    <br>
                    <br>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        @error('email')
                            <p class="text-center alert-danger m-2">
                                {{ $message }}
                            </p>
                        @enderror
                        @error('password')
                            <p class="text-center alert-danger m-2">
                                {{ $message }}
                            </p>
                        @enderror
                        <input id="email" type="email" class="input-style-login center @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="e mail" autofocus>
                        <br>
                        <input id="password" type="password" class="input-style-login center @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="contraseña">
                        <br>
                        <!--
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        -->
                        <style type="text/css">
                            ::-webkit-scrollbar-thumb {
                                background-color: #67140F;
                                border-radius: 20px;
                                border: 3px solid white;
                                width: 10px;
                            }
                        </style>
                        <button type="submit" class="center button-style" style="background-color: #67140F;">
                            {{ __('Ingresar') }}
                        </button>
                        {{-- <br> --}}
                        <br>
                        @if (Route::has('password.request'))
                            <a class="center btn btn-link w-px-250" href="{{ route('password.request') }}" rel="¿Olvidaste tu contraseña?">
                                <label>{{ __('¿Olvidaste tu contraseña?') }}</label>
                            </a>
                        @endif
                        <br>
                        <br>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
