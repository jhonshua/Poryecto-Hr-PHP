<div class="modal" tabindex="-1" role="dialog" id="prestacionModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> prestación</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="prestaciones_form" action="{{route('parametria.prestacion')}}">
                @csrf
                <label for="">Antigüedad:</label>
                <select name="antiguedad" id="antiguedad" class="form-control  select-clase" required>
                    @for ($i = 0; $i < 51; $i++)
                        {{-- @if (! $prestaciones->contains('antiguedad', $i)) --}}
                            <option value="{{$i}}">{{$i}} años</option>
                        {{-- @endif --}}
                    @endfor
                </select>

                <label for="">Vacaciones:</label>
                <select name="vacaciones" id="vacaciones" class="form-control  select-clase" required>
                    @for ($i = 0; $i < 20; $i++)
                        <option value="{{$i}}">{{$i}} días</option>
                    @endfor
                </select>

                <label for="">% de prima vacacional:</label>
                <select name="prima_vacacional" id="prima_vacacional" class="form-control  select-clase" required>
                    @for ($i = 0; $i < 51; $i++)
                        <option value="{{$i}}">{{$i}}%</option>
                    @endfor
                </select>

                <label for="">Aguinaldo:</label>
                <select name="aguinaldo" id="aguinaldo" class="form-control  select-clase" required>
                    @for ($i = 0; $i < 46; $i++)
                        <option value="{{$i}}">{{$i}} días</option>
                    @endfor
                </select>


                <hr>


                <label for="">Aguinaldo:</label>
                <select name="bono_aguinaldo" id="bono_aguinaldo" class="form-control  select-clase" required>
                    @for ($i = 0; $i < 46; $i++)
                        <option value="{{$i}}">{{$i}} días</option>
                    @endfor
                </select>


                <label for="">Vacaciones:</label>
                <select name="bono_vacaciones" id="bono_vacaciones" class="form-control  select-clase" required>
                    @for ($i = 0; $i < 20; $i++)
                        <option value="{{$i}}">{{$i}} días</option>
                    @endfor
                </select>

                <label for="">% de prima vacacional:</label>
                <select name="bono_prima_vacacional" id="bono_prima_vacacional" class="form-control  select-clase" required>
                    @for ($i = 0; $i < 51; $i++)
                        <option value="{{$i}}">{{$i}}%</option>
                    @endfor
                </select>


                <div class="row justify-content-center"> 
                    <div class="row justify-content-center col-xs-12 col-md-12 col-lg-2">                        
                        <button type="submit" class="button-style mt-4 guardar btn-block">Guardar</button>
                    </div> 
                </div> 
                <input type="hidden" name="id" id="id" value="" required>
                <input type="hidden" name="id_categoria" id="id_categoria" value="{{$prestacion->id}}" required>
            </form>
        </div>
        </div>
    </div>
</div>


<script>
$(function(){

    // al abrir el modal cargamos las prestaciones
    $('#prestacionModal').on('shown.bs.modal', function (e) {
        $("#prestacionModal .modal-body #antiguedad  option").show();
        var idPrestacion = $(e.relatedTarget).data('id');
        if(idPrestacion == ''){
            accionLabel = 'Crear';
            var antiguedad = 0;
            var vacaciones = 0;
            var prima_vacacional = 0;
            var aguinaldo = 0;
            var bono_vacaciones = 0;
            var bono_prima_vacacional = 0;
            var bono_aguinaldo = 0;

        } else {
            accionLabel = 'Editar';
            var antiguedad = $(e.relatedTarget).data('antiguedad');
            var vacaciones = $(e.relatedTarget).data('vacaciones');
            var prima_vacacional = $(e.relatedTarget).data('prima_vacacional');
            var aguinaldo = $(e.relatedTarget).data('aguinaldo');
            var bono_vacaciones = $(e.relatedTarget).data('bvacaciones');
            var bono_prima_vacacional = $(e.relatedTarget).data('bprima_vacacional');
            var bono_aguinaldo = $(e.relatedTarget).data('baguinaldo');
        }

        $('#prestacionModal .modal-title span').text(accionLabel);
        $('#prestacionModal .modal-body #id').val(idPrestacion);
        $('#prestacionModal .modal-body #antiguedad').val(antiguedad);
        $('#prestacionModal .modal-body #vacaciones').val(vacaciones);
        $('#prestacionModal .modal-body #prima_vacacional').val(prima_vacacional);
        $('#prestacionModal .modal-body #aguinaldo').val(aguinaldo);
        $('#prestacionModal .modal-body #bono_vacaciones').val(bono_vacaciones);
        $('#prestacionModal .modal-body #bono_prima_vacacional').val(bono_prima_vacacional);
        $('#prestacionModal .modal-body #bono_aguinaldo').val(bono_aguinaldo);

        // ocultamos las opciones que no se pueden escoger (porque ya existen)
        $('.prestaciones tbody tr').each(function(index){
            ant = $(this).data('antiguedad');
            $("#prestacionModal .modal-body #antiguedad  option[value='"+ant+"']").hide();
        });
    });

    // boton guardar
    $('#prestacionModal .guardar').click(function(){
        $(this).attr('disabled', true);
        $(this).val('Espere...');
        $('#prestaciones_form').submit();
    });
});
</script>

