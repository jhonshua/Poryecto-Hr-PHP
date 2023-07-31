<div class="modal" tabindex="-1" role="dialog" id="horarioModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> Horario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="horarios_form" action="{{ route('herramientas.creareditar') }}">
                @csrf

                <table>
                    <tr>
                        <td colspan="2">
                            <input type="text" name="alias" id="alias" class="form-control mb-3" required min="0" placeholder="Nombre/Alias del horario">
                        </td>
                    </tr>
                    <tr>
                        <td width="50%"><label for="">Entrada:</label><br>
                            <input type="time" name="entrada" id="entrada" required  value="08:00:00" step="1" class="form-control mb-3" >
                        </td>
                        <td><label for="">Salida:</label><br>
                            <input type="time" name="salida" id="salida" required value="08:00:00" step="1" class="form-control mb-3" >
                        </td>
                    </tr>
                    <tr>
                        <td><label for="">Numero de retardos que aplican para falta:</label></td>
                        <td>
                            <select name="retardos" id="retardos" class="form-control mb-3">
                                @for ($i = 0; $i < 11; $i++)
                                    <option value="{{$i}}">{{$i}}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="">Minutos de tolerancia :</label></td>
                        <td>
                            <select name="tolerancia" id="tolerancia"  class="form-control mb-3">
                                @for ($i = 0; $i <= 6; $i++)
                                    <option value="{{$i*5}}">{{$i*5}}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label for="comida">Horario de comida:</label>
                            <input type="checkbox" name="comida" id="comida" onchange="$('tr.comida').toggleClass('d-none');" value="1">
                        </td>
                    </tr>
                    <tr class="comida d-none">
                        <td><label for="">Entrada:</label><br>
                            <input type="time" name="entrada_comida" id="entrada_comida"  value="08:00:00" step="1" class="form-control mb-3" >
                        </td>
                        <td><label for="">Salida:</label><br>
                            <input type="time" name="salida_comida" id="salida_comida" value="08:00:00" step="1" class="form-control mb-3" >
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="mt-5">
                            <hr>
                            <strong>Días laborables:</strong> <br>
                            <table width="100%" class="dias">
                                <tr>
                                    <td class="text-center">L <br>
                                        <input type="checkbox" name="lunes" id="lunes" value="1">
                                    </td>
                                    <td class="text-center">M <br>
                                        <input type="checkbox" name="martes" id="martes" value="1"></td>
                                    <td class="text-center">M <br>
                                        <input type="checkbox" name="miercoles" id="miercoles" value="1"></td>
                                    <td class="text-center">J <br>
                                        <input type="checkbox" name="jueves" id="jueves" value="1"></td>
                                    <td class="text-center">V <br>
                                        <input type="checkbox" name="viernes" id="viernes" value="1"></td>
                                    <td class="text-center">S <br>
                                        <input type="checkbox" name="sabado" id="sabado" value="1"></td>
                                    <td class="text-center">D <br>
                                        <input type="checkbox" name="domingo" id="domingo" value="1"></td>
                                    <td class="text-center">Indefinido <br>
                                        <input type="checkbox" name="indefinido" id="indefinido" value="1"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="id" id="id" value="">
                <div class="btn btn-warning guardar mt-4">Guardar</div>
            </form>
        </div>
        </div>
    </div>
</div>


<script>
$(function(){

    $('#indefinido').click(function(){
        $('.dias input[type=checkbox]').not('#indefinido').prop('checked', false);
    });

    // al abrir el modal cargamos los datos
    $('#horarioModal').on('shown.bs.modal', function (e) {
        var idHorario = $(e.relatedTarget).data('id');
        if(idHorario == ''){
            accionLabel = 'Crear';
            var alias = '';
            var entrada = salida = tolerancia =  retardos = comida = lunes = martes = miercoles = jueves = viernes= sabado = domingo = indefinido = estatus = entrada_comida = salida_comida = sabado_entrada = sabado_salida = domingo_entrada = domingo_salida = 0;
        } else {
            accionLabel = 'Editar';
            var alias = $(e.relatedTarget).data('alias');
            var entrada = $(e.relatedTarget).data('entrada');
            var salida = $(e.relatedTarget).data('salida');
            var tolerancia = $(e.relatedTarget).data('tolerancia');
            var retardos = $(e.relatedTarget).data('retardos');
            var comida = $(e.relatedTarget).data('comida');
            var lunes = $(e.relatedTarget).data('lunes');
            var martes = $(e.relatedTarget).data('martes');
            var miercoles = $(e.relatedTarget).data('miercoles');
            var jueves = $(e.relatedTarget).data('jueves');
            var viernes = $(e.relatedTarget).data('viernes');
            var sabado = $(e.relatedTarget).data('sabado');
            var domingo = $(e.relatedTarget).data('domingo');
            var indefinido = $(e.relatedTarget).data('indefinido');
            var entrada_comida = $(e.relatedTarget).data('entrada_comida');
            var salida_comida = $(e.relatedTarget).data('salida_comida');
            var sabado_entrada = $(e.relatedTarget).data('sabado_entrada');
            var sabado_salida = $(e.relatedTarget).data('sabado_salida');
            var domingo_entrada = $(e.relatedTarget).data('domingo_entrada');
            var domingo_salida = $(e.relatedTarget).data('domingo_salida');
        }

        $('#horarioModal .modal-title span').text(accionLabel);
        $('#horarioModal .modal-body #id').val(idHorario);
        $('#horarioModal .modal-body #alias').val(alias);
        $('#horarioModal .modal-body #entrada').val(entrada);
        $('#horarioModal .modal-body #salida').val(salida);
        $('#horarioModal .modal-body #tolerancia').val(tolerancia);
        $('#horarioModal .modal-body #retardos').val(retardos);
        $('#horarioModal .modal-body #comida').prop('checked', comida);
        $('#horarioModal .modal-body #lunes').prop('checked', lunes);
        $('#horarioModal .modal-body #martes').prop('checked', martes);
        $('#horarioModal .modal-body #miercoles').prop('checked', miercoles);
        $('#horarioModal .modal-body #jueves').prop('checked', jueves);
        $('#horarioModal .modal-body #viernes').prop('checked', viernes);
        $('#horarioModal .modal-body #sabado').prop('checked', sabado);
        $('#horarioModal .modal-body #domingo').prop('checked', domingo);
        $('#horarioModal .modal-body #indefinido').prop('checked', indefinido);
        $('#horarioModal .modal-body #entrada_comida').val(entrada_comida);
        $('#horarioModal .modal-body #salida_comida').val(salida_comida);
        $('#horarioModal .modal-body #sabado_entrada').val(sabado_entrada);
        $('#horarioModal .modal-body #sabado_salida').val(sabado_salida);
        $('#horarioModal .modal-body #domingo_entrada').val(domingo_entrada);
        $('#horarioModal .modal-body #domingo_salida').val(domingo_salida);

        if(comida){
            $('tr.comida').removeClass('d-none');
        }
    });

    // boton guardar
    $('#horarioModal .guardar').click(function(){
        $(this).attr('disabled', true);
        $(this).val('Espere...');
        $('#horarios_form').submit();
    });
});
</script>