@extends('layouts.empleado')
@section('tituloPagina', "Recibos de nomina")

@section('content')


<div class="row">
    <div class="dataTables_btn" style="display:none">
        <div class="form-group mb-2">
            <a href="{{ route('empleado.nomina.zip_pdf',[Session::get('empleado')['id']]) }}" class="btn btn-warning mb-2 ">
               <button> <i class="fa fa-file-archive tooltip_" data-toggle="tooltip" title="DESCARGA MASIVA"></i> DESCARGA MASIVA</button>
            </a>
        </div>
    </div>
    <div class="col-md-12">
        <table class="table table-striped table-hover empresas mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Id</th>
                    <th>Tipo</th>
                    <th>Periodo</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
        <div class="wrapper-table">
            <table class="table table-striped table-hover empresas">
                <tbody>
                    @foreach($misc as $valores)

                        @foreach($valores as $valor)
                            <tr>
                                <td>{{$valor->id_periodo}}</td>
                                <td>{{$valor->nombre_periodo}}</td>
                                <td>{{$valor->numero_periodo}}</td>
                                <td>{{$valor->fecha_inicial_periodo}}</td>
                                <td>{{$valor->fecha_final_periodo}}</td>
                                <td><a class='btn btn-warning btn-sm' 
                                    href="{{route('empleado.nomina.ver_pdf',[$valor->id_empleado,$valor->id_e, $valor->file_xml, $valor->id_timbre,$valor->empresa])}}" target='_blank'>
                                    <i class='fas fa-file-pdf fa-2x text-danger'></i>
                                    </a>
                                </td>
                                <td><a class='btn btn-warning btn-sm' href="{{route('empleado.nomina.download_soap_xml',[$valor->id_e,$valor->id_empleado, $valor->file_xml])}}" target='_blank'>
                                    <i class='fas fa-file-code fa-2x text-primary'></i>
                                </a></td>
                            </tr>                        
                        @endforeach        
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
