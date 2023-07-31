<form action="" method="POST" id="frmPrincipal" role="form" enctype="multipart/form-data">
    @csrf               
    <input type="hidden" id="idAviso" name="idAviso" value="0">

    <div class="form-row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="text-center" >Aviso</h3></div>
                <div class="card-body">
                    <div class="titulo form-group ">
                        <label>Titulo</label>
                        <input type="text" class="form-control input-style-custom" id="titulo" name="titulo" required>
                    </div>
                    <div class="form-group" id="Divinicio"></div>
                    <div class="form-group" id="Divfin"></div>
                    <div class="tipo form-group">
                        <label>Tipo</label>
                        <select class="input-style-custom select-clase" style="width: 100%!important;" id="tipo" name="tipo" required>
                            <option value="">Selecciona...</option>
                            <option value="1">Imagen</option>
                            <option value="2">Video</option>
                        </select>
                    </div>
                    <div class="tipo form-group">
                        <label>Estado</label>
                        <div class="input-group">
                            <input type="checkbox" name="estatus" id="estatus" checked data-bootstrap-switch data-on-text='Activado' data-off-text='Desactivado'>  
                        </div>
                    </div>
                    <div class="botones form-group">
                        <div><br/><br/>
                            <a href="#" class="borrar button-style-cancel mr-2 tooltip_" data-toggle="tooltip" id="btn_cancelar" title="Cancelar" onClick="cerrarModal();";>Cancelar</a>
                            <button type="submit" name="submit" id="btn-agregar-aviso" data-toggle="tooltip" title="Agregar" value="submit" class="button-style tooltip_">Agregar aviso</button>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="text-center" >Multimedia</h3></div>
                <div class="card-body">
                    <div id="imgs"></div>
                    <div id="vdo"></div>
                </div>
            </div>
        </div>
    </div>
</form>
<style>
.card-header{
    background:#f0c018;
    margin-bottom:15px;
}
.card-warning{
    padding:10px 5px;
}

.card-title {
    margin-bottom: 0rem;
    text-align: center;
}

</style>