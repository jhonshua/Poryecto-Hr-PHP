<div class="modal" tabindex="-1" role="dialog" id="firmaFiniquitoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Firma finiquito</h5>
            </div>
            <div class="modal-body row pb-5">
                <div class="col-md-12 text-center">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mt-3"></i>
                    <div class="nombreempleado font-weight-bold my-3"></div>
                </div>
                <div class="col-md-12 mt-3">

                    <form method="post" action="{{route('procesos.calculofiniquitofirma')}}" class="firmaFiniquitoForm">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <input type="hidden" name="idperiodo" id="idperiodo">
                        <input type="hidden" name="ejercicio" id="ejercicio">

                        <div class="col-md-6 offset-3 d-flex formulario">
                            <center>
                                <div class="form-check-inline radio">
                                    <input class="form-check-input" type="radio" name="firma" id="sifirmo" value="1" checked>
                                    <label class="form-check-label" for="sifirmo">
                                        SI
                                    </label>
                                </div>
                                <div class="form-check-inline radio">
                                    <input class="form-check-input" type="radio" name="firma" id="nofirmo" value="2">
                                    <label class="form-check-label" for="nofirmo">
                                        NO
                                    </label>
                                </div>
                            </center>
                        </div>
                        
                        <hr>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel  mb-3">Cancelar</a>
                                <button type="submit" class="btn button-style mb-3 guardar">Guardado</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="{{ asset('public/css/radios_check.css') }}" rel="stylesheet">

<style>
    .nombreempleado{
        font-size: 20px;
        color: brown;
    }
</style>

<script>
$(function(){

   // $('.firmaFiniquitoForm').validate();

    // al abrir el modal cargamos las prestaciones
    $('#firmaFiniquitoModal').on('shown.bs.modal', function (e) {
        var nombreempleado = $(e.relatedTarget).data('nombreempleado');
        $('#firmaFiniquitoModal .modal-body .nombreempleado').text('¿El empleado ' + nombreempleado + " firmó el finiquito?");

      

        var id = $(e.relatedTarget).data('id');
        $('#firmaFiniquitoModal .modal-body #id').val(id);

        var idperiodo = $(e.relatedTarget).data('idperiodo');
        $('#firmaFiniquitoModal .modal-body #idperiodo').val(idperiodo);

        var ejercicio = $(e.relatedTarget).data('ejercicio');
        $('#firmaFiniquitoModal .modal-body #ejercicio').val(ejercicio);

     

        
    });


    $('.firmaFiniquitoForm').submit(function(){
        $('#firmaFiniquitoModal .modal-body .btn.guardar').attr('disabled', true).text('ESPERE...');
        //return false;
    });
});
</script>
