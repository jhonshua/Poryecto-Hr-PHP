<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')



<div class="container">
	@include('includes.header',['title'=>'Horarios para empleados', 'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'herramientas.horarios'])

	<div class="article border mt-2">
		<div class="row">
			<div class="col-md-7">

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
                            <input type="time" name="entrada" id="entrada" required   step="1" class="form-control mb-3" >
                        </td>
                        <td><label for="">Salida:</label><br>
                            <input type="time" name="salida" id="salida" required  step="1" class="form-control mb-3" >
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
                            <input type="time" name="entrada_comida" id="entrada_comida"   step="1" class="form-control mb-3" >
                        </td>
                        <td><label for="">Salida:</label><br>
                            <input type="time" name="salida_comida" id="salida_comida"  step="1" class="form-control mb-3" >
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="mt-5">
                            <hr>
                            <strong>Días laborables:</strong> <br>
                            <table width="100%" class="dias">
                                <tr>
                                    <td class="text-center">L <br>
                                        <input type="checkbox" name="lunes" id="lunes" value="1" onchange="ver_dia(1)">
                                    </td>
                                    <td class="text-center">M <br>
                                        <input type="checkbox" name="martes" id="martes" value="1" onchange="ver_dia(2)"></td>
                                    <td class="text-center">M <br>
                                        <input type="checkbox" name="miercoles" id="miercoles" value="1" onchange="ver_dia(3)"></td>
                                    <td class="text-center">J <br>
                                        <input type="checkbox" name="jueves" id="jueves" value="1" onchange="ver_dia(4)"></td>
                                    <td class="text-center">V <br>
                                        <input type="checkbox" name="viernes" id="viernes" value="1" onchange="ver_dia(5)"></td>
                                    <td class="text-center">S <br>
                                        <input type="checkbox" name="sabado" id="sabado" value="1" onchange="ver_dia(6)"></td>
                                    <td class="text-center">D <br>
                                        <input type="checkbox" name="domingo" id="domingo" value="1" onchange="ver_dia(7)"></td>
                                    <td class="text-center">Indefinido <br>
                                        <input type="checkbox" name="indefinido" id="indefinido" value="1"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="id" id="id" value="">
                <div class="row">
                	<div class="col-md-12 text-center">
                		<div class="btn button-style guardar mt-4 ">Guardar</div>
                	</div>
                </div>
			</div>
			<div class="col-md-5">
			    <div class="col-12 d-none" id="dia1">
			        <strong>Lunes:</strong> <br>
			        <div class="row">
			            <div class="col-6">
			                Entrada <br>
			                <input type="time" name="lunes_entrada" id="entrada_1"  step="1" class="form-control mb-3" >
			            </div>
			            <div class="col-6">
			                Salida <br>
			                <input type="time" name="lunes_salida" id="salida_1"  step="1" class="form-control mb-3" >
			            </div>
			        </div>
			    </div>
			    <div class="col-12 d-none" id="dia2">
			        <strong>Martes:</strong> <br>
			        <div class="row">
			            <div class="col-6">
			                Entrada <br>
			                <input type="time" name="martes_entrada" id="entrada_2"    step="1" class="form-control mb-3" >
			            </div>
			            <div class="col-6">
			                Salida <br>
			                <input type="time" name="martes_salida" id="salida_2"    step="1" class="form-control mb-3" >
			            </div>
			        </div>
			    </div>
			    <div class="col-12 d-none" id="dia3">
			        <strong>Miercoles:</strong> <br>
			        <div class="row">
			            <div class="col-6">
			                Entrada <br>
			                <input type="time" name="miercoles_entrada" id="entrada_3"    step="1" class="form-control mb-3" >
			            </div>
			            <div class="col-6">
			                Salida <br>
			                <input type="time" name="miercoles_salida" id="salida_3"    step="1" class="form-control mb-3" >
			            </div>
			        </div>
			    </div>
			    <div class="col-12 d-none" id="dia4">
			        <strong>Jueves:</strong> <br>
			        <div class="row">
			            <div class="col-6">
			                Entrada <br>
			                <input type="time" name="jueves_entrada" id="entrada_4"    step="1" class="form-control mb-3" >
			            </div>
			            <div class="col-6">
			                Salida <br>
			                <input type="time" name="jueves_salida" id="salida_4"    step="1" class="form-control mb-3" >
			            </div>
			        </div>
			    </div>
			    <div class="col-12 d-none" id="dia5">
			        <strong>Viernes:</strong> <br>
			        <div class="row">
			            <div class="col-6">
			                Entrada <br>
			                <input type="time" name="viernes_entrada" id="entrada_5"    step="1" class="form-control mb-3" >
			            </div>
			            <div class="col-6">
			                Salida <br>
			                <input type="time" name="viernes_salida" id="salida_5"    step="1" class="form-control mb-3" >
			            </div>
			        </div>
			    </div>
			    <div class="col-12 d-none" id="dia6">
			        <strong>Sabado:</strong> <br>
			        <div class="row">
			            <div class="col-6">
			                Entrada <br>
			                <input type="time" name="sabado_entrada" id="entrada_6"    step="1" class="form-control mb-3" >
			            </div>
			            <div class="col-6">
			                Salida <br>
			                <input type="time" name="sabado_salida" id="salida_6"    step="1" class="form-control mb-3" >
			            </div>
			        </div>
			    </div>
			    <div class="col-12 d-none" id="dia7">
			        <strong>Domingo:</strong> <br>
			        <div class="row">
			            <div class="col-6">
			                Entrada <br>
			                <input type="time" name="domingo_entrada" id="entrada_7"    step="1" class="form-control mb-3" >
			            </div>
			            <div class="col-6">
			                Salida <br>
			                <input type="time" name="domingo_salida" id="salida_7"    step="1" class="form-control mb-3" >
			            </div>
			        </div>
			    </div>
			</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
    let hora = "00:00:00";
    let hora2 = "00:00:00";
    $(function(){
    $('#entrada').change(function (e){
        hora = e.target.value;
        console.log(hora);        
    });
    
    $('#salida').change(function (e){
        hora2 = e.target.value;
        console.log(hora2);        
    });

    $('#indefinido').click(function(){
        $('.dias input[type=checkbox]').not('#indefinido').prop('checked', false);
    });

    // boton guardar
    $('.guardar').click(function(){
        $(this).attr('disabled', true);
        $(this).val('Espere...');
        $('#horarios_form').submit();
    });
});
function ver_dia(dia){

        $('#dia'+dia).toggleClass('d-none');
        if($('#dia'+dia).hasClass('d-none')){
            $('#entrada_'+dia).val("00:00:00");
            $('#salida_'+dia).val("00:00:00");
        }else{
            $('#entrada_'+dia).val(hora);
            $('#salida_'+dia).val(hora2);
        }
    }

</script>