
<form id="cuestionario{{$cuestionario_trabajador->id}}" action="#" role="form" class="form"> 
    @csrf
    <input type="hidden" name="cuestionario_trabajador" id="cuestionario_trabajador" value="{{$cuestionario_trabajador->id}}"/>
    @php  $contadorbloque =0; $numeroPregunta = 1; @endphp
        <img src="{{asset('img/spinner.gif')}}" style='width:50px;float: right;display:none;' id="cargando"/>
        @foreach ($bloques as $b)
            
            <h3>{{$b->nombre}} </h3>
            <fieldset>
                
                <legend>{{$b->nombre}}</legend>
                
                @isset($b->descripcion)
                    <h5>{{$b->descripcion}}</h5>
                @endisset
                <div class="condicional{{$b->id}}" style="padding:0 1% 3% 0;text-align:center;font-weight:800;font-size:1rem;display:none;"></div>
                @isset($b->instrucciones)
                    <h6>{{$b->instrucciones}}</h6>
                @endisset
               
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pregunta</th>
                            @if($tipo_cuestionario == 1)
                                <th>SI</th>
                                <th>NO</th>
                            @else
                                <th>Siempre</th>
                                <th>Casi siempre</th>
                                <th>Algunas veces</th>
                                <th>Casi nunca</th>
                                <th>Nunca</th>
                            @endif
                        </tr>
                    </thead>
                   
                    @foreach ($b->preguntas as $p)
                        <tr>
                            @if($p->pivot->condicional == 1)
                                @push('scripts')
                                    <script>
                                        $(".condicional{{$b->id}}").css("display","block").append('{{$numeroPregunta}} <label for="{{$p->id}}">{{$p->pregunta}}</label> &nbsp;&nbsp;Si&nbsp;<input class="{{$p->id}} requerido p{{$contadorbloque}} condicional" data-bloque="{{$contadorbloque}}" type="radio" name="{{$p->id}}" data-pregunta="{{$p->id}}" value="1">&nbsp;&nbsp;No&nbsp;<input type="radio" name="{{$p->id}}" class="{{$p->id}} p{{$contadorbloque}} condicional" data-bloque="{{$contadorbloque}}" data-pregunta="{{$p->id}}" value="0">');
                                    </script>
                                @endpush
                            @else
                                <td> <label for="{{$p->id}}">{{$numeroPregunta}} {{$p->pregunta}}</label></td>
                                @if($tipo_cuestionario == 1)
                                    <td><input class="{{$p->id}} requerido p{{$contadorbloque}}" type="radio" name="{{$p->id}}" data-pregunta="{{$p->id}}" value="1"></td>
                                    <td><input class="{{$p->id}} requerido p{{$contadorbloque}}" type="radio" name="{{$p->id}}" data-pregunta="{{$p->id}}" value="0"></td>
                                @else
                                    @if($p->tipo_respuesta == 1)
                                        @for($i = 0; $i < 5; $i++)
                                            <td><input class="{{$p->id}} requerido p{{$contadorbloque}} reqpre{{$contadorbloque}}" type="radio" name="{{$p->id}}" data-pregunta="{{$p->id}}" value="{{$i}}"></td>
                                        @endfor
                                    @else
                                        @for($i = 4; $i >= 0; $i--)
                                            <td><input class="{{$p->id}} requerido p{{$contadorbloque}} reqpre{{$contadorbloque}}" type="radio" name="{{$p->id}}" data-pregunta="{{$p->id}}" value="{{$i}}"></td>
                                        @endfor
                                    @endif
                                    
                                @endif
                            @endif
                        </tr>
                        @php  $numeroPregunta++; @endphp
                @endforeach
            </table>
            </fieldset>
            
            @php $contadorbloque++; @endphp
        @endforeach
        
</form>
