<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body class="bg-color-yellow">
<div class="login bg-color-yellow">
    <div class="article bg-color-yellow">
        <div class="row">
            <div class="col-lg-12 bg-white border box-shadow">
                <br>
                <br>
                <img src="{{asset("/img/logo.png")}}" class="w-px-250 center" alt="HRSystem" title="HRSystem">
                <br>
                <br>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input id="email" type="email" class="input-style center @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <br>
                    <br>
                    <input id="password" type="password" class="input-style center mt-4 @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="nuevo password">
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <br>
                    <input id="password-confirm" type="password" class="input-style center" name="password_confirmation" required autocomplete="new-password" placeholder="confirma tu password">
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

