@php $totalFiscal = $totalSindical = 0 @endphp

<table style="background:#F0C018;">
    <tr>
        <td style="background:#F0C018;">Periodo</td>
        <td>{{$periodo->id}}</td>
        <td  style="background:#F0C018;">Inicio</td>
        <td>{{$periodo->finicio}}</td>
        <td style="background:#F0C018;">Fin</td>
        <td> {{$periodo->ffin}}</td>
    </tr>
</table>

<table style="background:#F0C018;">
    <tr><td colspan="7" style="background:#F0C018;text-align:center;font-size:15px;"> FISCAL </td></tr>
    <tr> 
        <td style="background:#F0C018;">ID</td>
        <td style="background:#F0C018;">Num Empleado</td>
        <td style="background:#F0C018;">EMISORA</td>
        <td style="background:#F0C018;">NOMBRE</td>
        <td style="background:#F0C018;">APELLIDO PATERNO</td>
        <td style="background:#F0C018;">APELLIDO MATERNO</td>
        <td style="background:#F0C018;">SALARIO DIARIO</td>
        <td style="background:#F0C018;">SALARIO DIARIO INTEGRADO</td>
        <td style="background:#F0C018;">SUELDO NETO</td>
        <td style="background:#F0C018;">DIAS PAGADOS</td>
        <td style="background:#F0C018;">IMPORTE DEPO FISCAL</td>
        <td style="background:#F0C018;">IMPORTE DEPO SINDICAL</td>
        <td style="background:#F0C018;">TOTAL A DISPERSAR</td>
    </tr>
    @foreach($empleados as $empleado)
        <tr>
            <td>{{$empleado->id}}</td>
            <td>{{$empleado->id}}</td>
            <td>{{$empleado->razon_social}}</td>
            <td>{{$empleado->nombre}}</td>
            <td>{{$empleado->apaterno}}</td>
            <td>{{$empleado->amaterno}}</td>
            <td>{{$empleado->salario_diario}}</td>
            <td>{{$empleado->salario_diario_integrado}}</td>
            <td>{{$empleado->sueldo_neto}}</td>
            <td>{{$empleado->sueldo_neto}}</td>
            <td>{{$empleado->Fiscal}}</td>
            <td>{{$empleado->Sindical}}</td>
            <td>{{$empleado->Fiscal + $empleado->Sindical}}</td>
        </tr>
        @php $totalFiscal += $empleado->Fiscal; @endphp
    @endforeach
</table>      
            
  
