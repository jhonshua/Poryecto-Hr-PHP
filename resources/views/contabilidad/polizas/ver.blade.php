@extends('layouts.principal')
@section('tituloPagina', "Ver pólizas")

@section('content')
<div class="row">
<div class="col-12">
    <p style="text-align:center;font-size:2rem;fotn-wight:bold;">
    {{session()->get('empresa')['razon_social']}}
    </p>
</div>
    <div class="col-2" style="background-color: #F7DF87;text-align:center;font-size:1rem;font-weight: bold">
        POLIZA
    </div>
    <div class="col" style="background-color:#F0C018;font-weight: bold">
       No. {{ $data->id }}
    </div>
    <div class="col-3" style="background-color:#F0C018;font-weight: bold;text-align:right">
        {{ date('d-m-Y') }}
    </div>

<div class="col-md-12" style="background-color:#F0C018;font-weight: bold">
    Nómina {{$data->nombre_periodo}} del {{ \Carbon\Carbon::parse($data->fecha_inicial_periodo)->format('d/m/Y')  }} AL {{ \Carbon\Carbon::parse($data->fecha_final_periodo)->format('d/m/Y') }}
</div>
</div>
<div class="row">
<div class="col-md-12 p-0">
    <table class="table table-bordered table-sm">
        <thead>
            <tr style="background-color:#000;color:#FFF;">
                <th style="font-weight: normal;">Cuenta</th>
                <th style="font-weight: normal;">Concepto</th>
                <th style="font-weight: normal;">Nombre</th>
                <th style="font-weight: normal;">Debe</th>
                <th style="font-weight: normal;">Haber</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conceptos as $c )    
            <tr>
                <td>{{ $c[0] }}</td>
                <td>{{ $c[1] }}</td>
                <td>{{ $c[2] }}</td>
                <td>{{ $c[3] }}</td>
                <td>{{ $c[4] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
<div class="row">
    <table class="table table-bordered table-sm">
    <tr>
        <td colspan="2"  style="background-color:#F7DF87;width:56%"></td>
        <td style="background-color:#000; color:#FFF;text-align:center;font-weight: bold;width: 23.5%;">SUMAS IGUALES</td>
        <td style="width: 10%;font-weight: bold">{{ $total_debe }}</td>
    <td style="font-weight: bold"> {{ $total_haber }}</td>
    </tr>
    <tr style="font-weight: bold;height: 100px;">
        <td >Hecho por:</td>
        <td>Revisado por:</td>
        <td>Autorizado:</td>
        <td colspan="2">Auxiliares:</td>
        
    </tr>
    </table>
</div>

@endsection