<div class="modal fade" id="modalVer" tabindex="-1" role="dialog" aria-labelledby="create" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Biometrico</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" v-on:submit.prevent="createBio">
                <div class="modal-body">
                    <div class="col-xs-6">
                        <p><b>ID: </b> @{{ fillBio.id}}</p>
                        <p><b>Nombre: </b> @{{ fillBio.nombre}}</p>
                        <p><b>IP: </b> @{{ fillBio.ip}}</p>
                        <p><b>Puerto: </b> @{{ fillBio.puerto}}</p>
                        <p><b>Mac: </b> @{{ fillBio.mac}}</p>
                        <p><b>modelo: </b> @{{ fillBio.modelo}}</p>
                        <p><b>Firmaware: </b> @{{ fillBio.firmware}}</p>
                        <p><b>Plataforma: </b> @{{ fillBio.plataforma}}</p>
                        <p><b># Serie: </b> @{{ fillBio.num_serie}}</p>
                        <p><b>Proveedor: </b> @{{ fillBio.proveedor}}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <input  type="button" class="btn btn-primary" data-dismiss="modal" value="Cancela">
                </div>
            </form>
        </div>

    </div>
</div>