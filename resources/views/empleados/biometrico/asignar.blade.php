<div class="modal" tabindex="-1" role="dialog" id="modalAsignar">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Empleado a Biométrico</h5>
            </div>
            <div class="modal-body row pb-5">
                <div class="col-md-12 text-center">
                    <br>
                    <SELECT class="input-style center mb-3 select-clase" id="select_biometricos" name="select_biometrico">
                        <option value="">Biométrico</option>
                        @foreach ($biometricos as $bio)
                    <option value="{{ $bio->id}}" data-ip="{{ $bio->ip}}" data-puerto="{{ $bio->puerto}}"> {{ $bio->nombre }}</option>
                        @endforeach
                    </SELECT>


                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <a href="#" data-dismiss="modal" class="btn button-style-gray ml-2">Cancelar</a>
                            <button class="btn button-style  ml-2" id="add_guardar">Guardar</button>
                        </div>
                    </div>

                </div>



            </div>
        </div>
    </div>
</div>


<style>
    .nombreempleado{
        font-size: 20px;
        color: brown;
    }
</style>


<script>

    $("#add_guardar").click(function(){
        var bio = $('#select_biometricos :selected');
        var idBio = bio.val();
        var ip = bio.data('ip');
        var puerto = bio.data('puerto');
        var nombre = "{{$empleado->nombre}} {{ $empleado->apaterno }} {{ $empleado->amaterno}}";
        var id = "{{$empleado->id}}";
        nombre = (nombre.length > 23) ? nombre.substr(0,23): nombre;

        $.ajax({
            type: 'POST',
            url: 'http://wsbiometrics.ddns.net/biometricos/usuario/registrar',
            data: {ip: ip,puerto: puerto,id : id, nombre : nombre, password :'123456', rol:'0'},
            dataType: "json",    
            beforeSend: function() {    
                console.log(this.data);
            },            
        }).then( function(resp){  

            console.log(resp);                   
            datos = (resp.respuesta)?JSON.parse(resp.respuesta):"";                   
            if(resp.error && datos == ""){
                 err=resp.error;
                // alertify.error(resp.error);
                swal("Algo salio mal", "Vuelve a intentarlo", "warning");
            }else{

                $.ajax({
                    type: 'POST',
                    url: '{{ route('biometrico.asignar') }}',
                    data: {id_biometrico: idBio,id_empleado : id,  _token: '{{csrf_token()}}'},
                    dataType: "json",    
                }).then( function(resp2){             
                    console.log(resp2);
                    datos2 = (resp2.respuesta)?JSON.parse(resp2.respuesta):"";
                        if(resp2.error && datos2 == ""){
                            // alertify.error(resp2.error);
                            swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                        }else{
                            // alertify.success('Se asigno con exito el empleado');
                            swal("Se asigno con exito el empleado", "success");
                        }
                }).fail(function(ee) {
                    console.log(ee);
                    swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                            
                });
            }
        }).fail(function(e) {
                
            console.log(e);
            swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                                       
        }).always(function(resp){
            // $('#spinner').addClass('ocultar');
            $('#modalasignar').hide();
            $('.modal-backdrop').remove()
        });
    });



</script>
