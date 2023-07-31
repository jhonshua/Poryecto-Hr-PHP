  <div class="modal" tabindex="-1" role="dialog" id="periodoModal">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title"><span></span> Periódo de Nomina</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body p-4">

                  <form method="post" id="periodos_form" action="{{ route('nomina.agregarperiodo') }}">
                      @csrf

                      <div class="row">
                          <div class="col m2 s12"></div>
                          <div class="col m8 s12">
                              <table width="100%" class="mb-3">
                                  <tr>
                                      <td>
                                          <label for="">No. de Periódo:</label>
                                          <input type="number" name="numero_periodo" id="numero_periodo" class="input-fecha mb-3 mr-4 center" style="width: fit content;" required>
                                      </td>
                                      <td>
                                          <label for="">Tipo nómina:</label>
                                          <select name="nombre_periodo" id="nombre_periodo" class="input-style mb-3 ml-2 center" style="width:fit content;" required>
                                              <option value="DIARIA">DIARIA</option>
                                              <option value="SEMANAL">SEMANAL</option>
                                              <option value="QUINCENAL">QUINCENAL</option>
                                              <option value="MENSUAL">MENSUAL</option>
                                          </select>
                                      </td>
                                  </tr>
                              </table>

                          </div>
                          <div class="col m2 s12"></div>
                      </div>




                      <table width="100%" class="mb-3">
                          <tr>
                              <td>
                                  <label for="">Fecha Inicial:</label>
                                  <input type="text" name="fecha_inicial_periodo" id="fecha_inicial_periodo" class="input-fecha mb-3 center datepicker" required>
                              </td>
                              <td>
                                  <label for="">Fecha Final:</label>
                                  <input type="text" name="fecha_final_periodo" id="fecha_final_periodo" class="input-fecha mb-3 center datepicker" disabled required>
                              </td>
                              <td>
                                  <label for="">Fecha de Pago:</label>
                                  <input type="text" name="fecha_pago" id="fecha_pago" class="input-fecha mb-3 center datepicker" disabled required>
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <label for="">Ejercicio:</label>
                                  <select name="ejercicio" id="ejercicio" class="input-fecha mb-3" required disabled>
                                      @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                      <option>{{$y}}</option>
                                      @endfor
                                  </select>
                              </td>
                              <td>
                                  <label for="">Mes:</label>
                                  <select name="mes" id="mes" class="input-fecha mb-3" required>
                                      @for($mes = 1; $mes <= 12; $mes++) <option value="{{$mes}}" {{($mes == date('m')) ? 'selected' : ''}}>{{mes(str_pad($mes, 2, "0", STR_PAD_LEFT))}}</option>
                                          @endfor
                                  </select>
                              </td>
                              <td>
                                  <label for="">Bimestre:</label>
                                  <select name="bimestre" id="bimestre" class="input-fecha mb-3" required>
                                      @for($y = 1; $y <= 6; $y++) <option value="{{$y}}">{{$y}}º</option>
                                          @endfor
                                  </select>
                              </td>
                          </tr>
                      </table>


                      <div class="row">
                          <div class="col m2 s12"></div>
                          <div class="col m8 s12">
                              <table width="100%" class="mb-3">
                                  <tr>
                                      <td>
                                          <label for="">Dias del Periodo:</label>
                                          <input type="text" name="dias_periodo" id="dias_periodo" class="input-fecha mb-3 mr-4 center" style="width: fit content;" required>
                                      </td>
                                      <td>
                                          <label for="">¿Es Periodo Especial?:</label>
                                          <select name="especial" id="especial" class="input-style mb-3 ml-2 center" style="width: fit content;" required>
                                              <option value="0" selected>No</option>
                                              <option value="1">Si</option>
                                          </select>
                                      </td>
                                  </tr>
                              </table>
                          </div>
                          <div class="col m2 s12"></div>
                      </div>
                      {{--
                    <table width="100%" class="mb-3">
                        <tr>
                            <td width="40%">
                                <label for="">Dias del Periodo:</label>
                                <input type="text" name="dias_periodo" id="dias_periodo" class="input-fecha mb-3 mr-4 center" style="width: fit content;" required >
                            </td>
                            <td width="40%">
                                <label for="">¿Es Periodo Especial?:</label>
                                <select name="especial" id="especial" class="input-style mb-3 ml-2 center" style="width: fit content;" required>
                                    <option value="0" selected>No</option>
                                    <option value="1">Si</option>
                                </select>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                    </table>
 --}}
                      <div class="row">
                          <div class="col-md-12 ">
                              <button class="button-style center guardar mt-3 btn-block">Guardar</button>
                          </div>
                      </div>

                      <input type="hidden" name="id" id="id">
                  </form>
              </div>
          </div>
      </div>
  </div>


  <style>
      #periodoModal label {
          margin-bottom: 0px;
      }


      .input-fecha {
          width: 140px;
          text-align: center;
          border: 2px #c4c4c4 solid;
          border-radius: 7px;
          padding: 5px;
      }
  </style>

  <script type="text/javascript">
      $(function() {
          $('.datepicker').datepicker({
              dateFormat: 'yy-mm-dd'
          }).val();
      });
  </script>

  <script>
      $(function() {

          // al abrir el modal cargamos las prestaciones
          $('#periodoModal').on('shown.bs.modal', function(e) {

              var id = $(e.relatedTarget).data('id');
              console.log(id);

              if (id != '') {
                  var numero_periodo = $(e.relatedTarget).data('numero_periodo');
                  var nombre_periodo = $(e.relatedTarget).data('nombre_periodo');
                  var fecha_inicial_periodo = $(e.relatedTarget).data('fecha_inicial_periodo');
                  var fecha_final_periodo = $(e.relatedTarget).data('fecha_final_periodo');
                  var fecha_pago = $(e.relatedTarget).data('fecha_pago');
                  var especial = $(e.relatedTarget).data('especial');
                  accion = 'Editar';
                  $('#periodoModal .modal-body #fecha_final_periodo').attr('disabled', false);
                  url = '{{route("nomina.actualizarperiodo")}}';
              } else {
                  numero_periodo = nombre_periodo = fecha_inicial_periodo = fecha_final_periodo = fecha_pago = especial = '';
                  $('#periodoModal .modal-body #fecha_final_periodo').attr('disabled', true);
                  accion = 'Crear';
                  url = '{{route("nomina.agregarperiodo")}}';
              }

              $('#periodoModal #periodos_form').attr('action', url);
              $('#periodoModal .modal-body #id').val(id);
              $('#periodoModal .modal-body #numero_periodo').val(numero_periodo);
              $('#periodoModal .modal-body #nombre_periodo').val(nombre_periodo);
              $('#periodoModal .modal-body #fecha_inicial_periodo').val(fecha_inicial_periodo);
              $('#periodoModal .modal-body #fecha_final_periodo').val(fecha_final_periodo);
              $('#periodoModal .modal-body #fecha_pago').val(fecha_pago);
              $('#periodoModal .modal-body #especial').val(especial);
              $('#periodoModal .modal-title span').text(accion);

              if (id != 0) {
                  $('#periodoModal .modal-body #fecha_final_periodo').trigger('change');
                  var mes = $("#periodoModal #fecha_inicial_periodo").datepicker('getDate').getMonth() + 1;
                  var year = $("#periodoModal #fecha_inicial_periodo").datepicker('getDate').getFullYear();

                  var bimestre = Math.ceil(mes / 2);
                  $('#periodoModal #mes').val(mes);
                  $('#periodoModal #bimestre').val(bimestre);
                  $('#periodoModal #ejercicio').val(year);
              }

              $('#numero_periodo').focus();
          });

          $('#fecha_inicial_periodo').change(function() {
              if ($(this).val() != '') {
                  $('#fecha_final_periodo').attr('disabled', false);
                  $("#periodoModal #fecha_final_periodo").datepicker('option', 'minDate', $('#fecha_inicial_periodo').val());

                  $("#periodoModal #fecha_final_periodo").val($('#fecha_inicial_periodo').val());

                  var mes = $("#periodoModal #fecha_inicial_periodo").datepicker('getDate').getMonth() + 1;
                  var year = $("#periodoModal #fecha_inicial_periodo").datepicker('getDate').getFullYear();

                  var bimestre = Math.ceil(mes / 2);
                  $('#periodoModal #mes').val(mes);
                  $('#periodoModal #bimestre').val(bimestre);
                  $('#periodoModal #ejercicio').val(year);

                  /*Agregamos los dias segun el periodo y ponemos en el datepicker 2*/
                  var diasT = 0;
                  var periodo = $("#nombre_periodo").val();

                  if (periodo == "DIARIA") {
                      diasT = 1;
                  }
                  if (periodo == "SEMANAL") {
                      diasT = 7;
                  }
                  if (periodo == "QUINCENAL") {
                      diasT = 15;
                  }
                  if (periodo == "MENSUAL") {
                      diasT = 30;
                  }


                  console.log("dias: " + diasT);
                  var d = new Date($('#fecha_inicial_periodo').val());

                  d.setDate(d.getDate() + diasT)
                  console.log(d);
                  console.log("DIA: " + d.getDate());
                  console.log("MES: " + (d.getMonth() + 1));
                  console.log("Año: " + d.getFullYear());


                  var dia = d.getDate();
                  var mes = d.getMonth() + 1;
                  var anio = d.getFullYear();

                  if (dia == 1) {
                      day_new = "01";
                  }
                  if (dia == 2) {
                      day_new = "02";
                  }
                  if (dia == 3) {
                      day_new = "03";
                  }
                  if (dia == 4) {
                      day_new = "04";
                  }
                  if (dia == 5) {
                      day_new = "05";
                  }
                  if (dia == 6) {
                      day_new = "06";
                  }
                  if (dia == 7) {
                      day_new = "07";
                  }
                  if (dia == 8) {
                      day_new = "08";
                  }
                  if (dia == 9) {
                      day_new = "09";
                  }

                  var fechatotal = anio + "-" + mes + "-" + dia

                  $("#periodoModal #fecha_final_periodo").val(fechatotal);

                  $('#fecha_pago').attr('disabled', false);
                  $("#periodoModal #fecha_pago").val($('#fecha_inicial_periodo').val());
                  $("#periodoModal #fecha_pago").datepicker('option', 'minDate', $('#fecha_inicial_periodo').val());

                  days = Math.round(getNumDays($('#fecha_inicial_periodo').val(), $('#fecha_final_periodo').val()));

                  $('#dias_periodo').val(days + 1);

              } else {
                  $('#periodoModal #fecha_final_periodo').attr('disabled', true);
                  $('#periodoModal #fecha_pago').attr('disabled', true);
                  $('#periodoModal #mes').val('');
                  $('#periodoModal #bimestre').val('');
                  $('#periodoModal #ejercicio').val('');
              }
          });

          $('#fecha_final_periodo').change(function() {
              if ($(this).val() != '') {
                  $('#fecha_pago').attr('disabled', false);
                  $("#periodoModal #fecha_pago").val($('#fecha_inicial_periodo').val());
                  $("#periodoModal #fecha_pago").datepicker('option', 'minDate', $('#fecha_inicial_periodo').val());
                  days = getNumDays($('#fecha_inicial_periodo').val(), $('#fecha_final_periodo').val())
                  $('#dias_periodo').val(days + 1);
              } else {
                  $('#fecha_pago').attr('disabled', true);
                  $('#dias_periodo').val('');
              }
          });

          $('#periodoModal .guardar').click(function() {

          });

          $('#periodos_form').submit(function() {
              $('#periodoModal .guardar').attr('disabled', true).text('Espere...');
              $('#periodoModal select, #periodoModal input').attr('disabled', false);
          });

          function getNumDays(date1, date2) {
              var date1 = new Date(date1);
              var date2 = new Date(date2);
              var Difference_In_Time = date2.getTime() - date1.getTime();

              var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
              return Difference_In_Days;
          }

      });
  </script>