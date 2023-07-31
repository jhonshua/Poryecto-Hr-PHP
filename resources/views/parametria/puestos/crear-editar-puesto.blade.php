<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php    
    $id_puesto = 0;
    $nombre_puesto = '';
    $jerarquia = 0;
    $dependencia = '';
    $actividades = '';
    $crearEditar = 'Crear';
    if(isset($idPuesto)){       
        foreach($puestos as $key => $i){
            if($idPuesto == $i->id){
                $crearEditar = 'Editar';
                $id_puesto = $i->id;
                $nombre_puesto = $i->puesto;
                $jerarquia = $i->jerarquia;
                $dependencia = $i->dependencia;
                $actividades = $i->actividades;
            }            
        }
    }
@endphp
<div class="container">
    <a href="{{route('parametria.puestos')}}" data-toggle="tooltip" title="Regresar">
        @include('includes.back')
    </a>
    <label class="font-size-1-5em mb-5 under-line"><strong>{{$crearEditar}} puestos</strong></label>
    <div class="mb-4">        
        <div class="article border d-flex justify-content-center">
            <div class="col-xs-12 col-md-8 col-lg-8">
                <form method="post" id="puestos_form" action="{{route('parametria.guardar.puesto')}}">
                    @csrf
                    <input type="hidden" name="id" value="{{$id_puesto}}">
                    <label for="">Nombre del puesto:</label>
                    <input type="text" name="puesto" id="puesto" value="{{$nombre_puesto}}" placeholder="Nombre del puesto" class="form-control input-style-custom mb-3" required>
                    {!! $errors->first('puesto','<p class="text-center text-danger">Ingrese el nombre del puesto</p>') !!}

                    <label for="">Jerarquía:</label>
                    <div class="mb-2">
                        <select name="jerarquia" id="jerarquia" class="form-control input-style-custom mb-2" required>
                            @for ($i = 0; $i <= 20; $i++ )
                                <option {{$jerarquia == $i ? 'selected' : ''}} value="{{$i}}">{{$i}}</option>
                            @endfor
                        </select>
                    </div>

                    <label for="">Dependencia:</label>
                    <!-- <img src="https://hrsystem/public/img/exclamation-mark-sign.png" width="11px" height="11px" alt="Áreas de apoyo" title="Áreas de apoyo"/> -->
                    <div class="mb-2">
                        <select name="dependencia" id="dependencia" class="form-control input-style-custom mb-2" style="width: 100%!important;" required>
                            <option value="">Selecciona un puesto</option>
                            <option value="0">Ninguna</option>
                            @foreach ($puestos as $puesto)
                                <option {{$dependencia == $puesto->id ? 'selected' : ''}}  value="{{$puesto->id}}">{{$puesto->puesto}}</option>
                            @endforeach
                        </select>
                    </div>                

                    <strong>Actividades del puesto:</strong>
                    <div class="d-flex mb-3">
                        <input type="text" name="" id="actividad" placeholder="Actividades del puesto" class="form-control input-style-custom mr-2">
                        <button class="btn bg-color-yellow text-white add" type="button"><strong>+</strong></button>
                    </div>                
                    <div class="card p-3 actividades">
                        @if(trim($actividades) != '')
                            <div class="d-flex actividad mb-2">
                                <input type="text" name="actividades[]" class="form-control input-style-custom mr-2" value="{{$actividades}}">
                                <button class="btn bg-color-yellow text-white del" type="button"><strong>-</strong></button>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 text-center">
                            <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                            <button type="submit" class="btn button-style">Guardar</button>
                        </div>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>
<script>
    let $actividad = `<div class="d-flex actividad mb-2">
            <input type="text" name="actividades[]" class="form-control input-style-custom mr-2" value="**value**">
            <button class="btn bg-color-yellow text-white del" type="button"><strong>-</strong></button>
        </div>`;
    $(function(){        
        // Agregar actividad fisica
        $('.btn.add').click(function(){
            if($('#actividad').val().trim() != ''){
                let act = $actividad;
                act = act.replace('**value**', $('#actividad').val().trim());
                $(".card.actividades").append(act);
                $('#actividad').val('');
            }
        });

        // Borrar actividad
        $('.actividades').on('click', '.btn.del', function(){
            $(this).parents('.actividad').remove();
        });

        $("#dependencia").select2({             
            tags: true,
            // width : 'resolve'
        });
        $("#jerarquia").select2({             
            tags: true,
            // width : 'resolve'
        });
    });
    
</script>

