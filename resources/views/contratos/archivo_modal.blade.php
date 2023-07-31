<div class="modal" tabindex="-1" role="dialog" id="archivoModal" aria-labelledby="exampleModalScrollableTitle">
    <div class="modal-dialog modal-dialog-centered modal-xl " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span></span> Empresa Receptora</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">

{{--                 <form method="post" id="archivo_form" action="{{route('herramientas.empresasreceptoras.crearEditar')}}" class="row">
                    @csrf --}}

                    <div class="col-md-12" id="archivo_contenedor"></div>
                    <div class="col-md-12" id="pdf"></div>
                    <div class="col-md-12" id="editar"></div>
                    <input type="hidden" name="contrato_editar" id="contrato_editar" value="">

                {{-- </form> --}}
            </div>
        </div>
    </div>
</div>


<script>


 $(".pdf-geturl").click(function(){
    var id= $(this).data("id");
    var ruta_pdf = $(this).data("pdf-url");
    pdf(ruta_pdf);

    $("#archivo_contenedor").html('<input type="hidden" value="'+id+'" name="idcontrato" id="idcontrato"/><input type="hidden" value="'+ruta_pdf+'" name="archivo_contrato" id="archivo_contrato"/><br/>');
    $("#editar").html('').hide();

   console.log(ruta_pdf);
 });


function pdf(ruta){

    $("#pdf").html('<iframe src="'+ruta+'" width="100%" height="525"></iframe>');
    $("#pdf").slideDown("slow");
}

</script>
