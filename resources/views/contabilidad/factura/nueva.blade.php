<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<div class="container">
	@include('includes.header',['title'=>'Facturador - Nuevo', 'subtitle'=>'Contabilidad', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'factura.index'])

	<div class="article border mt-3">
		<div class="row containertable" id="containertable">
		    <div class="col-md-8 order-md-1">
		        <form action="{{ route('factura.insertar') }}" method="POST" class="needs-validation" novalidate="">
		            @csrf
		            <div class="col-md-5 mb-3">
		              <label for="country">Empresa emisora</label>
		              <select class="custom-select d-block w-100" id="emisora" name="emisora" required="">
		                <option value="">Selecciona una opción...</option>
		                    @foreach ($empresas as $e)
		                        <option value="{{$e->id}}">{{ $e->razon_social}}</option>
		                    @endforeach  
		              </select>
		            </div>
		            <div class="col-md-5 mb-3">
		                <label for="country">Metodo de Pago</label>
		                <select class="custom-select d-block w-100" id="forma" name="forma" required="">
		                    <option value="">Selecciona una opción...</option>
		                    <option value="03">Transferencia electrónica de fondos</option>
		                    <option value="01">Efectivo</option>
		                    <option value="02">Cheque nominativo</option>
		                    <option value="04">Tarjeta de crédito</option>
		                    <option value="05">Monedero electrónico</option>
		                    <option value="06">Dinero electrónico</option>
		                    <option value="08">Vales de despensa</option>
		                    <option value="12">Dación en pago</option>
		                    <option value="13">Pago por subrogación</option>
		                    <option value="14">Pago por consignación</option>
		                    <option value="15">Condonación</option>
		                    <option value="17">Compensación</option>
		                    <option value="23">Novación</option>
		                    <option value="24">Confusión</option>
		                    <option value="25">Remisión de deuda</option>
		                    <option value="26">Prescripción o caducidad</option>
		                    <option value="27">A satisfacción del acreedor</option>
		                    <option value="28">Tarjeta de débito</option>
		                    <option value="29">Tarjeta de servicios</option>
		                    <option value="30">Aplicación de anticipos</option>
		                    <option value="31">Intermediario pagos</option>
		                    <option value="99">Por definir</option>
		                </select>
		              </div>
		              <div class="col-md-5 mb-3">
		                <label for="country">Forma de Pago</label>
		                <select class="custom-select d-block w-100" id="metodo" name="metodo" required="">
		                    <option value="">Selecciona una opción...</option>
		                    <option value="PUE">Pago en una sola exhibición</option>
		                    <option value="PPD">Pago en parcialidades o diferido</option>
		                </select>
		                
		              </div>
		              <div class="col-md-5 mb-3">
		                <label for="country">Tipo de Comprobante</label>
		                <select class="custom-select d-block w-100" required="" name="tipo_comprobante" onChange="TipoComprobante();" id="selecti">
		                    <option value="">Selecciona una opción...</option>
		                    <option value="I">Ingresos</option>
		                    <option value="E">Egresos</option>
		                    <option value="P">Pagos</option>
		                </select>
		              </div>
		              <div class="mb-3;" style="display:none;" id="nTargeta">
		                <div class="col-md-6 mb-3">
		                    <label for="folio_fiscal">Folio Fiscal </label>
		                    <input type="text" name="folio_relacionado" class="form-control d-block w-100" placeholder="Folio Fiscal">
		                </div>
		                <div class="col-md-6 mb-3">
		                    <label for="tipo_relacion">Tipo </label>
		                    <select name="tipo_relacion" class="custom-select d-block w-100">
		                      <option value="">Selecciona un Tipo de Relacion...</option>
		                       <option value="01">Nota de crédito de los documentos relacionados</option>
		                       <option value="02">Nota de débito de los documentos relacionados</option>
		                       <option value="03">Devolución de mercancía sobre facturas o traslados previos</option>
		                       <option value="04">Sustitución de los CFDI previos</option>
		                       <option value="05">Traslados de mercancías facturados previamente</option>
		                       <option value="06">Factura generada por los traslados previos</option>
		                       <option value="07">CFDI por aplicación de anticipo</option>
		                       <option value="08">Factura generada por pagos en parcialidades</option>
		                       <option value="09">Factura generada por pagos diferidos</option>
		                    </select>
		                </div>
		              </div>

		              <div id="nTargeta2" class="mt-3" style="display:none;">
		                <div class="col-md-6 mb-3">
		                    <label for="tipo_relacion">Fecha Pago </label>
		                    <input type="datetime-local" name="fecha_pago" class="form-control" value="2020-01-24T20:36:20" step="1">
		                </div>
		                <div class="row mb-3">
		                    <div class="col-md-6 mb-3">
		                        <label for="tipo_relacion">Monto</label>
		                        <input type="text" name="monto" class="form-control" placeholder="Monto">
		                    </div>
		                    <div class="col-md-6 mb-3">
		                        <label for="tipo_relacion">Folio</label>
		                        <input type="text" name="folio" class="form-control" placeholder="Folio">
		                    </div>
		                </div>
		                <div class="row mb-3">
		                    <div class="col-md-6 mb-3">
		                        <label for="tipo_relacion">Importe Pagado</label>
		                        <input type="text" name="importe_pagado" class="form-control" placeholder="Importe Pagado">
		                    </div>
		                    <div class="col-md-6 mb-3">
		                        <label for="tipo_relacion">Numero Parcialidad</label>
		                        <input type="text" name="num_parcialidad" class="form-control" placeholder="Num Parcialidad">
		                    </div>
		                </div>
		                <div class="row mb-3">
		                    <div class="col-md-6 mb-3">
		                        <label for="tipo_relacion">Importe Saldo Anterio</label>
		                        <input type="text" name="importe_saldo_anterior" class="form-control" placeholder="Importe Saldo Anterior">
		                    </div>
		                    <div class="col-md-6 mb-3">
		                        <label for="tipo_relacion">Importe Saldo Insoluto</label>
		                        <input type="text" name="importe_saldo_insoluto" class="form-control" placeholder="Importe Saldo Insoluto">
		                    </div>
		                </div>
		                <button id="boton1" type="button" class="btn" onclick="document.getElementById('oculto1').style.display = 'block';document.getElementById('boton1').style.display = 'none';document.getElementById('boton2').style.display = 'block';" style="display: block;background: black; color: #ffffff;border:none;border-radius: 2%;float: right;">Mostrar</button>
		                <div id="oculto1" style="display: none;">
		                    <fieldset>
		                        <legend>2da Parcialidad</legend>
		                        <div class="col-md-6 mb-3">
		                            <label for="tipo_relacion">Folio Fiscal</label>                            
		                            <input type="text" name="folio_relacionado_2"  placeholder="Folio Fiscal" class="form-control">
		                        </div>
		                        <div class="row">                            
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Metodo de Pago</label>
		                                <select name="metodo_2" id="inputEst" class="form-control" title="Metodo de Pago">
		                                    <option value="">Selecciona una opcion...</option>
		                                    <option value="PUE">Pago en una sola exhibición</option>
		                                    <option value="PPD">Pago en parcialidades o diferido</option>
		                                </select>
		                            </div>
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Folio </label>                            
		                                <input type="text" name="folio_2"  placeholder="Folio Fiscal" class="form-control">
		                            </div>                            
		                        </div>
		                        <div class="row">
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Importe Pagado</label>                            
		                                <input type="text" name="importe_pagado_2"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Numero Parcialidad</label>                            
		                                <input type="text" name="num_parcialidad_2"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                        </div>
		                        <div class="row">
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Importe Saldo Anterior</label>                            
		                                <input type="text" name="importe_saldo__anterior_2"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Importe Saldo Insoluto</label>                            
		                                <input type="text" name="importe_saldo_insoluto_2"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                        </div>
		                    </fieldset>
		                </div>
		                <button id="boton2" type="button" class="btn tw" onclick="document.getElementById('oculto2').style.display = 'block';
		                    document.getElementById('boton2').style.display = 'none';
		                    document.getElementById('boton1').style.display = 'none';" style="display: none;background: black; color: #ffffff;border:none;border-radius: 2%;float: right;">Mas</button>
		                <div id="oculto2" style="display: none;">
		                    <fieldset>
		                        <legend>3da Parcialidad</legend>
		                        <div class="col-md-6 mb-3">
		                            <label for="tipo_relacion">Folio Fiscal</label>                            
		                            <input type="text" name="folio_relacionado_3"  placeholder="Folio Fiscal" class="form-control">
		                        </div>
		                        <div class="row">                            
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Metodo de Pago</label>
		                                <select name="metodo_3" id="inputE0st" class="form-control" title="Metodo de Pago">
		                                    <option value="">Selecciona una opcion...</option>
		                                    <option value="PUE">Pago en una sola exhibición</option>
		                                    <option value="PPD">Pago en parcialidades o diferido</option>
		                                </select>
		                            </div>
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Folio </label>                            
		                                <input type="text" name="folio_3"  placeholder="Folio Fiscal" class="form-control">
		                            </div>                            
		                        </div>
		                        <div class="row">
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Importe Pagado</label>                            
		                                <input type="text" name="importe_pagado_3"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Numero Parcialidad</label>                            
		                                <input type="text" name="num_parcialidad_3"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                        </div>
		                        <div class="row">
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Importe Saldo Anterior</label>                            
		                                <input type="text" name="importe_saldo__anterior_3"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                            <div class="col-md-6 mb-3">
		                                <label for="tipo_relacion">Importe Saldo Insoluto</label>                            
		                                <input type="text" name="importe_saldo_insoluto_3"  placeholder="Folio Fiscal" class="form-control">
		                            </div>
		                        </div>
		                    </div>
		                </div>
		              <div class="col-md-5 mb-3">
		                <label for="country">Uso CFDI</label>
		                <select class="custom-select d-block w-100" id="regimen" name="regimen" required="">
		                    <option value="">Selecciona una opción...</option>
		                    <option value="G01">Adquisición de mercancias</option>
		                      <option value="G02">Devoluciones, descuentos o bonificaciones</option>
		                      <option value="G03">Gastos en general</option>
		                      <option value="I01">Construcciones</option>
		                      <option value="I02">Mobilario y equipo de oficina por inversiones</option>
		                      <option value="I03">Equipo de transporte</option>
		                      <option value="I04">Equipo de computo y accesorios</option>
		                      <option value="I05">Dados, troqueles, moldes, matrices y herramental</option>
		                      <option value="I06">Comunicaciones telefónicas</option>
		                      <option value="I07">Comunicaciones satelitales</option>
		                      <option value="I08">Otra maquinaria y equipo</option>
		                      <option value="D01">Honorarios médicos, dentales y gastos hospitalarios.</option>
		                      <option value="D02">Gastos médicos por incapacidad o discapacidad</option>
		                      <option value="D03">Gastos funerales.</option>
		                      <option value="D04">Donativos.</option>
		                      <option value="D05">Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).</option>
		                      <option value="D06">Aportaciones voluntarias al SAR.</option>
		                      <option value="D07">Primas por seguros de gastos médicos.</option>
		                      <option value="D08">Gastos de transportación escolar obligatoria.</option>
		                      <option value="D09">Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.</option>
		                      <option value="D010">Pagos por servicios educativos (colegiaturas)</option>
		                      <option value="P01">Por definir</option>
		                  
		                </select>
		              </div>

		            <input type="hidden" name="usuario" value="{{ Auth::user()->email }}">
		            
		          <hr class="mb-4">
		          <input type="submit" class="btn button-style" name="Siguiente" value="Siguiente" />
		        </form>
		      </div>
		</div>
	</div>
</div>

<script>
  
    function TipoComprobante(){
          
        var aux = $('#selecti').val();
        console.log(aux);
        if(aux == 'E' ){
            $('#nTargeta').css('display','block');              
       	}else if(aux=='P'){
            $('#nTargeta').css("display", "block");
            $('#nTargeta2').css("display", "block");
        }else{
            document.getElementById('nTargeta').style.display='none';
            document.getElementById('nTargeta2').style.display='none';
        }
    }

    function ocultar()
    {
    	document.getElementById('obj1').style.display = 'none';
    }

    function mostrar()
    {
    	document.getElementById('obj2').style.display = 'block';
    }

</script>