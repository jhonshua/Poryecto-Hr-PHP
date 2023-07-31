<form action="" method="POST" id="frmAudienciaPreJudicial" role="form" enctype="multipart/form-data">
    @csrf 
    @if(!empty($demanda->id))
    <input type="hidden" name="idDemanda" id="idDemanda" value="{{$demanda->id}}"/>  
    @endif 
    <input type="hidden" name="idAudiencia" id="idAudiencia">
    <input type="hidden" name="pre" id="pre">
    <h1 id="titulo"></h1>
    <div class="row">         
        <div class="col-sm-4" id="divExpediente">
            <div class="form-group">
                <label for="expediente">Expediente </label>
                <input class="form-control" id="expediente" name="expediente" required type="text" />
            </div>
        </div>
        <div class="col-md-4" id="divCiudad">
            <div class="form-group">
                <label for="ciudad">Ciudad </label>
                <input class="form-control input-lg" name="ciudad" id="ciudad" type="text" required />
            </div>
        </div>
        <div class="col-md-4">
            <div id="DivFechaAviso" class="form-group"></div>
        </div>

        <div class="col-md-4" id="ContDivFechaSentencia">
            <div id="DivFechaSentencia" class="form-group"></div>
        </div>
        <div class="col-md-4" id="DivTipoPrueba">
            <label for="TipoPruebaCons">Tipo de prueba </label>
            <select class="form-control" name="TipoPruebaCons" id="TipoPruebaCons">
                <option value="">Selecciona una opcion...</option>
                <option value="1">Documental Privado</option>
                <option value="2">Documental Publico</option>
                <option value="3">Confesional hechos propios</option>
                <option value="4">Confesional Representado</option>
                <option value="5">Testimonial</option>
                <option value="6">Inspección Ocular</option>
                <option value="7">Prueba pericial</option>
                <option value="8">Instrumental Actuaciones</option>
                <option value="9">Presuncional Humana</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4" id="divjunta">
            <div class="form-group">
                <label for="junta">Junta </label>
                <input class="form-control input-sm" id="junta" name="junta" type="text" required/>
            </div>
        </div>
        <div class="col-md-4">
            <div id="DivFechaAudiencia" class="form-group"></div>
        </div>
        <div class="col-md-4" id="divhora_audiencia">
            <div id="DivHoraAudiencia" class="form-group"></div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label form="honorarios">Costo Honorarios </label>
                <input type="number" name="honorarios" id="honorarios" step="0.01" value="0"  class="form-control"/>
            </div>
        </div>
    </div>
    <div class="row" id="DDescripcion">
        <div class="col-md-12">
            <div class="form-group">
                <label for="ArregloConci">Descripción </label>
                <textarea class="form-control" name="ArregloConci" id="ArregloConci" rows="3"></textarea>
            </div>
        </div>
    </div>
    

    <div class="row" id="DIncidencias">
        <div class="col-md-12">
            <div class="form-group">
                <label for="incidencias">Incidencias </label>
                <textarea class="form-control" name="incidencias" id="incidencias" rows="3"></textarea>
            </div>
        </div>
    </div>

    <div class="row" id="DObservaciones">
        <div class="col-md-12">
            <div class="form-group">
                <label for="observacionesCons">Observaciones </label>
                <textarea class="form-control" name="observacionesCons" id="observacionesCons" rows="3"></textarea>
            </div>
        </div>
    </div>

    <div class="row" id="DivSentido">
        <div class="col-md-4">
            <label for="Sentido">Sentido </label> 
            <select name="Sentido" id="Sentido" class="form-control" onChange="sentencia();" >
                <option value="">Selecciona una opcion...</option>
                <option value="Confirmando">Confirmando</option>
                <option value="Revocando">Revocando</option>
            </select>
        </div>
        <div class="col-md-4 Confirmado"> 
            <label for="montoCons">Monto </label>
            <input type="number" class="form-control" step="0.01" name="montoCons"  id="montoCons" placeholder="0.00"/>
        </div>
        <div class="col-md-4 Confirmado">
            <label for="FormaPagoCons">Forma de pago </label> 
            <input type="text" class="form-control" name="FormaPagoCons" id="FormaPagoCons" placeholder="Forma de pago" />
        </div>
    </div>

    <div class="row DTipoAudiencia">
        <div class="col-md-12">
            <div class="form-group">
                <label for="TipoAudiencia">Tipo Audiencia </label>
                <select class="form-control" name="TipoAudiencia" id="TipoAudiencia" required OnChange="habilitar();">
                    <option value="">Seleccione</option>
                    <option value="1">Conciliación</option>
                    <option value="2">Contestación de Demanda y Exepciones</option>
                    <option value="3">Ofrecimiento de Pruebas</option>
                    <option value="4">Desahogo de Pruebas</option>
                    <option value="5">Reinstalación</option>
                    <option value="6">Alegatos</option>
                    <option value="7">Laudo</option>
                </select>
            </div>
        </div>
        
    </div>
    <div class="row DTipoAudiencia">
        <div id="TipoAudienciaSelect" class="col-md-12"></div>
    </div>
    <br/>
    @include('juridico.audiencia-conciliatorio-modal')
    
    <h1 class="evidencia">Documentos</h1>
    <div class="row evidencia">
        <div class="col-md-12" id="multimedia"></div>
    </div>
    @if(!empty($demandas))
    <br/><h1 class="DivDemandas" style="display:none">Demandas</h1>
    <div class="row" id="DivDemandas" style="display:none">
        <div class="col-md-12">
            <label for="demandas[]">Demandas </label><small><i> (Selecciona a que demandas aplica la audiencia)</i></small>
            <select class="form-control" multiple id="demandas[]" name="demandas[]">
                @foreach($demandas as $demanda)
                @php        @endphp
                <option value="{{$demanda->id}}">{{$demanda->nombre}} {{$demanda->apaterno}} {{$demanda->amaterno}}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    <div class="form-group text-center">
        <br/><br/>
        <a href="#" class="btn button-style-cancel mr-2 tooltip_" data-toggle="tooltip" id="btn_cancelar_pre" title="Cancelar" data-dismiss="modal">Cancelar</a>
        <button  class="borrar btn button-style mr-2 tooltip_" data-toggle="tooltip" id="btn_audiencia" title="Actualizar"></button>           
    </div>

</form>

<link href="{{asset('css/bootstrap-datetimepicker.css')}}" rel="stylesheet">
<link href="{{asset('css/fileinput/fileinput.min.css')}}" rel="stylesheet">
{{-- <link href="{{ asset('css/iconos_datepicker.css') }}" rel="stylesheet"> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script src="{{asset('js/datetimepicker/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{asset('js/moment/moment.js')}}"></script>
<script src="{{asset('js/moment/es.js')}}"></script>
<script src="{{asset('js/fileinput/fileinput.min.js')}}"></script>
<script src="{{asset('js/fileinput/locales/es.js')}}"></script>
<script src="{{asset('js/fileinput/themes/fas/theme.js')}}"></script>
<script src="{{asset('js/fileinput/themes/explorer-fas/theme.js')}}"></script>
<script>
var validator = $("#frmAudienciaPreJudicial").validate({
    errorClass: "text-danger",
    errorElement: "span",
    focusInvalid: false,
    errorPlacement: function(error, element) {
        error.appendTo( $('label[for="'+element.attr("name")+'"]') );
    },
    rules: {
        'expediente': {required: true},
        'ciudad': {required: true},
        'FechaAviso': {required: true},
        'FechaAudiencia': {required: true},
        'ArregloConci' : {required: true},
        'TipoAudiencia' : {required:true},
        'TipoPruebaCons' : {required:true},
        'observacionesCons' : {required:true},
        'Sentido' : {required:true},
        'demandas[]' : {required:true}
    }
});

function formAudienciasIni(tipo){
    $("span.text-danger").remove();
    $("#frmAudienciaPreJudicial input[type=text]").val('').removeClass('text-danger');
    $("#frmAudienciaPreJudicial input[type=number]").val('0').removeClass('text-danger');
    $("#frmAudienciaPreJudicial textarea").val('').removeClass('text-danger');
    $("#arreglo_conciliatorio").prop('checked',false);
    $(".conciliatorio").each(function(){
        $(this).prop('checked',false);
    });
    $("#TipoAudienciaSelect").html("");
    $("#TipoAudiencia").val("");
    arreglo();
    fechas();
    fechaProxima();
    archivosMultimedia();
    sentencia();
    if(tipo == 1){//prejudicial
        $("#titulo").html("Datos Audiencia PRE-Judicial");
        $(".evidencia, #arregloConciliatorio,#DivCheckArregloConciliatorio,#divhora_audiencia, #divExpediente, #divCiudad, #DDescripcion").slideDown();
        $("#DivSentido,#divjunta, .DTipoAudiencia, #DIncidencias, #ContDivFechaSentencia,#DivTipoPrueba,#DObservaciones").slideUp();
    }else if(tipo == 0){//judicial
        $("#titulo").html("Datos Audiencia Judicial");
        $(".evidencia, #divjunta, #arregloConciliatorio, #DivCheckArregloConciliatorio,.DTipoAudiencia, #DIncidencias, #divExpediente, #divCiudad").slideDown();
        $("#divhora_audiencia, #DivSentido,#DDescripcion, #ContDivFechaSentencia,#DivTipoPrueba,#DObservaciones").slideUp();
    }else if(tipo == 2){ //constitucional
        $("#titulo").html("Datos Audiencia Constitucional");
        $("#divjunta, .evidencia, #arregloConciliatorio,#DivCheckArregloConciliatorio,#DDescripcion,.DTipoAudiencia, #DIncidencias, #divExpediente, #divCiudad").slideUp();
        $("#divhora_audiencia, #DivSentido,#ContDivFechaSentencia,#DivTipoPrueba,#DObservaciones").slideDown();
    }else if(tipo == 3){//masiva
        $("#titulo").html("Datos Audiencia Masiva");
        $(".DivDemandas,#DivDemandas,.evidencia, #divjunta, #arregloConciliatorio, #DivCheckArregloConciliatorio,.DTipoAudiencia, #DIncidencias, #divExpediente, #divCiudad").slideDown();
        $("#divhora_audiencia, #DivSentido,#DDescripcion, #ContDivFechaSentencia,#DivTipoPrueba,#DObservaciones").slideUp();
        $("#multimedia").html('<center>Las evidencias se cargan individualmente</center>');
    }
    $("#pre").val(tipo);
}

function archivosMultimedia(){
    $("#multimedia").html('<label for="imagenS">Subir Archivos <small> (Adjunta imagenes o archivos PDF, PNG)</small></label><input type="file" class="form-control" name="imagenS[]" multiple id="imagenS" />');
    $("#imagenS").fileinput({
        language:'es',
        theme: 'fas',
        disabledPreviewExtensions: ['msi','exe','com','zip','rar','app','vb','scr'],
        allowedFileExtensions: ["jpg", "pdf", "png", "jpeg"],
        showUpload:false,
        browseIcon:'<i class="fa fa-folder-open"></i>',
        browseClass:'btn btn-success',
        removeIcon:'<i class="fa fa-trash"></i>',
        removeClass:'btn btn-default btn-secondary',
        previewFileType: "image",
        previewFileIcon:'<i class="fa fa-file-archive-o"></i>',
    });  
}

function fechas(){
    $("#DivFechaAviso").html('<label for="FechaAviso">Fecha notificación </label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar-alt"></i></span></div><input type="text" class="form-control" name="FechaAviso" id="FechaAviso" required></div>');
    $("#DivFechaAudiencia").html('<label  for="FechaAudiencia">Fecha audiencia </label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar-alt"></i></span></div><input type="text" class="form-control" name="FechaAudiencia" id="FechaAudiencia" required></div>');
    $("#DivHoraAudiencia").html('<label  for="">Hora audiencia</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-clock"></i></span></div><input type="text" class="form-control" name="HoraAudiencia" id="HoraAudiencia"></div>');
    $("#DivFechaSentencia").html('<label  for="FechaSentencia">Fecha sentencia </label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar-alt"></i></span></div><input type="text" class="form-control" name="FechaSentencia" id="FechaSentencia" required></div>');

    $('#FechaAviso').datetimepicker({format: 'DD-MM-YYYY'});
    $('#FechaAudiencia').datetimepicker({format: 'DD-MM-YYYY'});
    $('#HoraAudiencia').datetimepicker({format: 'LT'});
    $('#FechaSentencia').datetimepicker({format: 'DD-MM-YYYY'});
}


function habilitar(){
    var audiencia= $('#TipoAudiencia').val();
    if(audiencia == 1){
        $("#TipoAudienciaSelect").html('<div class="form-group"><label for="ArregloConciTipo">Observaciones</label><textarea class="form-control" name="ArregloConciTipo" id="ArregloConciTipo" rows="3"></textarea></div>');
    }else if(audiencia == 2){
        $("#TipoAudienciaSelect").html('<div class="form-group"><label for="contestacion">Tipo de contestacion</label><select name="contestacion" id="contestacion" class="form-control" ><option value="0">Selecciona una opcion...</option><option value="1">Aceptar Relacion Laboral con renuncia</option><option value="2">Negar Relacion Laboral</option><option value="3">Negando el Despido con reinstalacion</option></select></div>');
    }else if(audiencia == 3){
        $("#TipoAudienciaSelect").html('<div class="form-group"><label for="tipoPrueba">Tipo de prueba</label><select name="tipoPrueba" class="form-control" id="tipoPrueba"  OnChange="habilitarPrueba();"><option value="0">Selecciona una opcion...</option><option value="1">Documental Privado</option><option value="2">Documental Publico</option><option value="3">Confesional hechos propios</option><option value="4">Confesional Representado</option><option value="5">Testimonial</option><option value="6">Inspección Ocular</option><option value="7">Prueba pericial</option><option value="8">Instrumental Actuaciones</option><option value="9">Presuncional Humana</option></select></div><div id="habilitarPrueba"></div>');
    }else if(audiencia == 4){ // Desahogo de pruebas
       $("#TipoAudienciaSelect").html('<div class="form-group"><label for="desahogo">Tipo de prueba</label><select name="desahogo" id="desahogo" class="form-control"><option value="0">Selecciona una opcion...</option><option value="1">Documental Privado</option><option value="2">Documental Publico</option><option value="3">Confesional hechos propios</option><option value="4">Confesional Representado</option><option value="5">Testimonial</option><option value="6">Inspección Ocular</option><option value="7">Prueba pericial</option><option value="8">Instrumental Actuaciones</option><option value="9">Presuncional Humana</option></select></div><div class="form-group"><label>Listar los documentos que se van a presentar</label><textarea name="motivoDesahogo" id="motivoDesahogo" rows="3" class="form-control"></textarea></div>');
    }else if(audiencia == 6){//Alegatos
        $("#TipoAudienciaSelect").html('<div class="form-group"><label for="ObservacionesAlegato">Observaciones</label><textarea class="form-control" name="ObservacionesAlegato" id="ObservacionesAlegato" rows="3"></textarea></div>');
    }else if(audiencia == 7){//Laudo
        $("#TipoAudienciaSelect").html('<div class="form-group"><label for="TipoSitua">Situación</label><select class="form-control" name="TipoSitua" id="TipoSitua"  OnChange="habilitarSituacion();"><option value="0">Selecciona una opcion...</option><option value="1">Absuelto</option><option value="2">Condenado</option></select></div><div id="habilitarSituacion"></div>');
    }else{
        $("#TipoAudienciaSelect").html('');
    }
    
}


function habilitarPrueba(){
    var prueba= $('#tipoPrueba').val();
    if(prueba == 1 || prueba == 2 || prueba == 6){
        $("#habilitarPrueba").html('<div class="form-group"><label>Listar los documentos que se van a presentar</label><textarea  name="motivo" id="motivo" rows="5" class="form-control"></textarea></div>');
    }else if(prueba == 3 || prueba == 4){ //prueba 2
        $("#habilitarPrueba").html('<div class="row"><div class="col-md-4"><label>Persona 1</label><input class="form-control input-sm" type="text" name="persona1" id="persona1" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio1" id="domicilio1" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado1" id="estado1" placeholder="Estado"/></div><div class="col-md-4"><label>Persona 2</label><input class="form-control input-sm" type="text" name="persona2" id="persona2" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio2" id="domicilio2" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado2" id="estado2" placeholder="Estado"/></div><div class="col-md-4"><label>Persona 3</label><input class="form-control input-sm" type="text" name="persona3" id="persona3" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio3" id="domicilio3" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado3" id="estado3" placeholder="Estado"/></div></div><br/>');
    }else if(prueba == 5){ // 2 y 3
        $("#habilitarPrueba").html('<div class="row"><div class="col-md-4"><label>Persona 1</label><input class="form-control input-sm" type="text" name="persona1" id="persona1" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio1" id="domicilio1" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado1" id="estado1" placeholder="Estado"/></div><div class="col-md-4"><label>Persona 2</label><input class="form-control input-sm" type="text" name="persona2" id="persona2" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio2" id="domicilio2" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado2" id="estado2" placeholder="Estado"/></div><div class="col-md-4"><label>Persona 3</label><input class="form-control input-sm" type="text" name="persona3" id="persona3" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio3" id="domicilio3" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado3" id="estado3" placeholder="Estado"/></div></div><br/><div class="row"><div class="col-md-4"><label>Persona 4</label><input class="form-control input-sm" type="text" name="persona4" id="persona4" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio4" id="domicilio4" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado4" id="estado4" placeholder="Estado"/></div><div class="col-md-4"><label>Persona 5</label><input class="form-control input-sm" type="text" name="persona5" id="persona5" placeholder="Nombre"/><input class="form-control input-sm" type="text" name="domicilio5" id="domicilio5" placeholder="Domicilio"/><input class="form-control input-sm" type="text" name="estado5" id="estado5" placeholder="Estado"/></div></div><br/>');
    }else if(prueba == 7){ // 4
        $("#habilitarPrueba").html('<div class="form-group"><label>Tipo</label><div class="radio"><label><input type="radio" name="RadioTipoPrueba" value="Caligrafia" class="RadioTipoPrueba" />&nbsp;Caligrafia</label></div><div class="radio"><label><input type="radio" name="RadioTipoPrueba" value="Grafoscopia" class="RadioTipoPrueba" />&nbsp;Grafoscopia</label></div>');
    }else{
        $("#habilitarPrueba").html('');
    }
}

function habilitarSituacion(){
    var situacion = $('#TipoSitua').val();
    if($("#pre").val() != 3){
        if(situacion == 1){ //Absuelto
            $("#habilitarSituacion").html('<div class="row"><div class="col-md-12"><label for="FileLaudo">Documento de laudo</label><input type="file" name="FileLaudo" id="FileLaudo" class="form-control" /></div></div><br/><br/>');
            archivoLaudo();
        }else if(situacion == 2){ // Condenado
            $("#habilitarSituacion").html('<br/><div class="row"><div class="col-md-12"><label for="FileLaudo">Documento de laudo</label><input type="file" name="FileLaudo" id="FileLaudo" class="form-control" /></div></div><div class="row"><div class="col-md-4"><div class="checkbox"><label>Amparo&nbsp;<input type="checkbox" name="Amparo" id="Amparo" OnClick="amparo();" /></label></div></div><div id="amparoDiv" class="col-md-8"><div class="row"><div class="col-md-6"><label>Monto:</label><input type="number" step="0.01" name="monto" id="monto" placeholder="0.00" class="form-control"/></div><div class="col-md-6"><label>Forma de pago:</label><input type="text" name="FormaPago" id="FormaPago" placeholder="En que forma se dio el pago..." class="form-control"/></div></div></div></div><br/><br/>');
            archivoLaudo();
        }else{
            $("#habilitarSituacion").html('');
        }
    }else{
        if(situacion == ""){
            $("#habilitarSituacion").html('');
        }else{
            $("#habilitarSituacion").html('<center><i>La evidencia se carga individualmente</i></center>'); 

        }
    }
}

function archivoLaudo(){
    $("#FileLaudo").fileinput({
        language:'es',
        theme: 'fas',
        disabledPreviewExtensions: ['msi','exe','com','zip','rar','app','vb','scr'],
        allowedFileExtensions: ["jpg", "pdf", "png", "jpeg"],
        showUpload:false,
        browseIcon:'<i class="fa fa-folder-open"></i>',
        browseClass:'btn btn-success',
        removeIcon:'<i class="fa fa-trash"></i>',
        removeClass:'btn btn-default btn-secondary',
        previewFileType: "image",
        previewFileIcon:'<i class="fa fa-file-archive-o"></i>',
    });  
}
function amparo(){
    if( $("#Amparo").prop('checked')){
        $("#amparoDiv").html('');
    }else{
        $("#amparoDiv").html('<div class="row"><div class="col-md-6"><label>Monto:</label><input type="number" step="0.01" name="monto" id="monto" placeholder="0.00" class="form-control"/></div><div class="col-md-6"><label>Forma de pago:</label><input type="text" name="FormaPago" id="FormaPago" placeholder="En que forma se dio el pago..." class="form-control"/></div></div><br/><br/>');
    }
}

function sentencia(){
    if($("#Sentido").val() =='Confirmando'){
            $('.Confirmado').slideDown();
    }else{
            $('.Confirmado').slideUp();
            $("#montoCons, #FormaPagoCons").val('');
    }
}


</script>
