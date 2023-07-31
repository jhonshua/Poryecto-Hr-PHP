@php //dd($movimientos); @endphp
<table class="" id="" cellspacing="0" style="width:100%;border-collapse:collapse;">
    <tr class="GridViewScrollHeader">
        <th>ID</th>
        <th>NOMBRE</th>
        <th class="text-nowrap">DEPARTAMENTO</th>
        <th>PUESTO</th>
        <th>SEDE</th>
        <th class="text-nowrap">ESTATUS</th>
        <th class="text-nowrap">FECHA ALTA</th>
        <th class="text-nowrap">FECHA BAJA</th>
        <th class="text-nowrap">CAUSA BAJA</th>
        <th class="text-nowrap">FINIQUITO FIRMADO</th>
        <th class="text-nowrap">FINIQUITADO</th>

    </tr>
    @foreach($movimientos as $movimiento)
        <tr class="">
            <td>{{$movimiento['id']}}</td>
            <td>{{$movimiento['nombre']}}</td>
            <td class="text-nowrap">{{$movimiento['departamento']}}</td>
            <td>{{$movimiento['puesto']}}</td>
            <td>{{$movimiento['sede']}}</td>
            <td class="text-nowrap">{{$movimiento['estatus']}}</td>
            <td class="text-nowrap">{{$movimiento['fecha_alta']}}</td>
            <td class="text-nowrap">{{$movimiento['fecha_baja']}}</td>
            <td class="text-nowrap">{{$movimiento['causa_baja']}}</td>
            <td class="text-nowrap">{{$movimiento['estatus_firma_finiquito']}}</td>
            <td class="text-nowrap">{{$movimiento['finiquitado']}}</td>
    
        </tr>
    @endforeach
</table>