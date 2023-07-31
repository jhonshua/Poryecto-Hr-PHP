@extends('layouts.principal')
@section('tituloPagina', "Ver Factura")

@section('content')
<div class="row" id="crudFactura">
   
    <div class="col-md-4">
        <div class="col-md-12">
            <h3><strong>Datos Factura</strong></h3>                
        </div>
        <div class="card">
            <div class="card-body">
            <div class="col-md-12 mb-3">
              <h5 class="font-weight-bold">Empresa Emisora</h5>
                        <span>{{ $emisora->razon_social}}</span>
            </div>
            <div class="col-md-12 mb-3">
                <h5 class="font-weight-bold">Metodo de Pago</h5>
            <span>{{ $factura->metodo_string}}</span>
              </div>
              <div class="col-md-12 mb-3">
                <h5 class="font-weight-bold">Forma de Pago</h5>
                <span>{{ $factura->forma_string}}</span>                
              </div>
              <div class="col-md-12 mb-3">
                <h5 class="font-weight-bold">Tipo de Comprobante</h5>
              <span>{{ $factura->tipo_string}}</span>
              </div>
             @if ($factura->tipo_comprobante == "E" || $factura->tipo_comprobante == "P")
             <div class="col-md-12 mb-3">
                <h5 class="font-weight-bold">Folio</h5>
              <span>{{ $factura->folio_relacionado}}</span>
              </div>
             @endif
              <div class="col-md-12 mb-3">
                <h5 class="font-weight-bold">Uso CFDI</h5>
                <span>{{ $factura->regimen_string}}</span>     
              </div>
    </div>
</div>
    </div>
    <div class="col-md-8" >
        <div class="col-md-12">
            <h3><strong>Estatus:</strong> 
                <span class="badge badge-success" >Timbrada</span>
            </h3>                
        </div>
        <div class="card">
            <div class="card-body">
                
                <h3 class="card-title font-weight-bold text-center">Conceptos </h3>
                <table class="responsive-table table">
                    <thead>
                    <tr>
                        <th colspan="7" class="text-center">PRODUCTOS</th>
                    </tr>
                    <tr>
                        <th>Concepto</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Clave</th>
                        <th>Precio unitario</th>
                        <th>Importe</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($conceptos as $c)
                        <tr>
                            <th>{{$c->concepto}}</th>
                            <th>{{$c->cantidad}}</th>
                            <th>{{$c->unidad}}</th>
                            <th>{{$c->clave}}</th>
                            <th>${{number_format($c->monto, 2, ',', '.') }}</th>
                            <th>${{number_format($c->monto * $c->cantidad, 2, ',', '.')}}</th>
                        </tr>
                        @endforeach
                        <tr>
                            <th colspan="5" class="text-right">SubTotal</th>
                        <th>${{ number_format($factura->sub_total, 2, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right">IVA</th>
                            <th>${{ number_format($factura->iva, 2, ',', '.') }}</th>
                        </tr>
                        @if ($factura->retenidos == 1)    
                        <tr>
                            <th colspan="5" class="text-right">Impuestos Retenidos</th>
                            <th>${{ number_format($factura->sum_retenidos, 2, ',', '.') }}</th>
                        </tr>
                        @endif
                        <tr>
                            <th colspan="5" class="text-right">Total</th>
                            <th>${{ number_format($factura->total, 2, ',', '.') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    <div class="row">
        <div class="col-md-12 mt-2 text-right">
            <a href="{{ route('timbrar.factura.downloadFacturaPdf',[$factura->id]) }}" class="btn btn-dark" target="_BLANK">Ver factura</a>
            <a href="{{ route('timbrar.factura.downloadFacturaXml',[$repo,$timbrado[0]->file_xml]) }}" class="btn btn-dark">Descargar XML</a>
            <a href="{{ route('cancelar.cancelarFactura',[$factura->id]) }}" class="btn btn-danger">Cancelar</a>
        </div>
    </div>

  @endsection