<div class="modal fade" id="modalExportar" tabindex="-1" role="dialog" aria-labelledby="create" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post" action="{{route('poliza.exportar')}}" >
      @csrf
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalExportarT">Exportar póliza</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>      
      <div class="modal-body">
       <div class="col-xs-6">
        <h5>Ingresa los siguientes datos poara generar la póliza.</h5>
        <div class="form-group">
          <label for="titulo">Titulo nómina</label>
          <input type="text" class="form-control" id="titulo" name="titulo">
        </div>
        <div class="form-row">
          <div class="form-group col-6">
            <label for="póliza">No. póliza</label>
            <input type="text" class="form-control" id="poliza" name="poliza">

          </div>
          <div class="form-group col-6">
            <label for="dia">Día</label>
            <input type="text" class="form-control" id="dia" name="dia">

          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-6">
            <label for="ins_deber">Cuenta INS(Deber)</label>
            <input type="text" class="form-control" id="ins_deber" name="ins_deber">

          </div>
          <div class="form-group col-6">
            <label for="ins_haber">Cuenta ISN(Haber)</label>
            <input type="text" class="form-control" id="ins_haber" name="ins_haber">

          </div>
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">Cuenta IMSS</label>
          <input type="text" class="form-control" id="imss" name="imss">

        </div>
        <div class="form-group">
          <label for="rcv">Cuenta RCV</label>
          <input type="text" class="form-control" id="rcv" name="rcv">

        </div>
        <div class="form-group">
          <label for="infonavit">Cuenta Infonavit</label>
          <input type="text" class="form-control" id="infonavit" name="infonavit">

        </div>
      </div>
      <input type="hidden" name="id" v-model="id_poliza">
      </div>
      <div class="modal-footer">
          <input  type="submit" class="btn btn-warning" value="Crear">     
      </div>
    </div>
    </form>
  </div>
</div>