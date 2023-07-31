<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body class="bg-color-yellow">
<div class="login bg-color-yellow">
    <div class="article bg-color-yellow">
        <div class="row">
            <div class="col-lg-6 p-5 gone">
                <h1 class="text-dark font-size-2-5em line-height-50-px">
                    <b>Somos el salto tecnológico</b>
                    <br>
                    {{-- <b></b> --}}
                    {{-- <br> --}}
                    que tu empresa necesita.
                </h1>
                <h2 class="text-white font-size-2-2em">
                    Calcula tu nómina y gestiona
                    <br>
                    el capital humano de manera
                    <br>
                    <b>rápida, eficaz y simple.</b>
                </h2>

                <h1 class="text-dark font-size-2-5em line-height-50-px">
                    <b>Somos el primer CDO en México</b>
                    <br>
                    {{-- <b></b> --}}
                    {{-- <br> --}}
                </h1>

            </div>
            <div class="col-lg-6 bg-white border">
                <br>
                <br>
                <img src="{{asset("/img/logo.png")}}" class="w-px-250 center" alt="HR-System" title="HR-System">
                <center>Empleados</center>
                <center><br>{{session('mensaje')}}</center>
                @foreach ($errors as $error)
                    <li>{{$error}}</li>
                @endforeach
                <br>
                <br>
                <form method="POST" action="{{ route('empleado.login') }}" id="formregistro">
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
                    <input id="email" type="email" class="username-input  input-style-login center @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="e mail" autofocus>
                    <br>
                    <input id="password_input" type="password" class="password-input input-style-login center @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="contraseña">
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
                    <button id="login-button" name="login" type="submit" class="center button-style">
                        {{ __('Ingresar') }}
                    </button>
                    {{-- <br> --}}
                    <br>
                    @if (Route::has('password.request'))
                        <a class="center btn btn-link w-px-250 olvidecontraseña" href="#" rel="¿Olvidaste tu contraseña?">
                            <label>{{ __('¿Olvidaste tu contraseña?') }}</label>
                        </a>
                    @endif
                    <br>
                    <br>
                </form>

                <form action="" method="post" class="text-center" id="forgotPassForm" style="display: none;">
                    @csrf
                    <div class="login-form" style="height:inherit">
                        <div class="message" style="margin-bottom:20px; text-align:center;"> Recuperar contraseña</div>
                        <div class="username-row row">
                            <input id="email" type="email" class="username-input  input-style-login center @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="e mail" autofocus>
                        </div>
                        <center style="margin-bottom:40px;">
                            <div class="call-to-action" >
                                <button id="resetPassword" type="button" class="center button-style" >Enviar</button>
                                <a href="#" class="showLoginForm btn-link"><small>Hacer login</small></a>
                            </div>
                        </center>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


<script>
    $('.olvidecontraseña').click(function(){
        $('#formregistro').fadeOut('fast', function(){
            $('#forgotPassForm').fadeIn();
        })
    });
    $('.showLoginForm').click(function(){
        $('#forgotPassForm').fadeOut('fast', function(){
            $('#formregistro').fadeIn();
        })
    });

    $('#resetPassword').click(function(){
        $(this).text('Espere...');
        $(this).prop('disabled', true);
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'email' : $('#forgotPassForm #email').val(),
            '_token': '{{ csrf_token() }}'
        }

        var url = "{{route('empleado.recuperar')}}";
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {

                $("#forgotPassForm .message").fadeOut('fast', function(){
                    $("#forgotPassForm .message").text(response.mensaje);
                    $("#forgotPassForm .message").fadeIn();
                })
                $('#resetPassword').text('Enviar');
                $('#resetPassword').prop('disabled', false);
            }
        });
    });
</script>

</body>
</html>
