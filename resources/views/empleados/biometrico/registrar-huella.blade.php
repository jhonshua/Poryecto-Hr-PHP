<div class="modal" tabindex="-1" role="dialog" id="modalRegistrarH">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Huella en Biometrico</h5>
            </div>
            <div class="modal-body row pb-5">
                <div class="col-md-12 center">
                    <label class="mb-0">Biometricos:</label>
                    <SELECT class="input-style mb-3 select-clase" id="select_biometricos2" name="select_biometrico2">
                        <option value="">SELECCIONAR</option>
                       @if ($biometricos2->count() > 1)
                       <option value="0">Todos</option>   
                       @endif                        
                        @foreach ($biometricos2 as $bio)
                            <option value="{{ $bio->id}}" data-ip="{{ $bio->ip}}" data-puerto="{{ $bio->puerto}}"> {{ $bio->nombre }}</option>
                        @endforeach
                    </SELECT>
                    
                </div>
                <div class="col-md-12 mt-4 text-center">
                    <a href="#" data-dismiss="modal" class="btn button-style-gray">Cancelar</a>
                    <button class="btn button-style font-weight-bold registrar ml-2">Registrar</button>
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

<script type="text/javascript">
    var huella;


    
    $('.registrar').click(function(){
        
        var bio = $('#select_biometricos2 :selected');
        var idBio = bio.val();
        var ip = bio.data('ip');
        var puerto = bio.data('puerto');
        var id = "{{$empleado->id}}";

        $.ajax({
            type: 'POST',
            url: 'https://hrsystemsync.com.mx/biometricos/usuario/huella',
            data: {ip: ip,puerto: puerto,id : id, huella: huella},
            dataType: "json",  
            beforeSend: function() {
                $('#spinner').removeClass('ocultar'), // Le quito la clase que oculta mi animación 
                console.log(this.data);
            },            
        }).then( function(resp){  

            console.log(resp);                 
            datos = (resp.respuesta)?JSON.parse(resp.respuesta):"";                   
            if(resp.error && datos == ""){
                err=resp.error;
                swal("Algo salio mal", "Vuelve a intentarlo", "warning");
            }else{

                $.ajax({
                    type: 'POST', 
                    url: '{{ route('biometrico.agregarhuella') }}',
                    data: {id_empleado : id,indice : huella, huella: resp.datos.strHuella ,  _token: '{{csrf_token()}}'},
                    dataType: "json",    
                }).then( function(resp2){             
                    console.log(resp2);
                    datos2 = (resp2.respuesta)?JSON.parse(resp2.respuesta):"";
                    if(resp2.error && datos2 == ""){
                        swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                    }else{
                        swal("Se registro con exito la huella", "success");
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

            $('#spinner').addClass('ocultar');
            $('#modalasignar').hide();
            $('.modal-backdrop').remove()
        });
    });
</script>
