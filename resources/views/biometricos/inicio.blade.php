<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<div class="container">
<div class="row mt-5" id="crudBios">
    <div class="col-lg-12 col-md-12">
        <div class="table-responsive">
        <span data-toggle="tooltip" data-html="true" data-placement="right" title="Nuevo Biometrico">
            <a href="#" class="font-weight-bold revisar btn btn-warning" data-toggle="modal" data-target="#modalCrear">
                <i class="fa fa-plus"></i> Agregar
            </a>
        </span>
        <span data-toggle="tooltip" data-html="true" data-placement="right" title="Sincronizar Biometricos">
            <a href="#" class="font-weight-bold revisar btn btn-warning">
                <i class="fa fa-sync"></i> Sincronizar
            </a>
        </span>
                <table class="table table-striped biometricos">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Modelo</th>
                            <th>IP</th>
                            <th>Puerto</th>
                            <th>MAC</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in biometricos">
                            <td>@{{ d.id }} <i class="fas fa-star" v-if="d.principal == 1"></i></td>
                            <td>@{{ d.nombre }}</td>
                            <td>@{{ d.modelo }}</td>
                            <td>@{{ d.ip }}</td>
                            <td>@{{ d.puerto }}</td>
                            <td>@{{ d.mac }}</td>
                            <td>
                                <span class="badge badge-success"  v-if="d.estatus == 1">Activo</span>
                                <span class="badge badge-danger" v-else>Desactivado</span>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm cancelar" alt="Borrar Biometrico" title="Borrar biometrico"  v-on:click.prevent="deleteBio(d)"><i class="fas fa-trash"></i></button>
                                <button class="btn btn-warning btn-sm vern" alt="Ver Biometrico" title="Ver Biometrico" v-on:click="verBio(d)"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
        </div>
    </div>
    @include('biometricos.ver')
    @include('biometricos.agregar')
</div>
</div>
<script>

    var urlBase ="https://hrsystemsync.com.mx/biometricos/";
    new Vue({
    el: '#crudBios',
    created: function() {
        this.getBios();
    },
    data: {
        biometricos: [],
        newBio: { 'nombre': '' ,'ip': '', 'puerto': '', 'ok' : false },
        fillBio: { 'id': '', 'nombre': '' ,'ip': '', 'puerto': '','modelo':'','firmware':'','mac':'','plataforma':'','num_serie':'',
                   'proveedor':'', 'ok': false },
        errors: [],
    },

    methods: {
        borrar(){
            this.newBio = { 'nombre': '' ,'ip': '', 'puerto': '', 'ok' : false };
            this.fillBio = { 'id': '', 'nombre': '' ,'ip': '', 'puerto': '', 'ok': false },
            this.errors = [];
        },
        getBios() {
            var url = "{{route('sistema.biometricos.getbiometricos')}}";
            axios.get(url).then(response => {
                this.biometricos = response.data;
            });
        },
        checarBio(){
            var url = urlBase + 'datos/' + this.newBio.ip + "/" + this.newBio.puerto;
            this.errors = [];
            axios.get(url).then(response => {
                if(response.data.exito){
                    this.newBio.modelo      = response.data.datos.dispositivo;
                    this.newBio.firmware    = response.data.datos.firmware;
                    this.newBio.mac         = response.data.datos.mac;
                    this.newBio.plataforma  = response.data.datos.plataforma;
                    this.newBio.num_serie   = response.data.datos.serie;
                    this.newBio.proveedor   = response.data.datos.vendor;
                    this.newBio.estatus     = 1;
                    //this.newBio.proveedor = response.data.datos.;
                    this.newBio.ok=true;
                }else{
                    alertify.error('Error de conexión con el dispositivo');
                    this.errors.push("Error de conexión con el dispositivo");
                }

            });
        },
        createBio: function() {
            var url = "{{route('sistema.biometricos.crear')}}";
            axios.post(url, {
                'nombre' : this.newBio.nombre,
                'ip' : this.newBio.ip,
                'puerto' : this.newBio.puerto,
                'modelo' : this.newBio.modelo,
                'firmware' : this.newBio.firmware,
                'mac': this.newBio.mac,
                'plataforma': this.newBio.plataforma,
                'num_serie' : this.newBio.num_serie,
                'proveedor':this.newBio.proveedor,
                'estatus': this.newBio.estatus,
            }).then(response => {
                this.getBios();
                this.borrar();
                alertify.success('Biometrico agregado correctamente');
                $('#modalCrear').modal('hide');
                $('.modal-backdrop').remove()
            }).catch(error => {
                this.newBio.ok = false;
                this.errors = error.response.data;
            });
        },
        deleteBio: function(bio) {
            var url = 'biometricos/' + bio.id;
            alertify.confirm('¿Borrar el biometrico?', function(){
                axios.delete(url).then(response => {
                    alertify.success('Se elimino el biometrico');
                    window.location.reload();
                }).catch(error => {
                    alertify.error('El Biometrico no se pudo eliminar, intente de nuevo');
                });
            });
        },
        verBio: function(bio){
            this.fillBio.id = bio.id;
            this.fillBio.nombre = bio.nombre;
            this.fillBio.ip = bio.ip;
            this.fillBio.puerto = bio.puerto;
            this.fillBio.modelo = bio.modelo;
            this.fillBio.firmware = bio.firmware;
            this.fillBio.mac = bio.mac;
            this.fillBio.plataforma= bio.plataforma;
            this.fillBio.num_serie = bio.num_serie;
            this.fillBio.proveedor=bio.proveedor;
            this.fillBio.estatus= bio.estatus;
            $('#modalVer').modal('show');
        }
    }
});
</script>
