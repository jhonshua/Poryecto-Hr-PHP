<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar datos para aguinaldos</h5>
            </div>
            <div class="modal-body row pb-2">
                <div class="text-center col-md-12 mt-3">                    
                    <form action="{{route('procesos.exportar.aguinaldo')}}" method="POST" target="_blank">
                        @csrf
                        <button type="submit" class="button-style mr-3">Ver el layout de ejemplo</button>
                        @foreach ($deptos as $depto)
                            <input type="hidden" name="deptos[]" value="{{$depto}}">
                        @endforeach
                        <input type="hidden" name="impuestoanual" value="{{$impuestoanual}}">
                        <input type="hidden" name="ejercicio" value="{{$ejercicio}}">
                    </form>
                    <HR>
                </div>

                <div class="col-md-12">
                    Por favor no edite el "orden"  ni agregue otras columnas. <br>
                    Tampoco use simbolos como $, o fechas formateadas. (Conserve el formato de exportación)
                    <form method="post" action="{{route('procesos.importar.aguinaldo')}}" class="importarForm mt-3" enctype="multipart/form-data">
                        @csrf
                        <label for="">Subir archivo:</label>
                        <!-- <input type="file" name="archivo-empleados" id="archivo-empleados" required class="form-control mb-3" accept=".xls, .xlsx"> -->
                        <div class="custom-file ">
                            <input type="file"name="archivo-empleados" id="archivo-empleados" onchange="file(event)" required accept=".xlsx, .xls" class="custom-file-input" required/>
                            <label class="custom-file-label" id="archivo_text">Seleccionar Archivo</label>
                        </div> 
                        @foreach ($deptos as $depto)
                            <input type="hidden" name="deptos[]" value="{{$depto}}">
                        @endforeach
                        <input type="hidden" name="impuestoanual" value="{{$impuestoanual}}">
                        <input type="hidden" name="ejercicio" value="{{$ejercicio}}">
                        <div class="">
                            <div class="row mt-3 d-flex justify-content-center">
                                <button type="button" data-dismiss="modal" class="button-style-cancel my-4 mx-1 regresar">CANCELAR</button>
                                <button type="submit" class="button-style my-4 mx-1 guardar">CONTINUAR</button>
                            </div>                            
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function file(val){       
    let text = val;        
    $('#archivo_text').html(val.target.files[0].name); 
}
$(function(){
    $('#importarModal .importarForm').submit(function(){
        $('#importarModal .guardar').attr('disabled', true).text('ESPERE...');
    })
});
</script>

