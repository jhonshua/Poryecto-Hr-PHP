<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
            <a href="{{ url('/prestaciones-inicio') }}" data-toggle="tooltip" title="Regresar" >
                @include('includes.back')
            </a>
            <label class="font-size-1-5em mb-5 under-line font-weight-bold">Parametría / Tipo de prestaciones / Crear prestación </label>
            @if(session()->has('success'))
                <div class="row">
                    <div class="alert alert-success" style="width: 100%;" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>Notificación: </strong>
                        {{ session()->get('success') }}
                    </div>
                </div>
            @elseif(session()->has('danger'))
                <div class="row">
                    <div class="alert alert-danger" style="width: 100%;" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>Notificación: </strong>
                        {{ session()->get('danger') }}
                    </div>
                </div>
            @endif
            <div class="article border general-div" id="general-div" >
                <form action="{{route('parametria.prestaciones.insertar')}}" method="POST" id="form" >
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                          <input type="text" class="form-control input-style-custom" name="nombre" value="{{old('nombre')}}" placeholder="Ingrese un nombre para la prestación" >
                          {!! $errors->first('nombre','<p class="text-danger text-center mt-3">Error: El campo nombre es requerido</p>') !!} 
                        </div>
                        <div class="col-md-6">
                            <select name="tipo_clase" class=" form-control  select-clase" style="width:100% !important"  >
                                <option value="" disabled selected >Seleccione una clase</option>
                                @foreach ($clases as $clase)
                                    <option value="{{$clase->id}}" {{($clase->id == old('tipo_clase')) ? 'selected' : ''}} >{{$clase->tipo_clase}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('tipo_clase','<p class="text-danger text-center mt-3">Error: El campo clase es requerido</p>') !!} 
                        </div>
                    </div>
                    <br>
                    <br>
                    <button type="submit" class="center button-style  w-10 guardar ">Guardar</button>
                </form>
            </div>
        </div>
        <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
        <script>
            $(function() {
                $('.select-clase').select2();
            });
        </script>
    </body>
</html>
