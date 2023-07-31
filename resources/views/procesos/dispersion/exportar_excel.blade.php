
@php  $totalFiscal = $totalSindical = 0 @endphp
<table style="background:#F0C018;">
    <tr><td colspan="7" style="background:#F0C018;text-align:center;font-size:15px;"> FISCAL </td></tr>
    <tr> 
        <td style="background:#F0C018;">Num Empleado</td>
        <td style="background:#F0C018;">ID Bancario</td>
        <td style="background:#F0C018;">Nombre</td>
        <td style="background:#F0C018;">Banco</td>
        <td style="background:#F0C018;">Importe</td>
        <td style="background:#F0C018;">Cuenta</td>
        <td style="background:#F0C018;">Clave Interbancaria</td>
    </tr>
    @foreach($empleados as $empleado)
        <tr>
            <td>{{$empleado->id}}</td>
            <td>{{$empleado->id}}</td>
            <td>{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</td>
            <td>{{$empleado->nombre_banco}}</td>
            <td>{{$empleado->Fiscal}}</td>
            <td style="mso-number-format:'@'">{{$empleado->cuenta_bancaria}}</td>
            <td style="mso-number-format:'@'">{{$empleado->clabe_interbancaria}}</td>
        </tr>
        @php $totalFiscal += $empleado->Fiscal; @endphp
    @endforeach
    <tr>
        <td colspan="3"  style="background:#F0C018;"></td>
        <td  style="background:#F0C018;">TOTAL</td>
        <td>{{$totalFiscal}}</td>
    </tr>
    <tr>
        <td colspan="7"></td>
    </tr>
</table>      
            

<table style="background:#F0C018;">
    <tr><td colspan="7" style="background:#F0C018;text-align:center;font-size:15px;"> SINDICAL </td></tr>
    <tr> 
        <td style="background:#F0C018;">Num Empleado</td>
        <td style="background:#F0C018;">ID Bancario</td>
        <td style="background:#F0C018;">Nombre</td>
        <td style="background:#F0C018;">Banco</td>
        <td style="background:#F0C018;">Importe</td>
        <td style="background:#F0C018;">Cuenta</td>
        <td style="background:#F0C018;">Clave Interbancaria</td>
    </tr>
    @foreach($empleados as $empleado)
        <tr>
            <td>{{$empleado->id}}</td>
            <td>{{$empleado->id}}</td>
            <td>{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</td>
            <td>{{$empleado->nombre_banco}}</td>
            <td>{{$empleado->Sindical}}</td>
            <td style="mso-number-format:'@'">{{$empleado->cuenta_bancaria}}</td>
            <td style="mso-number-format:'@'">{{$empleado->clabe_interbancaria}}</td>
        </tr>
        @php $totalSindical += $empleado->Sindical; @endphp
    @endforeach
    <tr>
        <td colspan="3"  style="background:#F0C018;"></td>
        <td  style="background:#F0C018;">TOTAL</td>
        <td>{{$totalSindical}}</td>
    </tr>
    
</table>    
