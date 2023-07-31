<div class="modal" tabindex="-1" role="dialog" id="cerrarPeriodoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Cerrar Periódo de Nomina</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">
            
            <div class="d-flex">
                <a  id="cerrar_btn" class="btn button-style btnCerrar center">Cerrar periodo</a>

                <form method="post" id="nomina_cerrar_form" action="{{ route('nomina.cerrarperiodo') }}" enctype="multipart/form-data">
                     @csrf
                    <input type="hidden" name="idPeriodo" id="cerrar_nomina">
                </form>
            </div>
                        
        </div>
        </div>
    </div>
</div>



<script>
$(function(){


    $('.btnCerrar').click(function(){
        $('.btnCerrar').addClass('disabled', true);
        $(this).text('Espere...');
    });
});

    $("#cerrar_btn").click(function(){
        document.getElementById("nomina_cerrar_form").submit();
    });

</script>