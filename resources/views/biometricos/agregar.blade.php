<div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-labelledby="create" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar Biometrico</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" v-on:submit.prevent="createBio">
                <div class="modal-body">
                    <div class="col-xs-6">
                        <div class="form-group ">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" v-model="newBio.nombre">
                        </div>
                        <div class="form-group ">
                            <label>IP</label>
                            <input type="text" name="ip" class="form-control" v-model="newBio.ip">
                        </div>
                        <div class="form-group ">
                            <label>Puerto</label>
                            <input type="text" name="puerto" class="form-control" v-model="newBio.puerto">
                        </div>
                        <span v-for="error in errors" class="text-danger">@{{ error }}</span>
                    </div>
                    <div class="col-xs-6" v-if="newBio.ok">
                        <p><b>Dispositivo: </b> @{{ newBio.modelo}}</p>
                        <p><b>Firmaware: </b> @{{ newBio.firmware}}</p>
                        <p><b>Mac: </b> @{{ newBio.mac}}</p>
                        <p><b>Plataforma: </b> @{{ newBio.plataforma}}</p>
                        <p><b># Serie: </b> @{{ newBio.num_serie}}</p>
                        <p><b>Proveedor: </b> @{{ newBio.proveedor}}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-primary" value="Guardar" v-if="newBio.ok">
                    <input type="button" class="btn btn-primary" value="Checar" v-on:click.prevent="checarBio()"
                           v-if="!newBio.ok">
                </div>
            </form>
        </div>
    </div>
</div>