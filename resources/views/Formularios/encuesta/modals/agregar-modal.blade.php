<div class="modal fade" id="agregar-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="title_type"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addata">
            <p id="instructions"></p>
            <div id="config_select">
              <select class="form-control input-style-custom" id="opc_select" ></select>
              <br>
              <select class="form-control input-style-custom" id="iconos"></select>
              <br>
              <input type="hidden" id="tipo_opciones">
              <input type="hidden" id="tipo_opc_select">
            </div>
            
              <div class="table-responsive " id="div_radios">
                  <table class="table" id="tblitems">
                      <thead>
                          <tr>
                              <th>
                                <img src="{{asset('/img/icono-agregar-inhabilitado.png')}}" class="button-style-icon" id="addparams" data-toggle="tooltip" title="Agregar items" >
                              </th>
                              <!--<th>Cantidad de radios</th>-->
                              <th>Radio</th>
                              <th class="text-center" >Titulo</th>
                              <th class="text-center">Valor</th>
                          </tr>
                      </thead>
                      <tbody></tbody>
                  </table>
              </div>
              <div class="table-responsive " id="div_selectm">
                  <table class="table" id="tblitemselect">
                      <thead>
                          <tr>
                              <th><img src="{{asset('/img/icono-agregar-inhabilitado.png')}}" class="button-style-icon" id="addparams_select" data-toggle="tooltip" title="Agregar items" ></th>
                              <!--<th>Cantidad de items</th>-->
                              <th>Icono</th>
                              <th class="text-center" >Titulo</th>
                              <th class="text-center">Valor</th>
                          </tr>
                      </thead>
                      <tbody></tbody>
                  </table>
              </div>
              <div class="table-responsive " id="div_checklist">
                <table class="table" id="tblchecklist">
                    <thead>
                        <tr>
                            <th>
                              <img src="{{asset('/img/icono-agregar-inhabilitado.png')}}" class="button-style-icon" id="addcheck" data-toggle="tooltip" title="Agregar items" >
                            </th>
                            <!--<th>Cantidad de items</th>-->
                            <th>Check list</th>
                            <th class="text-center" >Titulo</th>
                            <th class="text-center">Valor</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="button-style" data-dismiss="modal">Cerrar</button>
          <button type="button" class="button-style" id="btnaddparams" >Agregar parámetros</li></button>
        </div>
      </div>
    </div>
  </div>