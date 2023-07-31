@php 
$encabezadoRes = "";
@endphp

@if(count($resultados) > 0)
    @foreach($resultados as $resultado)
    <h4 class="font-weight-bold" >{{$resultado['cuestionario']['nombre']}}</h4>
    <table id="t{{$resultado['cuestionario']['id']}}" class="table table-striped table-sm" style="width:100%;">
        <thead class="thead-dark"><tr><th>Pregunta</th><th>Respuesta</th></tr></thead>
        <tbody>
        @foreach($resultado['respuestas'] as $respuesta)
            @if($resultado['cuestionario']['id'] == 1)
            <tr><td>{{$respuesta->pregunta}} </td><td> {{$respuestas[0][$respuesta->pivot->valor]}}</td></tr>
            @else
                <tr><td>{{$respuesta->pregunta}} </td><td> {{$respuestas[$respuesta->tipo_respuesta][$respuesta->pivot->valor]}}</td></tr>
            @endif
            
        @endforeach
        </tbody>
    </table><br/>
    @endforeach

    @if(count($resultado['totales']) > 0)
        <h3>Resultados</h3>
        <table id="tres{{$resultado['cuestionario']['id']}}" class="table table-striped table-sm" style="width:100%;">
            <thead class="thead-dark"><tr><th></th><th>Total</th><th>Ponderador</th></tr></thead>
            <tbody>
                <tr id="tr0">
                    <td>Total</td><td>{{$resultado['cuestionario_trabajador']['total_cuestionario']}}</td>
                    @foreach($ponderadorTotal as $pon)
                        @if($resultado['cuestionario_trabajador']['total_cuestionario'] >= $pon[0]  && $resultado['cuestionario_trabajador']['total_cuestionario'] < $pon[1])
                            <td style="background:{{$pon[2]}};text-align:center"><strong>{{ $pon[3] }}</strong></td>
                        @endif
                    @endforeach
                </tr>
                @foreach($resultado['totales'] as $tot)
                    @if($encabezadoRes != $tot->clase)
                        @php $txt = ($tot->clase == "categoria")? "Categoría":"Dominio";     @endphp
                        <tr class="thead-dark text-center"><th colspan="3">{{$txt}}</th></tr>
                        @php $encabezadoRes =  $tot->clase; @endphp
                    @endif   
                    <tr id="tr{{$tot->id}}"><td>{{$tot->dato}}</td><td>{{$tot->pivot->total}}</td>
                    @foreach($ponderadorCyD[$tot->id] as $pon)
                        @if($tot->pivot->total >= $pon[0]  && $tot->pivot->total < $pon[1])
                        <td class="text-center" style="background:{{$pon[2]}}"><strong>{{$pon[3]}}</strong></td>
                        @endif
                    @endforeach
                    </tr>
                @endforeach
        </tbody></table><br/>
    @endif
@endif