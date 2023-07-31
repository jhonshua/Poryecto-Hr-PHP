<div class="modal" tabindex="-1" role="dialog" id="crearconceptooModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Crear concepto de nómina</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>


        <div class="modal-body p-4">

        <form method="post" id="submit_concepto" action="{{ route('conceptos.agregar') }}">
            @csrf
            <input type="hidden" name="id" id="id_upd">
            <input type="hidden" name="id_alterno" id="id_alterno_upd">

            <input type="text" name="nombre_concepto" id="nombre_concepto" value="{{ old('nombre_concepto') }}" class="form-control  input-style-custom mb-3" placeholder="Nombre del concepto" required >

            <input type="text" name="nombre_corto" id="nombre_corto" value="{{ old('nombre_corto') }}" class="form-control  input-style-custom mb-3" placeholder="Nombre corto" required>

            <div class="row">
                <div class="my-2 center mb-2">
                    <input type="radio" name="tipo" id="deduccion" value="1">
                    <label for="deduccion">Deducción</label>
                    &nbsp;&nbsp;
                    <input type="radio" name="tipo" id="percepcion" checked value="0">
                    <label for="percepcion">Percepción</label>
                </div>
            </div>
            <div class="row">
                <div class="my-2 center mb-2">
                    <input type="checkbox" name="finiquito" id="finiquito" value="1">
                    <label for="finiquito">Finiquito</label>
                    &nbsp;&nbsp;
                    <input type="checkbox" name="nomina" id="nomina" value="1">
                    <label for="nomina">Nómina</label>
                </div>
            </div>

            <div class="row">
                <div class="my-2 center mb-2">
                    <input type="radio" name="filerool" id="sindical" value="0">
                    <label for="sindical">Sindical</label>
                    <input type="radio" name="filerool" id="fiscal" checked value="1">
                    <label for="fiscal">Fiscal</label>
                </div>
            </div>

            <select name="codigo_sat" id="codigo_sat" class="form-control  input-style-custom mb-3">
                <option value="" disabled selected>Código Concepto</option>
                @foreach ($codigosSat as $cod)
                    <option value="{{$cod->codigo_form_sat}}">{{$cod->nombre}}</option>
                @endforeach
            </select>

            <div class="row">
                <div class="my-2 center mb-2">
                    <label>Tipo Proceso:</label><br>
                    <input type="radio" name="tipo_proceso" checked id="captura" value="0">
                    <label for="captura">Por Captura</label>&nbsp;&nbsp;&nbsp;
                    <input type="radio" name="tipo_proceso" id="calculo" value="1">
                    <label for="calculo">Rutina de Cálculo</label>&nbsp;&nbsp;&nbsp;
                    <br>
                    <input type="radio" name="tipo_proceso" id="programado" value="2">
                    <label for="programado">Programado</label>&nbsp;&nbsp;&nbsp;
                </div>
            </div>

            <select name="rutinas" id="rutinas" class="form-control  input-style-custom mb-3" required>
                <option value="" disabled selected>Rutinas</option>
                <option value=''>NINGUNA</option>
                <option value='FAHOPAT'>FAHOPAT</option>
                <option value='HEXT2'>HEXT2</option>
                <option value='HEXT3'>HEXT3</option>
                <option value='IMSS'>IMSS</option>
                <option value='ISR'>ISR</option>
                <option value='PPAGUI'>PPAGUI</option>
                <option value='PRDOM'>PRDOM</option>
                <option value='PVAC'>PVAC</option>
                <option value='SDO'>SDO</option>
                <option value='INFONA'>INFONA</option>
                <option value='DESCFISCALES'>DESC.FISCALES</option>
                <option value='PTU'>PTU</option>
                <option value='DIASFESTIVOS'>DIASFESTIVOS</option>
                <option value='ASIMILADOS'>ASIMILADOS</option>
            </select>

        </form>


            <div class="row">
                <div class="col m12 s12 mt-3 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                    <button type="button" class="btn button-style" id="add_concepto">Guardar</button>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>



<script type="text/javascript">
	$("#add_concepto").click(function(){
		var nombre_concepto = document.getElementById("nombre_concepto").value;
        var nombre_corto = document.getElementById("nombre_corto").value;
        var rutina = document.getElementById("rutinas").value;

        if(nombre_concepto== "" || nombre_corto == "" || rutina == ""){
            swal({
              title: "Para continuar debes agregar la información requerida",
            });
        }else{
          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          });
          setTimeout(submitconcepto, 1500);
        }

	});

    function submitconcepto() { document.getElementById("submit_concepto").submit() }
</script>
