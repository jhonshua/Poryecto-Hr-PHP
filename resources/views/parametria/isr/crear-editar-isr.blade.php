<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php    
    $tipo_tabla = '';
    $id_impuesto = 0;
    $limite_superior = 0;
    $limite_inferior = 0;
    $cuota_fija = 0;
    $porcentaje = 0;
    $crearEditar = 'Crear';
    if(isset($datos_impuestos)){       
        foreach($datos_impuestos as $key => $i){
            $crearEditar = 'Editar';
            $id_impuesto = $i->id;
            $tipo_tabla = $i->tipo_tabla;
            $limite_inferior = $i->limite_inferior;
            $limite_superior = $i->limite_superior;
            $cuota_fija = $i->cuota_fija;
            $porcentaje = $i->porcentaje;
        }
    }
@endphp
<div class="container">
    <a href="{{route('parametria.isr')}}" data-toggle="tooltip" title="Regresar">
        @include('includes.back')
    </a>
    <label class="font-size-1-5em mb-5 under-line"><strong>{{$crearEditar}} impuesto ISR</strong></label>
    <div class="mb-4">        
        <div class="article border d-flex justify-content-center">
            <div class="col-xs-12 col-md-8 col-lg-8">
                <form method="post" id="isr_form" action="{{route('parametria.guardar.isr')}}">
                    @csrf
                    <input type="hidden" name="id" value="{{$id_impuesto}}">
                    <label for="">Tipo Tabla:</label>
                    <select name="tipo_tabla" id="tipo_tabla" class="form-control input-style-custom mb-2" >
                        <option value="" >SELECCIONE</option>
                        <option {{$tipo_tabla == 'SEMANAL' ? 'selected' : ''}} value="SEMANAL">SEMANAL</option>
                        <option {{$tipo_tabla == 'QUINCENAL' ? 'selected' : ''}} value="QUINCENAL">QUINCENAL</option>
                        <option {{$tipo_tabla == 'ANUAL' ? 'selected' : ''}} value="ANUAL">ANUAL</option>
                    </select>
                    {!! $errors->first('tipo_tabla','<p class="text-center text-danger">Seleccione tipo tabla</p>') !!}

                    <label for="">Limite Inferior:</label>
                    <input type="number" name="limite_inferior" id="limite_inferior" value="{{$limite_inferior}}" class="form-control input-style-custom mb-3 " placeholder="Limite Inferior" step="any" required min="0">
                    {!! $errors->first('limite_inferior','<p class="text-center text-danger">Ingrese un valor mayor igual a cero</p>') !!}

                    <label for="">Limite Superior:</label>
                    <input type="number" name="limite_superior" id="limite_superior" value="{{$limite_superior}}" class="form-control input-style-custom mb-3" placeholder="Limite Superior" step="any" required min="0">
                    {!! $errors->first('limite_superior','<p class="text-center text-danger">Ingrese un valor mayor igual a cero</p>') !!}

                    <label for="">Cuota Fija:</label>
                    <input type="number" name="cuota_fija" id="cuota_fija" value="{{$cuota_fija}}" class="form-control input-style-custom mb-3" placeholder="Cuota Fija" step="any" required min="0">
                    {!! $errors->first('cuota_fija','<p class="text-center text-danger">Ingrese un valor mayor igual a cero</p>') !!}

                    <label for="">Porcentaje:</label> 
                    <input type="number" name="porcentaje" id="porcentaje" value="{{$porcentaje}}" class="form-control input-style-custom mb-3" placeholder="Porcentaje" step="any" required min="0">
                    {!! $errors->first('porcentaje','<p class="text-center text-danger">Ingrese un valor mayor igual a cero</p>') !!}
                    
                    <div class="row justify-content-center">
                        <div class="row justify-content-center col-xs-12 col-md-12 col-lg-2">                        
                            <button type="submit" class="button-style btn-block guardar">Guardar</button>
                        </div> 
                    </div>                              
                
                </form>
            </div>
        </div>
    </div>
</div>


