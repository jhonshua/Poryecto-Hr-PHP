<div class="modal" tabindex="-1" role="dialog" id="exportarPModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Exportar nóminas por periodo </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">
            

            <form method="post" id="periodo_biometricos_form" target="_blank" action="{{route('docs.nominasPeriodo')}}">
                @csrf
                <input type="hidden" name="tipo" id="id_tipo" value="XML">
                <input type="hidden" name="id_periodo" id="id_periodo" value="0">
                <input type="hidden" name="ejercicio" id="ejercicio">
                <input type="checkbox" name="" id="selCheckboxes">
                <label for="selCheckboxes" class="font-weight-bold">MARCAR TODAS/DESMARCAR TODAS:</label>
                <div id="cblist">


                </div>
                <div class="row">
                        <div class="col-md-12 text-center">
                            <a href="#" data-dismiss="modal" class="btn button-style-cancel mb-3">Cancelar</a>
                            <button class="btn button-style mb-3 guardar">Generar</button>
                        </div>
               
            </form>
        </div>
        </div>
    </div>
</div>

<style>
    #biometricosModal label{
        margin-bottom: 0px;
    }
</style>

<script>
$(function(){
    $("#selCheckboxes").on("click", function() {
        $(".check").prop("checked", this.checked);
    });

    // al abrir el modal cargamos las prestaciones
    $('#exportarPModal').on('shown.bs.modal', function (e) {
        var tipo = $('#id_tipo').val();
        var periodo = $('#id_periodo').val();
        
        console.log('tipo: ' + tipo + ' periodo: ' + periodo + ' url: ' + url);

        var url = 'departamentos-reporte-nomina/'+ periodo;
        $.ajax({
            type: "GET",
            url: url,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    //location.reload();
                    console.log(response);
                    $("#selCheckboxes").prop("checked", false);
                var container = $('#cblist');
                container.html('');
                $.each(response.deptos, function(k, v) {
                    /// do stuff
                    $('<input />', { type: 'checkbox', class: 'check',name:'deptos[]', id: v.id, value: v.id }).appendTo(container);
                    $(' <label /> ', { 'for': v.id,text: ' '+v.nombre }).appendTo(container);
                    $('<br />').appendTo(container);
                });
                    
                } else {
                    swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                }
            },
            error: function() {
                swal("Algo salio mal", "Vuelve a intentarlo", "warning");
            }
        });

    });

    $('#exportarPModal #incluirBiometrico').change(function(){
        $('#exportarPModal .fechas').toggle();
    });


    $('#periodo_biometricos_form').submit(function(){
        var valida=false;
        if($('.check').is(':checked') ){
            valida = true;
           /*  $('#exportarPModal .guardar').attr('disabled', true).text('Espere...'); */
            $('#exportarPModal select, #exportarPModal input').attr('disabled', false);
            $('#exportarPModal').modal('hide');
        }else{            
           
            swal("Algo salio mal", "Seleccione al menos un departamento", "warning");
        }
        return valida;
        
    });
});
</script>