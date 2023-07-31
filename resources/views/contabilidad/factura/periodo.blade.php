@extends('layouts.principal')
@section('tituloPagina', "Control de pólizas")

@section('content')

<div class="row mt-5">
    <div class="col-md-11">
        <div class="card">
            <div class="card-header bg-warning text-center">
              <h5 class="font-weight-bold">Facturas</h5>
            </div>         
            <div class="card-body">
                <table class="table responsive-table table-striped  table-sm">
                    <thead>
                        <tr>
                            <th scope="col" class="bg-warning">Emisora</th>
                            <th scope="col" class="bg-warning">Subtotal</th>
                            <th scope="col" class="bg-warning">IVA</th>
                            <th scope="col" class="bg-warning">Total</th>
                            <th scope="col" class="bg-warning">Opciones</th>
                            <th scope="col" class="bg-warning">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr>
                            <th colspan="6">DEPÓSITO 1</th>
                        </tr>
                        <tr>
                            @if (empty($deposito_uno))
                                <th colspan="6" style="font-weight: normal;">EL DEPÓSITO 1 AUN NO ESTA TIMBRADO</th>
                            @else
                                no vacio
                            @endif
                        </tr>
                        <tr>
                          <th colspan="6">DEPÓSITO 2</th>
                        </tr>
                        <tr>
                            @if (empty($deposito_uno))
                            <th colspan="6" style="font-weight: normal;">EL DEPÓSITO 2 AUN NO ESTA TIMBRADO</th>
                        @else
                            no vacio
                        @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-11 mt-1">
        <div class="card">
            <div class="card-header bg-warning text-center">
              <h5 class="font-weight-bold">Facturas</h5>
            </div>         
            <div class="card-body">
                <table class="table responsive-table table-striped  table-sm">
                    <thead>
                        <tr>
                          <th scope="col" class="bg-warning">Emisora</th>
                          <th scope="col" class="bg-warning">Concepto</th>
                          <th scope="col" class="bg-warning">Clave</th>
                          <th scope="col" class="bg-warning">Forma de Pago</th>
                          <th scope="col" class="bg-warning">Método de Pago</th>
                          <th scope="col" class="bg-warning">Uso CFDI</th>
                          <th scope="col" class="bg-warning">Monto</th>
                          <th scope="col" class="bg-warning"></th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr>
                          <th colspan="8">DEPÓSITO 1</th>
                        </tr>
                        <tr>
                            {{-- <th colspan="6" style="font-weight: normal;"></th> --}}
                            <form action="../codfuent_cfdi_33/FacturaPeriodo.php" method="POST">
                                <input type="hidden" name="periodo" value="" />
                                <input type="hidden" name="emisora" value="" />
                                <input type="hidden" name="total" value="" />
                                <input type="hidden" name="subtotal" value="" />
                                <input type="hidden" name="iva" value="" />
                                <input type="hidden" name="deposito" value="0" />
                                <th scope="row">Nombre Emisora</th>
                                <th scope="row"><input type="text" name="concepto" value="" style="width:95%;" id="inputEst" required placeholder="Concepto de facturacion..." /></th>
                                <th scope="row"><input type="number" name="clave" value="14111807" step="1" id="inputEst" style="width:90px;" required /></th>
                                <th scope="row">
                                   <select name="forma" required id="inputEst" style="width:160px;" >
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
                                </th>
               
                                <th scope="row">
                                   <select name="metodo" required id="inputEst" style="width:160px;" >
                                       <option value="PUE">Pago en una sola exhibición</option>
                                       <option value="PPD">Pago en parcialidades o diferido</option>
                                   </select>
                                </th>
                                <th scope="row">
                                   <select name="regimen" required id="inputEst" style="width:160px;" >
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
                                </th>
                                <th scope="row">$ 66666</th>
                                <th scope="row">
                                    <input type="submit" value="TIMBRAR" />
                                </th>
                               </form>
                        </tr>
                        <tr>
                          <th colspan="8">DEPÓSITO 2</th>
                        </tr>
                        <tr>
                            {{-- <th colspan="8" style="font-weight: normal;">EL DEPÓSITO 2 AUN NO ESTA TIMBRADO</th> --}}
                            <form action="../codfuent_cfdi_33/FacturaPeriodo.php" method="POST">
                                <input type="hidden" name="periodo" value="" />
                                <input type="hidden" name="emisora" value="" />
                                <input type="hidden" name="total" value="" />
                                <input type="hidden" name="subtotal" value="" />
                                <input type="hidden" name="iva" value="" />
                                <input type="hidden" name="deposito" value="0" />
                                <th scope="row">
                                    <select name="emisora" required="" id="inputEst" style="width:250px;">
                                        <option value="">SELECCIONE UNA OPCION...</option>
                                 <option value="16">DESARROLLADORA DE EMPRESAS, ACT, SA de CV</option><option value="23">V-ADMINISTRATIVA FC S.A DE C.V.</option>                 </select>
                                 
                                </th>
                                <th scope="row"><input type="text" name="concepto" value="" style="width:95%;" id="inputEst" required placeholder="Concepto de facturacion..." /></th>
                                <th scope="row"><input type="number" name="clave" value="14111807" step="1" id="inputEst" style="width:90px;" required /></th>
                                <th scope="row">
                                   <select name="forma" required id="inputEst" style="width:160px;" >
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
                                </th>
               
                                <th scope="row">
                                   <select name="metodo" required id="inputEst" style="width:160px;" >
                                       <option value="PUE">Pago en una sola exhibición</option>
                                       <option value="PPD">Pago en parcialidades o diferido</option>
                                   </select>
                                </th>
                                <th scope="row">
                                   <select name="regimen" required id="inputEst" style="width:160px;" >
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
                                </th>
                                <th scope="row">$ 66666</th>
                                <th scope="row">
                                    <input type="submit" value="TIMBRAR" />
                                </th>
                               </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection