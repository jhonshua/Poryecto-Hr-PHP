@extends('layouts.empleado')
@section('tituloPagina', "Recibos de nómina")

@section('content')


<div class="row">
	<div class="col-md-12">
        <table class="table table-striped table-hover empresas mb-0">
            <thead class="thead-dark">
                <tr>
                    <th width="10%">Tipo</th>
                    <th width="5%">Periodo</th>
                    <th width="25%">Del</th>
                    <th width="25%">Al</th>
                    <th width="5%" class="text-center">PDF</th>
                    <th width="5%" class="text-center">XML</th>
                </tr>
            </thead>
        </table>
        <div class="wrapper-table">
            <table class="table table-striped table-hover empresas">
                <tbody>
                    @foreach ($nominas as $reg)
                        <tr>
                            <td width="10%">{{$reg->nombre_periodo}}</td>
                            <td width="5%">{{$reg->id_periodo}}</td>
                            <td width="25%">{{$reg->fecha_inicial_periodo}}</td>
                            <td width="25%">{{$reg->fecha_final_periodo}}</td>
                            <td width="5%" align="center">
                                <a href="{{route('empleados.recibospdf')}}?idPeriodo={{$reg->id_periodo}}&xml={{$reg->rutaXML2}}" target="_blank">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </a>
                            </td>
                            <td width="5%" align="center">
                                <a href="{{asset('public/repositorio/'.$bd_id.'/'.Session::get('empleado')['id'].'/'.$reg->rutaXML2)}}" download><i class="fas fa-file-code fa-2x text-primary"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    @foreach ($aguinaldos as $reg)
                        <tr>
                            <td>AGUINALDO</td>
                            <td>{{$reg->id_periodo}}</td>
                            <td>{{$reg->fecha_inicial_periodo}}</td>
                            <td>{{$reg->fecha_final_periodo}}</td>
                            <td align="center">
                                <a href="{{route('empleados.recibospdf')}}?idPeriodo={{$reg->id_periodo}}&xml={{$reg->rutaXML2}}" target="_blank">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </a>
                            </td>
                            <td align="center">
                                <a href="{{asset('public/repositorio/'.$bd_id.'/'.Session::get('empleado')['id'].'/'.$reg->rutaXML2)}}" download>
                                    <i class="fas fa-file-code fa-2x text-primary"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection

@push('css')
<style>
    .wrapper-table{
        height: 530px;
        margin-bottom: 20px;
        overflow-y: scroll;
        width: 100%;
    }
</style>
@endpush
