@php //dd($movimientos); @endphp
<table class="" id="" cellspacing="0" style="width:100%;border-collapse:collapse;">
    <tr class="GridViewScrollHeader">
        <th>Fecha inicial</th>
        <th>Fecha final</th>
        <th>Sede</th>
        <th>Trabajadores al inicio del periodo</th>
        <th>Trabajadores al final del periodo</th>
        <th>Promedio de trabajadores</th>
        <th class="text-nowrap">Altas del periodo</th>
        <th class="text-nowrap">Bajas del periodo</th>
        <th class="text-nowrap">IRP</th>
        <th class="text-nowrap">Estado</th>

    </tr>
    @foreach($movimientos as $d)
    <tr class="">
        <td>{{$d['datos']['finicio']}}</td>
        <td>{{$d['datos']['ffin']}}</td>
        <td>{{$d['datos']['sede']}}</td>
        <td>{{$d['datos']['i']}}</td>
        <td>{{$d['datos']['f']}}</td>
        <td>{{$d['datos']['promedio_total']}}</td>
        <td>{{$d['datos']['altas_periodo_total']}}</td>
        <td>{{$d['datos']['bajas_total']}}</td>
        @if($d['datos']['irp'] >= 0 && $d['datos']['irp'] <= 5) <td style="background: #F0C018;" class="bg-warning">{{$d['datos']['irp']}}%</td>
            <td style="background: #F0C018;" class="bg-warning">El IRP del periodo es BAJO</td>
            @elseif($d['datos']['irp'] > 5 && $d['datos']['irp'] <= 15) <td style="background: #38c172;" class="bg-success">{{$d['datos']['irp']}}%</td>
                <td style="background: #38c172;" class="bg-success">El IRP del periodo es el RECOMENDABLE</td>
                @elseif($d['datos']['irp'] > 15)
                <td style="background: #e3342f;" class="bg-danger">{{$d['datos']['irp']}}%</td>
                <td style="background: #e3342f;" class="bg-danger">El IRP del periodo es el ALTO</td>
                @endif

    </tr>
    @endforeach
</table>