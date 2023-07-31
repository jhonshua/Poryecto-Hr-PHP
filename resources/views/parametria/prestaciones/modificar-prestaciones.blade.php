<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
            <a href="{{route('parametria.prestaciones.listado',['id'=>$data['id_categoria'] ])}}" data-toggle="tooltip" title="Regresar" ref="Alta prestación">
                @include('includes.back')
            </a>
            <label class="font-size-1-5em mb-5 under-line font-weight-bold">Parametría / Modificar /Tipo de prestaciones </label>
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
                <div class="col-md-8  offset-md-2" >
                    <form action="{{route('parametria.prestaciones.insertarPrestacion')}}" method="POST" id="form" >
                        @csrf
                        <div class="row">
                    
                            <div class="col-md-12 mt-4">
                                <label >Antiguedad </label>
                                <select name="antiguedad" class=" form-control  select-clase" style="width:100% !important"  >
                                    @for ($i = 0; $i < 51; $i++)
                                        <option value="{{$i}}" {{ ($data['antiguedad'] ==$i    )? 'selected':''  }} >{{$i}} años</option>
                                    @endfor
                                </select>
                                {!! $errors->first('antiguedad','<p class="text-danger text-center mt-3">Error: El campo antiguedad es requerido</p>') !!} 
                            </div>
                            <div class="col-md-12 mt-4">
                                <label >Vacaciones </label>
                                <select name="vacaciones" class=" form-control  select-clase" style="width:100% !important"  >
                                    @for ($i = 0; $i < 20; $i++)
                                        <option value="{{$i}}" {{ ($data['vacaciones'] ==$i    )? 'selected':''  }} >{{$i}} días</option>
                                    @endfor
                                </select>
                                {!! $errors->first('vacaciones','<p class="text-danger text-center mt-3">Error: El campo vacaciones es requerido</p>') !!} 
                            </div>
                            <div class="col-md-12 mt-4">
                                <label >Prima vacacional </label>
                                <select name="prima_vacacional" class=" form-control  select-clase" style="width:100% !important"  >
                                    @for ($i = 0; $i < 51; $i++)
                                        <option value="{{$i}}" {{ ($data['prima_vacacional'] ==$i    )? 'selected':''  }} >{{$i}}%</option>
                                    @endfor
                                </select>
                                {!! $errors->first('prima_vacacional','<p class="text-danger text-center mt-3">Error: El campo prima vacacional es requerido</p>') !!} 
                            </div>
                            <div class="col-md-12 mt-4">
                                <label >Aguinaldo </label>
                                <select name="aguinaldo" class=" form-control  select-clase" style="width:100% !important"  >
                                    @for ($i = 0; $i < 46; $i++)
                                        <option value="{{$i}}" {{ ($data['prima_vacacional'] ==$i    )? 'selected':''  }} >{{$i}} días</option>
                                    @endfor
                                </select>
                                {!! $errors->first('aguinaldo','<p class="text-danger text-center mt-3">Error: El campo aguinaldo es requerido</p>') !!} 
                            </div>
                            <div class="col-md-12 ">
                                <div class="font-size-1-5em under-line-custom mt-4"></div>
                            </div>
                            <div class="col-md-12 mt-4">
                                <label >Bono aguinaldo </label>
                                <select name="bono_aguinaldo" class=" form-control  select-clase" style="width:100% !important"  >
                                    @for ($i = 0; $i < 46; $i++)
                                        <option value="{{$i}}" {{ ($data['bono_aguinaldo'] ==$i    )? 'selected':''  }}  >{{$i}} días</option>
                                    @endfor
                                </select>
                                {!! $errors->first('bono_aguinaldo','<p class="text-danger text-center mt-3">Error: El campo bono de aguinaldo es requerido</p>') !!} 
                            </div>
                            <div class="col-md-12 mt-4">
                                <label>Bono vacaciones </label>
                                <select name="bono_vacaciones" class=" form-control  select-clase" style="width:100% !important"  >
                                    @for ($i = 0; $i < 20; $i++)
                                        <option value="{{$i}}" {{ ($data['bono_vacaciones'] ==$i    )? 'selected':''  }}>{{$i}} días</option>
                                    @endfor
                                </select>
                                {!! $errors->first('bono_vacaciones','<p class="text-danger text-center mt-3">Error: El campo bono de vacaciones es requerido</p>') !!} 
                            </div>
                            <div class="col-md-12 mt-4">
                                <label>Bono prima vacacional </label>
                                <select name="bono_prima_vacacional" class=" form-control  select-clase" style="width:100% !important"  >
                                    @for ($i = 0; $i < 51; $i++)
                                        <option value="{{$i}}" {{ ($data['bono_prima_vacacional'] ==$i    )? 'selected':''  }}>{{$i}}%</option>
                                    @endfor
                                </select>
                                {!! $errors->first('bono_prima_vacacional','<p class="text-danger text-center mt-3">Error: El campo bono de prima vacacional es requerido</p>') !!} 
                            </div> 
                            <input type="hidden" name="id_categoria" value="{{$data['id_categoria']}}">    
                            <input type="hidden" name="id" value="{{$data['id']}}">      
                        </div>
                        <br>
                    
                        <button type="submit" class="center button-style w-10 guardar ">Guardar</button>
                    </form>
                </div>
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
