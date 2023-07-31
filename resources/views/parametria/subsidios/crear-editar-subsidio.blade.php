<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php    
    $tipo_tabla = '';
    $id_subsidio = 0;
    $ingreso_desde = 0;
    $ingreso_hasta = 0;
    $subsidio = 0;
    $crearEditar = 'Crear';
    if(isset($datos_subsidios)){       
        foreach($datos_subsidios as $key => $i){
            $crearEditar = 'Editar';
            $id_subsidio = $i->id;
            $tipo_tabla = $i->tipo_tabla;
            $ingreso_desde = $i->ingreso_desde;
            $ingreso_hasta = $i->ingreso_hasta;
            $subsidio = $i->subsidio;           
        }
    }
@endphp
<div class="container">
    <a href="{{route('parametria.subsidio')}}" data-toggle="tooltip" title="Regresar">
        @include('includes.back')
    </a>
    <label class="font-size-1-5em mb-5 under-line"><strong>{{$crearEditar}} Subsidio</strong></label>
    <div class="mb-4">
        <div class="article border d-flex justify-content-center">
            <div class="col-xs-12 col-md-8 col-lg-8">
                <form method="post" id="isr_form" action="{{route('parametria.guardar.subsidio')}}">
                    @csrf
                    <input type="hidden" name="id" value="{{$id_subsidio}}">
                    <label for="">Tipo Tabla:</label>
                    <select name="tipo_tabla" id="tipo_tabla" class="form-control input-style-custom mb-2" >
                        <option value="" >SELECCIONE</option>
                        <option {{$tipo_tabla == 'SEMANAL' ? 'selected' : ''}} value="SEMANAL">SEMANAL</option>
                        <option {{$tipo_tabla == 'QUINCENAL' ? 'selected' : ''}} value="QUINCENAL">QUINCENAL</option>
                        <option {{$tipo_tabla == 'ANUAL' ? 'selected' : ''}} value="ANUAL">ANUAL</option>
                        <option {{$tipo_tabla == 'MENSUAL' ? 'selected' : ''}} value="MENSUAL">MENSUAL</option>
                        <option {{$tipo_tabla == 'DIARIA' ? 'selected' : ''}} value="DIARIA">DIARIA</option>
                        <option {{$tipo_tabla == 'DECENAL' ? 'selected' : ''}} value="DECENAL">DECENAL</option>
                    </select>
                    {!! $errors->first('tipo_tabla','<p class="text-center text-danger">Seleccione tipo tabla</p>') !!}

                    <label for="">Para ingresos de:</label>
                    <input type="number" name="ingreso_desde" id="ingreso_desde" value="{{$ingreso_desde}}" placeholder="Para ingresos de" class="form-control input-style-custom mb-3" step="any" required min="0">
                    {!! $errors->first('ingreso_desde','<p class="text-center text-danger">Ingrese un valor mayor igual a cero</p>') !!}

                    <label for="">Hasta ingresos de:</label>
                    <input type="number" name="ingreso_hasta" id="ingreso_hasta" value="{{$ingreso_hasta}}" placeholder="Hasta ingresos de" class="form-control input-style-custom mb-3"  step="any" required min="0">
                    {!! $errors->first('ingreso_hasta','<p class="text-center text-danger">Ingrese un valor mayor igual a cero</p>') !!}

                    <label for="">SUBSIDIO:</label>
                    <input type="number" name="subsidio" id="subsidio" value="{{$subsidio}}" placeholder="Subsidio" class="form-control input-style-custom mb-3" step="any" required min="0">
                    {!! $errors->first('subsidio','<p class="text-center text-danger">Ingrese un valor mayor igual a cero</p>') !!}            
                    
                    <div class="row justify-content-center"> 
                        <div class="row justify-content-center col-xs-12 col-md-12 col-lg-2">                        
                            <button type="submit" class="button-style btn-block">Guardar</button>
                        </div> 
                    </div>                              
                
                </form>
            </div>
        </div>
    </div>
</div>


