<div class="modal" id="exportarPrestamoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title">Exportar Prestamo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12 ">
                    <form class="d-flex mt-3 align-items-center" action="{{route('prestamos.exportar')}}"
                          target="_blank" method="POST">
                        @csrf
                        <label for="" class="ml-3" title="Generar excel con el listado de datos"> Exportar:</label>
                        <select name="estatus" id="" class="form-control ml-2">
                            <option value="">Cualquier estatus</option>
                            <option value="1">Abiertos</option>
                            <option value="4">Para revision</option>
                            <option value="0">Cerrados</option>
                            <option value="3">Rechazados</option>
                        </select>
                        <select name="meses" id="" class="form-control ml-2">
                            <option value="">Todos los meses</option>
                            @php
                                $mesAnterior = '';
                                $cont = 0;
                            @endphp
                            @foreach ($meses as $mes)
                                @if ($mes->fecha_creacion->format('M Y') != $mesAnterior && $cont < 13)
                                    @php
                                        $mesAnterior = $mes->fecha_creacion->format('M Y') ;
                                        $cont++;
                                    @endphp
                                    <option value="{{ $mes->fecha_creacion->format('Y-m')}}">{{ $mes->fecha_creacion->format('M Y')}}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="btn text-nowrap btn-success mx-3"
                                title="Generar excel con el listado de datos">Exportar <i class="fas fa-file-excel"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>