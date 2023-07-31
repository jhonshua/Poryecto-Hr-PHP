<form action="" method="post" id="formDemanda">
    @csrf               
    <input type="hidden" id="idDemanda" name="idDemanda" value="">
    <legend><b>Detalles</b></legend>
    
    <div class="row">
        <div class="col-md-6">
            <div id="DivFechaNotificacion" class="form-group"></div>

            <div class="form-group">
                <label for="prestaciones_devengadas">Prestaciones devengadas </label>
                <input type="number" name="prestaciones_devengadas" id="prestaciones_devengadas" step="0.01" value="" class="form-control input-sm" required/>
            </div>
            <div class="form-group">
                <label for="indemnizacion_constitucional">Indeminizacion Constitucional </label>
                <select name="indemnizacion_constitucional" id="indemnizacion_constitucional" class="form-control input-sm" required>
                    <option value="0">Seleccione una opcion</option>
                    <option value="30">30 dias</option>
                    <option value="45">45 dias</option>
                    <option value="60">60 dias</option>
                    <option value="90">90 dias</option>
                </select>
            </div>
            <div class="form-group">
                <label for="folio">No. de folio </label>
                <input type="text" name="folio" id="folio" placeholder="No. de folio" class="form-control input-sm"/>
            </div>
            <div class="form-group">
                <label for="motivo">Motivo </label>
                <textarea class="form-control input-sm" name="motivo" id="motivo" rows="3" required></textarea>
            </div>
            <div class="form-group">
            
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="PActora">Parte actora </label>
                <div id="actores"></div>
            </div>
            <div class="form-group">
                <label for="cliente">Parte demandada </label>
                <div id="acusado"></div>
                <div id="NmbOtroDiv" style="display:none">
                    <label for="NmbOtro">Nombre </label>
                    <input type="text" class="form-control input-sm" id="NmbOtro" name="NmbOtro" placeholder="Nombre" value=""/>
                </div>
            </div>
            <div class="form-group">
                <label for="contrato">Empresa que contrató </label>
                <div id="contrato"></div>
            </div>
            <div class="form-group">
                <a href="#" class="btn  button-style-cancel mr-2 tooltip_" data-toggle="tooltip" id="btn_cancelar_pre" title="Cancelar" data-dismiss="modal">Cancelar</a>
                <button  class="borrar btn button-style mr-2 tooltip_" data-toggle="tooltip" id="btn_demanda" title="Actualizar"><i class="fas fa-trash-alt"></i></button>           

            </div>
  
        </div>
    </div>

              
</form>

<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>

<script>
$("#formDemanda").validate({
    errorClass: "text-danger",
    errorElement: "span",
    errorPlacement: function(error, element) {
        error.appendTo( $('label[for='+element.attr("name")+']') );
    },
    rules: {
        'prestaciones_devengadas': {required: true},
        'indeminizacion_constitucional': {required: true},
        'folio': {required: true},
        'contrato': {required: true},
        'PActora': {required:true}
    }
});

</script>

