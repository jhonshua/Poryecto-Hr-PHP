<div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-labelledby="create" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Nuevo Concepto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post" v-on:submit.prevent="createConcepto">
        <div class="modal-body">
          <div class="col-xs-6">
          <div class="form-group ">
            <label>Cantindad</label>
            <input type="number" name="cantidad" class="form-control" step="1" v-model="newConcepto.cantidad">
          </div>
          <div class="form-group ">
            <label>UNIDAD</label>
            <select class="custom-select" name="unidad" v-model="newConcepto.unidad">
                <option value="H87">Pieza</option>
                <option value="EA">Elemento</option>
                <option value="E48">Unidad de servicio</option>
                <option value="ACT">Actividad</option>
                <option value="E51">Trabajo</option>
                <option value="KT">Kits</option>
                <option value="XBX">Caja</option>
                <option value="MON">Mes</option>
                <option value="11">Equipos</option>
                <option value="DAY">Dia</option>
            </select>
          </div>
          <div class="form-group ">
            <label>Concepto</label>
            <input type="text" name="concepto" class="form-control" v-model="newConcepto.concepto">
          </div>
          <div class="form-group ">
            <label>Clave</label>
            <input type="number" name="clave" class="form-control" v-model="newConcepto.clave" step="1" min="01010101" max="99999999">
         </div>
          <div class="form-group ">
            <label>Monto</label>
            <input type="number" name="clave" class="form-control" v-model="newConcepto.monto" min="0.01" step="0.01" max="9999999999">
         </div>
         <div class="form-check" v:if="factura.tipo_comprobante == 'I'">
             <input type="checkbox" class="form-check-input" id="retenido" v-model="newConcepto.impuesto_retenido" v-bind:true-value="1" v-bind:false-value="0"
             >
             <label class="form-check-label" for="retenido">Impuestos Retenidos</label>
          </div>
          <div class="form-check" v:if="factura.tipo_comprobante == 'I'">
             <input type="checkbox" class="form-check-input" id="iva_considerar" v-model="newConcepto.iva_considerar" v-bind:true-value="1" v-bind:false-value="0">
             <label class="form-check-label" for="iva_considerar">Considerar Iva</label>
          </div>
          <span v-for="error in errors" class="text-danger">@{{ error }}</span>
        </div>
        
        <div class="modal-footer">
                    <input  type="submit" class="btn btn-primary" value="Guardar">
        </div>
      </div>
    </form>
    </div>
  </div>
</div>