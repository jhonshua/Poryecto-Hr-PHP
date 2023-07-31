@php
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=Poliza.xls"); //Indica el nombre del archivo resultante
header("Pragma: no-cache");
header("Expires: 0");    
@endphp

<table>
  <tr>
  </tr>
  <tr>
  </tr>
  <tr>
    <td>Dr</td>
    <td>{{ $numero_poliza }}</td>
    <td>{{ $titulo_nomina }}</td>
    <td>{{ $dias_poliza }}</td>
  </tr>
  @foreach ($array_conceptos as $concepto)
  <tr>
    <td>

    </td>
    <td>
     {{ $concepto['cuenta_contable'] }}
    </td>
    <td>
     0
    </td>
    <td>
     {{ $concepto['nombre_concepto'] }} 
    </td>
    <td>
     1
    </td>
    <td>
      {{ $concepto['debe'] }} 
    </td>
    <td>
      {{ $concepto['haber'] }} 
    </td>
  </tr>
  @endforeach
  @foreach ($array_subsidio as $s)
  <tr>
    <td>

    </td>
    <td>
     {{ $s['cuenta_subsidio'] }}
    </td>
    <td>
     0
    </td>
    <td>
      {{ $s['nombre_cuenta'] }}
    </td>
    <td>
     1
    </td>
    <td>
     {{ $s['debe'] }}
    </td>
    <td>
     {{ $s['haber'] }}
    </td>
  </tr>
  @endforeach
  <tr>
    <td></td>
    <td>{{$cuentaBancaria}}</td>
    <td>0</td>
    <td>{{$empresa}}</td>
    <td>1</td>
    <td>{{$total_debe}}</td>
    <td>{{$total_haber}}</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_imms}}</td>
    <td>0</td>
    <td>{{$nombre_imss}}</td>
    <td>1</td>
    <td>{{$valor_imms}}</td>
    <td>0</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_imms2}}</td>
    <td>0</td>
    <td>{{$nombre_imss}}</td>
    <td>1</td>
    <td>0</td>
    <td>{{$valor_imms}}</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_rcv}}</td>
    <td>0</td>
    <td>{{$nombre_rcv}}</td>
    <td>1</td>
    <td>{{$valor_rcv}}</td>
    <td>0</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_rcv2}}</td>
    <td>0</td>
    <td>{{$nombre_rcv}}</td>
    <td>1</td>
    <td>0</td>
    <td>{{$valor_rcv}}</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_infonavit}}</td>
    <td>0</td>
    <td>{{$nombre_infonavit}}</td>
    <td>1</td>
    <td>{{$valor_infonavit}}</td>
    <td>0</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_infonavit2}}</td>
    <td>0</td>
    <td>{{$nombre_infonavit}}</td>
    <td>1</td>
    <td>0</td>
    <td>{{$valor_infonavit}}</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_ins}}</td>
    <td>0</td>
    <td>{{$nombre_ins}}</td>
    <td>1</td>
    <td>{{$valor_ins}}</td>
    <td>0</td>
    </tr>
    <tr>
    <td></td>
    <td>{{$cuenta_ins2}}</td>
    <td>0</td>
    <td>{{$nombre_ins}}</td>
    <td>1</td>
    <td>0</td>
    <td>{{$valor_ins}}</td>
    </tr>
    
    <tr>
    <td>FIN_PARTIDAS</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    </tr>
      </table>
