<div class="modal" tabindex="-1" role="dialog" id="exportarPModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exportar Asistencia </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form method="post" id="periodo_biometricos_form" action="{{route('consultas.reporte-asistencias.fechas')}}">
                    @csrf
                    <table width="100%" class="mb-3">
                        <tr>
                            <td width="50%">&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr class="mt-3">
                            <td width="50%" class="fechas">
                                <label>Fecha inicial:</label>
                                <input type="text" name="fecha_inicio" id="fecha_inicio_r" class="form-control mb-3 datepicker input-style-custom" required>
                            </td>
                            <td class="fechas">
                                <label>Fecha final:</label>
                                <input type="text" name="fecha_fin" id="fecha_fin_r" class="form-control mb-3 datepicker ml-1 input-style-custom" required>
                            </td>
                        </tr>
                        <input type="hidden" name="tipo_asistencias" value="{{Session::get('empresa')['tipo_asistencias']  }}">
                    </table>


                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a href="#" data-dismiss="modal" class="btn button-style-cancel mb-3">Cancelar</a>
                            <button class="btn button-style mb-3">Generar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>