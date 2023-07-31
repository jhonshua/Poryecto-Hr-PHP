<div class="modal" tabindex="-1" role="dialog" id="verEvidenciaCovid">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Evidencia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row pb-2">
                <div class="col-md-12 mt-3">
                    <iframe src="" frameborder="0" width="100%" height="600px" id="documento"></iframe>
                    
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(function(){

        $('#verEvidenciaCovid').on('shown.bs.modal',function(e){
            var url = $(e.relatedTarget).data('url');
            $('#verEvidenciaCovid .modal-body #documento').attr('src',url);
        });

    });
</script>