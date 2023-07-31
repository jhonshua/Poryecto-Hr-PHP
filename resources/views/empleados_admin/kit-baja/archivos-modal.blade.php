<div class="modal" tabindex="-1" role="dialog" id="archivosModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archivos para el Kit de Baja del Empleado</h5>
            </div>
            {{-- {{route('empleados_bck.kitBajaEmpleado.subirArchivos')}} --}}
            <form method="post" action="{{ route('empleados.kitbajaempleadosubirArchivos') }}" class="permisoForm mt-3 container-fluid" enctype="multipart/form-data">
                <div class="text-danger mb-2">* Obligatorio</div>
                <div class="modal-body row pb-2">
                    @csrf
                    
                    @foreach ($kitBajaCampos as $campo)
                        <div class="col-md-6 mt-1">
                            <label for="">{{$campo->alias}}: {!! ($campo->obligatorio == 1) ? '<span class="text-danger">*</span>' : '' !!}</label>
                            <input type="file" name="{{$campo->nombre_campo}}" id="{{$campo->nombre_campo}}" class="form-control mb-4" value="">
                            <a href="" id="file_{{$campo->nombre_campo}}" class="mb-3" target="_blank"></a>
                        </div>
                    @endforeach
                                        
                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                    <input type="hidden" name="id_empleado" id="id_empleado" value="">
                    <input type="hidden" name="return_id" id="return_id" value="">
                    <button type="button" data-dismiss="modal" class="btn button-style-cancel  mb-3 regresar">CANCELAR</button>
                    <button type="submit" class="btn button-style  mb-3 continuar" >GUARDAR</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
// $(function(){

    $('#archivosModal').on('shown.bs.modal', function (e) {

        var id_empleado = $(e.relatedTarget).data('id_empleado');
        document.getElementById("id_empleado").value = id_empleado;
        $('#archivosModal .modal-body #id_empleado').val(id_empleado);

        var return_id = $(e.relatedTarget).data('return_id');
        document.getElementById("return_id").value = return_id;
        $('#archivosModal .modal-body #return_id').val(return_id);

        var kitBajaUrl = "{{asset('storage/repositorio/'. Session::get('empresa')['id'] . '/$empleado_id/kitBaja/')}}";
        kitBajaUrl = kitBajaUrl.replace('$empleado_id', id_empleado);

        var data = $(e.relatedTarget).data();
        var keys = Object.keys(data);
        var archivos = $.map(keys, function(v) {
            if (v.indexOf('file') >= 0) { return v; }
        })
        console.log(archivos);
        archivos.forEach(arch => {
            $('#'+arch).attr('href', kitBajaUrl+'/'+data[arch]);
            $('#'+arch).text(data[arch]);
        });
        
    });

    $('#archivosModal .permisoForm').submit(function(){
        $('#archivosModal .permisoForm .btn.continuar').prop('disabled', true).text('ESPERE...');
    });
    
// });
</script>
