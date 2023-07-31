<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<link href="{{ asset('css/steps.css') }}" rel="stylesheet">

<body>
    @php
    
        $finicio = new DateTime($datosImplementacion->fecha_inicio);
        $ffin = new DateTime($datosImplementacion->fecha_fin);
        $i = "Periodo de implementación : ".$finicio->format('d-m-Y')." al ".$ffin->format('d-m-Y');
        $hoy = new DateTime();
        $id_periodo_norma = 0;
        
        if($periodoNorma){
            $id_periodo_norma = $periodoNorma->id;
        }
    @endphp

    @include('includes.navbar')
    <div class="container">

    @include('includes.header',['title'=>'Lista de empleados',
        'subtitle'=>'Norma 035', 'img'=>'img/icono-lista-empleados.png',
        'route'=>'norma.normaTabla'])

        <div class="row d-flex justify-content-start ">
            @if($hoy <= $fNormaExp) 
                <div class="col-lg-3 col-md-12 col-xs-12 mb-2 mt-1">
                    @if(!empty($participantes) && $participantes->count() == 0 && !empty($fNormaExp) && $hoy < $fNormaExp) 
                        <a href="#">
                            <button type="button" class="button-style mt-1 revisar" title="Nueva lista" id="nuevaLista" data-toggle="modal" data-target="#nuevaListaModal">Nueva lista
                                <!--<img src="{{--{{asset('img/icono-exportar.png')}}--}}" class="w-15px mb-1" >-->
                            </button>
                        </a>
                    @endif
                    @if(!empty($participantes) && $participantes->count() > 0 && !empty($fNormaExp) && $hoy < $fNormaExp) 
                        <a href="#">
                            <button type="button" class="button-style mt-1" title="Recordatorio masivo" id="recordatorioLista" data-toggle="modal" data-target="#recordatorioListaModal" data-backdrop="static" data-keyboard="false">Recordatorio masivo
                            <!--<img src="{{--{{asset('img/icono-exportar.png')}}--}}" class="w-15px mb-1" >-->
                            </button>
                        </a>
                    @endif
                </div>
            @endif
        </div>
        <br>
        @if(session()->has('success'))
            <div class="row">
                <div class="alert alert-success" style="width: 100%;" align="center">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong>Notificación: </strong>
                    {{ session()->get('success') }}
                </div>
            </div>
        @elseif(session()->has('danger'))
            <div class="row">
                <div class="alert alert-danger" style="width: 100%;" align="center">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong>Notificación: </strong>
                    {{ session()->get('danger') }}
                </div>
            </div>
        @endif
        <div class="article-header border">
            <div class="row">
                <div class="col-md-8">
                    @if(!empty($datosImplementacion->fecha_inicio))
                        <h4 class="font-weight-bold">{{$i}}</h4>
                    @endif
                </div>
                <div class="col-md-4">
                    @if(!empty($datosImplementacion->fecha_inicio))
                        @if(!empty($datosImplementacion->sede_asignada->nombre))
                            <h4 class="text-center font-weight-bold">SEDE : {{$datosImplementacion->sede_asignada->nombre}}</h4>
                        @endif
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    @if(!empty($datosImplementacion->fecha_inicio))
                        @if(!empty($datosImplementacion->razon_social_asignada->razon_social))
                            {{$datosImplementacion->razon_social_asignada->razon_social}}
                        @endif

                        <h4>{!!$tituloNorma!!} <b>{!!$expansion!!}</b></h4>
                    @endif
                </div>
                <div class="dataTables_filter col-xs-12 col-md-12 col-lg-4 " id="div_buscar"></div>
            </div>
        </div>
        <br>
        <div class="article border">
            <table class="table w-100 seleccionados" id="tbl ">
                <thead>
                    <tr>
                        <th>Detalle</th>
                        <th>ID</th>
                        <th>Paterno</th>
                        <th>Materno</th>
                        <th>Nombre</th>
                        <th>Genero</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trabajadores as $key=> $trabajador)
                    <tr id="{{$trabajador->id}}">
                        <td class="details-control" data-edad="{{$trabajador->edad}}" data-estado_civil="{{$trabajador->estado_civil}}" data-nivel_estudios="{{$trabajador->nivel_estudios}}" data-tipo_puesto="{{$trabajador->tipo_puesto}}" data-tipo_contratacion="{{$trabajador->tipo_contratacion}}" data-tipo_personal="{{$trabajador->tipo_personal}}" data-tipo_jornada="{{$trabajador->tipo_jornada}}" data-rotacion_turnos="{{$trabajador->rotacion_turnos}}" data-experiencia_puesto_actual="{{$trabajador->experiencia_puesto_actual}}" data-experiencia_laboral="{{$trabajador->experiencia_laboral}}"><a class="mr-2"><img src="{{asset("img/icono-detalle.png")}}" class="button-style-icon"> </a>
                        </td>
                        <td>{{$trabajador->id}}</td>
                        <td>{{$trabajador->paterno}}</td>
                        <td>{{$trabajador->materno}}</td>
                        <td>{{$trabajador->nombre}}</td>
                        <td>@if(!empty($trabajador->sexo)) <span class='text-secundary font-weight-bold'>{{$catalogos['sexo'][$trabajador->sexo]}} </span> @endif </td>
                        <td>@if(!empty($trabajador->correo))
                                <a class="custom-correo" id='correotabla{{$trabajador->id}}' data-idinformacion='{{$trabajador->id}}' data-correo='{{$trabajador->correo}}' class='text-secundary font-weight-bold' data-toggle='modal' data-target='#modificarCorreosModal'>
                                    {{$trabajador->correo}}
                                </a>
                            @endif
                        </td>
                        <td>
                            @php
                                $icono_pdf = asset("img/icono-pdf.png");
                                $icono_resultados = asset("img/icono-resultados.png");
                                $icono_llenar_formulario = asset("img/icono-llenar-encuesta.png");
                                $icono_editar = asset("img/icono-editar.png");
                                $icono_correo =asset("img/icono-correo.png");
                                $btn="";
                                $id = $trabajador->id;
                                $cuestionarios = $trabajador->cuestionarios;
                                $totCu = count($cuestionarios);
                                $totalCuestionariosTerminados=0;

                                foreach ($cuestionarios as $key => $c) if($c['estatus'] == 1) $totalCuestionariosTerminados++;

                                $btn = '<div class="btn-group">';

                                if($trabajador->informacion_validada === 1){

                                    $btn .= '<a data-ipersonal="'.$id.'" class="resultado'.$id.' enviaf  mr-2" data-toggle="modal" data-target="#resultadosListaModal" title="Resultados"><img src="'.$icono_resultados.'" class="button-style-icon"></a>';
                                    $btn .= '<a href="'.route('norma.implementacion.cuestionarios.pdf.infTrabajador', $id).'" class="resultado'.$id.' enviaf  mr-2" target="_blank" title="Exportar pdf"><img src="'.$icono_pdf.'" class="button-style-icon"></a>';

                                }else{
                                    $btn .= '<a data-ipersonal="'.$id.'" data-nombre="'.$trabajador->nombre.'" data-paterno="'.$trabajador->paterno.'" data-materno="'.$trabajador->materno.'" data-sexo="'.$trabajador->sexo.'" class="remplazar'.$id.' remplazar  mr-2" data-toggle="modal" data-target="#remplazarListaModal" title="Remplazar empleado"><img src="'.$icono_editar.'" class="button-style-icon"></a>';

                                }
                                if($totCu > $totalCuestionariosTerminados){

                                    $btn .= '<a data-ipersonal="'.$id.'" class="enviaf  mr-2" data-toggle="modal" data-target="#llenarCuestionarioModal" title="Llenar cuestionario"><img src="'.$icono_llenar_formulario.'" class="button-style-icon"></i></a>';
                                    
                                    if(!empty($trabajador->correo) && $trabajador->informacion_validada === 0){

                                        $btn .= '<a data-ipersonal="'.$trabajador->id.'" data-nombre="'.$trabajador->nombre.'" data-paterno="'.$trabajador->paterno.'" data-materno="'.$trabajador->materno.'" data-correo="'.$trabajador->correo.'" class="recordatorio mr-2" data-toggle="modal" data-target="#recordatorioListaModal" title="Recordatorio"><img src="'.$icono_correo.'" class="button-style-icon"></a>';

                                    }else if(empty($trabajador->correo) && $trabajador->informacion_validada === 0){

                                        $btn .= '<a data-ipersonal="'.$id.'" data-correo="'.$trabajador->correo.'" class="recordatorio btn btn-danger btn-sm mr-2 disabled" title="Recordatorio"><i class="fa fa-envelope"></i></a>';

                                    }
                                }

                                echo $btn.'</div>';
                            @endphp

                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
            <form id="accionImplementacion">
                @csrf
                <input type="hidden" id="implementacion" name="implementacion" value="{{$datosImplementacion->id}}" />
                <input type="hidden" id="periodo_norma" name="periodo_norma" value="{{$id_periodo_norma}}" />
                <input type="hidden" id="accion" name="accion" value="" style="width:500px" />
            </form>
        </div>
    </div>
    @include('includes.footer')
    @include('norma.implementacion.modals.nueva-lista-modal')
    @include('norma.implementacion.modals.lista-remplazar-modal')
    @include('norma.implementacion.modals.lista-cuestionario-modal')
    @include('norma.implementacion.modals.lista-modificar-cuenta-modal')
    @include('norma.implementacion.modals.lista-resultados-modal')
    @include('norma.implementacion.modals.lista-recordatorio-modal')
    @include('includes.spinner')

    <script src="{{asset('js/validate/jquery-validate-adicional.js') }}"></script>
    <script src="{{asset('js/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/steps/modernizr-2.6.2.min.js') }}"></script>
    <script src="{{ asset('js/steps/js.cookie.min.js') }}"></script>
    <script src="{{ asset('js/steps/jquery.steps.min.js') }}"></script>
    <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="{{asset('js/parsley/i18n/es.js')}}"></script>

    <script>
        $(function(){
           
            let catalogos =  @php echo json_encode($catalogos); @endphp 
            
            const table= $('.seleccionados').DataTable({
                        scrollY:'60vh',
                        scrollCollapse: true,
                        paging:false,
                        "order": [[2, 'asc' ]],
                        "language": {
                            search: '',
                            searchPlaceholder: 'Buscar registros',
                            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                        },
                        columnDefs: [{ 
                            className: "text-center", "targets": [3]
                        }],
                        initComplete: function () {
                            // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                            elementos = $(".dataTables_filter > label > input").detach();  
                            elementos.appendTo('#div_buscar');
                            $("#div_buscar > input").addClass("input-style-custom-search");
                        },
                    });
     
            $(function() {
                let detailRows = [];
                $('.seleccionados').on( 'click', 'tr td.details-control', function () {
                    
                    let tr = $(this).closest('tr');
                    let row = table.row( tr );
                    let idx = $.inArray( tr.attr('id'), detailRows );
                
                    if ( row.child.isShown() ) {
                        tr.removeClass( 'details' );
                        row.child.hide();
                        // Remove from the 'open' array
                        detailRows.splice( idx, 1 );
                    }else {
                        let params ={
                            'edad' : $(this).data('edad'),
                            'estado_civil' : $(this).data('estado_civil'),
                            'nivel_estudios' : $(this).data('nivel_estudios'),
                            'tipo_puesto' : $(this).data('tipo_puesto'),
                            'tipo_contratacion' : $(this).data('tipo_contratacion'),
                            'tipo_personal' : $(this).data('tipo_personal'),
                            'tipo_jornada' : $(this).data('tipo_jornada'),
                            'rotacion_turnos' : $(this).data('rotacion_turnos'),
                            'experiencia_puesto_actual' : $(this).data('experiencia_puesto_actual'),
                            'experiencia_laboral' : $(this).data('experiencia_laboral'),
                        };
                
                        tr.addClass( 'details' );
                        row.child( format( params) ).show();
                        // Add to the 'open' array
                        if ( idx === -1 ) {
                            detailRows.push( tr.attr('id') );
                        }
                    }
                });
                table.on( 'draw', function () {
                    
                    if({{$terminado}}){
                        
                        $(".recordatorio, .remplazar").remove();
                    }
            
                    $.each( detailRows, function ( i, id ) {
                        $('#'+id+' td.details-control').trigger( 'click' );
                    });
                });
                // al abrir el modal cargamos los datos
                $('#nuevaListaModal').on('shown.bs.modal', function (e) {
                    $('#contenedorLista').empty();
                    $('#contenedorLista').append('<div class="text-center" style="padding:15px 0px;"><h3><b>Tipo de selección:</b></h3><h4>&nbsp;<input type="radio" id="sistema" value="1" name="tipo_lista"/> <label for="sistema">Sistema</label>&nbsp;&nbsp;<input type="radio" id="archivo" value="2" name="tipo_lista"/> <label for="archivo">Archivo</label></h4><div id="contenedorLista2"><img src="{{asset("img/spinner.gif")}}" style="width:50px;display:none;" id="cargando"/><form action="" method="post" id="trabajadores_form" role="form" accept-charset="UTF-8" enctype="multipart/form-data">@csrf<input type="hidden" value="{{$datosImplementacion->id}}" name="idimplementacion" id="idimplementacion" /><input type="hidden" value="{{$id_periodo_norma}}" name="norma" id="norma" /><div id="divTrabajadores"></div><div class="form-row" id="botones"><br/><br/></div></form></div></div>');
                    validarFormulario();
                    
                    $("input:radio[name=tipo_lista]").on('click', function(){
                        $("#cargando").css("display","inline-block");
                        //alert($(this).val());
                        //$("#trabajadores_form").slideUp(function(){
                        if($(this).val() == 1){
                            //alert($("#trabajadores_sede #sede").val());
                            $("#trabajadores_form").slideUp(function(){
                                let params = {'_token': $('meta[name="csrf-token"]').attr('content'), "sede" : $("#trabajadores_sede #sede").val()};
                                //var url = "{{--{{route('norma.implementacion.lista.empleados.generar.api')}}--}}";
                                let url = "{{route('norma.implementacion.lista.empleados.generar')}}";
                                $.ajax({
                                        type: "POST",
                                        url: url,
                                        data: params,
                                        dataType: 'JSON',
                                        async: false,
                                        success: function (response){
                                   
                                            if(response.ok == 1) {
                                                
                                                $('#divTrabajadores').html('<label for="trabajadores" class="font-weight-bold" >Seleccionados:</label><select class=" dropdown-primary form-control-lg" style="height:300px;margin:10px 10px;font-size:14px;width:95%" multiple  name="trabajadores[]" id="trabajadores"></select><label class="font-weight-bold" >Exentos:</label><select class=" dropdown-primary form-control-lg" style="height:200px;margin:10px 10px;font-size:14px;width:95%" multiple  name="excentos[]" id="excentos"></select><input type="hidden" value="' + response.total + '" name="totalEmpleados" id"totalEmpleados"/><br/>');
                                                empleados(response);
                                                $("#nuevaListaModal #trabajadores_form #trabajadores, #excentos").attr("disabled","disabled");
                                            
                                            }else if(response.ok==3){
                                                
                                                swal("No existen datos por sistema intente cargar los empleados por archivo mediante formato excel ..!!", {
                                                    icon: "warning",
                                                });

                                            }else{
                                                
                                                swal("Ocurrio un error contacta a tu administrador", {
                                                    icon: "error",
                                                });
                                            }
                                            $("#nuevaListaModal  #cargando").css("display","none");
                                            botonesFormulario(1);
                                        }
                                });
                                $("#trabajadores_form").slideDown();
                            });
                            
                        }else{
                            $("#trabajadores_form").slideUp(function(){
                                $('#divTrabajadores').html(`<div class="custom-file ">
                                                                <input type="file" name="importar_trabajador" id="importar_trabajador" class="custom-file-input"  accept=".xls, .xlsx" required/>
                                                                <label class="custom-file-label" >Seleccionar Archivo</label>
                                                            </div>`);
                                $("#trabajadores_form").slideDown();
                                $("#nuevaListaModal  #cargando").css("display","none");
                                botonesFormulario(2);
                            });
                     
                        }
                    });
                });
                $(document).on('change','input[type="file"]',function(){
                
                    let fileName = this.files[0].name;
                    if(fileName !=""){
                        let ext_archivo = fileName.split('.').pop();
                        ext_archivo = ext_archivo.toLowerCase();
                        let extension = ['xlsx','xls'];
                    
                        if(!extension.includes(ext_archivo)){
                        
                            this.value = '';
                            swal("El archivo no es valido , intentalo nuevamente !", {
                                icon: "error",
                            });   
                        }
                        
                    }else{
                        swal("No se a seleccionado ningún archivo !", {
                            icon: "error",
                        });    
                    }
                });
                $('#remplazarListaModal').on('shown.bs.modal', function (e) {
        
                    let  idPersonal = $(e.relatedTarget).data('ipersonal');
                    let nombre = $(e.relatedTarget).data('nombre');
                    let paterno = $(e.relatedTarget).data('paterno');
                    let materno = $(e.relatedTarget).data('materno');
                    let sexo = $(e.relatedTarget).data('sexo');
                    $("#idEmpleadoEdit").val(idPersonal);
                    $("#generoEdit").val(sexo);
                    
                    $("#divTrabajadoresEditar").html('¿Está seguro de remplazar a <strong>' + nombre+ ' '+ paterno + ' ' + materno + '</strong>?<br/><br/>');
                    $("#botonesEditar").html('<button id="regresarEditar" type="button" data-dismiss="modal" aria-label="Close" class=" btn btn-secondary cancelar">Cancelar</button><button class="button-style guardar ml-3" id="btn-remplazar">Remplazar</button>')
                    $("#btn-remplazar").off();
                    $("#btn-remplazar").on("click", function(){
                        var img = "{{asset('img/spinner.gif')}}";
                        $("#btn-remplazar").html("<img src='"+img+"' style='width:20px' />").attr("disabled","disabled");
                        var url = "{{route('norma.implementacion.lista.empleados.remplazar.api')}}";
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: $('#trabajadores_editar_form').serialize(),
                            dataType: 'JSON',
                            success: function (response) {
                                if(response.ok == 1) {
                                    console.log(response);
                                    $("#divTrabajadoresEditar").html('<strong>' + nombre + ' '+ paterno + ' ' + materno + '</strong> fue remplazado por <strong>' + response.remplazo.nombre + ' ' + response.remplazo.paterno + ' ' + response.remplazo.materno + '</strong><br/><br/>');
                                    $("#botonesEditar").html('<button id="regresarEditar" type="button" data-dismiss="modal" aria-label="Close" class=" btn btn-secondary cancelar" onclick="location.reload();" >OK</button>');
                                    table.row( $(".seleccionados tbody tr[id="+idPersonal+"]") ).draw();
                                } else if(response.ok == 2){
                                    $("#divTrabajadoresEditar").html('<strong>'+response.msg+'</strong><br/><br/>');
                                    $("#botonesEditar").html('<button id="regresarEditar" type="button" data-dismiss="modal" aria-label="Close" class=" btn btn-secondary cancelar">OK</button>');
                                
                                }else {
                                    swal("Ocurrió un error al cargar los datos del empleado. Intente nuevamente!", {
                                        icon: "error",
                                    });   
                                }
                            },error: function(jqXHR, textStatus, errorThrown){
                                swal("Ocurrió un error al cargar los datos del empleado. Intente nuevamente!", {
                                    icon: "error",
                                });   
                            
                            }
                        });
                        return false;
                    });
            
                });
                $('#llenarCuestionarioModal').on('shown.bs.modal', function (e) {
                    let idEmpleado = $(e.relatedTarget).data('ipersonal');
                    $("#informacion_trabajador").val(idEmpleado);
                    $("#divTrabajadoresCuestionarios").html('');
                    var url = "{{route('norma.implementacion.lista.empleados.admin.llenar.cuestionarios')}}";
                    $.ajax({
                            type: "POST",
                            url: url,
                            data: $('#trabajador_form').serialize(),
                            dataType: 'html',
                            success: function (respuesta) {
                                $("#divTrabajadoresCuestionarios").append('<h3 class="font-weight-bold text-center" >Información personal</h3>');
                                $("#divTrabajadoresCuestionarios").append(respuesta);
                            }
                    });
                
                });
      
                function empleados(r){
                
                    const {total,hombres,mujeres} = r.datos;
                    let n =  Math.round((0.9604*total)/((0.0025*(total-1))+0.9604));
                    let porcentaje_hombres_en_total = Math.round((hombres.length*100)/total);
                    let porcentaje_mujeres_en_total = Math.round((mujeres.length*100)/total);
                    let total_hombres_en_n = Math.round((porcentaje_hombres_en_total *n)/100);
                    let total_mujeres_en_n = Math.round((porcentaje_mujeres_en_total *n)/100);
                    // realizar la seleccion aleatoria de empleados
                    cargarEmpleados(hombres,total_hombres_en_n);
                    cargarEmpleados(mujeres,total_mujeres_en_n);
                    if(total <=16){ // Guía de referencia I
                        $('#divTrabajadores').prepend("<h4 class='font-weight-bold' >" + total + " Empleados <strong>(Guía de referencia I)</strong></h4>");
                    }else if(total >=17 && total <=50){ // Guía de referencia I y II, empresas con menos de 50 trabajadores
                        $('#divTrabajadores').prepend("<h4 class='font-weight-bold'> " + total + " Empleados <strong>(Guía de referencia I y II)</strong></h4>");
                    }else if(total > 50){ // Guía de referencia I y III, empresas con 51 o más trabajadores
                        $('#divTrabajadores').prepend("<h4c class='font-weight-bold'> " + total + " Empleados <strong>(Guía de referencia I y III)</strong></h4>");
                    }else{
                    }
                }
                //Función para obtener dinamicamente a los trabajadores que realizaran el cuestionario
                function cargarEmpleados(datos,total_n){
            
                    let tot = datos.length;
                    let remover = tot-total_n;
                    let min = 1;
                    // console.log(total_n, tot, remover);
                    // return false;
                    //se eliminan los trabajadores a los que no se debe aplicar
                    do {
                        
                        if(remover != 0){
                            let aleatorio = Math.floor(Math.random() * (datos.length - min)) + min;
                            if(datos[aleatorio-1]!= undefined){
                                
                                let  correo = datos[aleatorio-1]['correo'];
                                if(datos[aleatorio-1]['correo'] == undefined){
                                    correo = "sincorreo";
                                }
                                $("#nuevaListaModal #excentos").append("<option value='"+datos[aleatorio-1]['id']+"$"+datos[aleatorio-1]['nombre']+"$"+datos[aleatorio-1]['apaterno']+"$"+datos[aleatorio-1]['amaterno']+"$"+datos[aleatorio-1]['genero']+"$"+correo+"' selected='selected'>"+datos[aleatorio-1]['nombre']+" "+datos[aleatorio-1]['apaterno']+" "+datos[aleatorio-1]['amaterno']+"</option>");
                                //$("#excentos").append("<li>"+datos[aleatorio-1]['nombre']+" "+datos[aleatorio-1]['apaterno']+" "+datos[aleatorio-1]['amaterno']+"</li>")
                                datos.splice(aleatorio-1,1); 
                                //alert();
                            }
                        }
                    }while (datos.length != (tot-remover));
                    //return false;
                    //agregar trabajadores a la lista de seleccionados
                    for(let i  = 0; i<datos.length; i++){
                        //console.log(datos[i]['correo']);
                        let correo = datos[i]['correo'];
                        if(datos[i]['correo'] == undefined){
                            correo = "sincorreo";
                        }
                        $("#nuevaListaModal #trabajadores_form #trabajadores").append("<option value='"+datos[i]['id']+"$"+datos[i]['nombre']+"$"+datos[i]['apaterno']+"$"+datos[i]['amaterno']+"$"+datos[i]['genero']+"$"+correo+"' selected='selected'>"+datos[i]['nombre']+" "+datos[i]['apaterno']+" "+datos[i]['amaterno']+"</option>");
                    }
                }
                function botonesFormulario(btn){
                    if(btn == 1){
                        
                        $("#botones").html('<br><button class="button-style-custom center guardar " id="btn-guardar">Guardar</button>');
                    
                    }else{
                        $("#botones").html('<button class="button-style-custom center mt-3" id="btn-procesar">Procesar archivo</button>');
                        $("#btn-procesar").off();
                        $("#btn-procesar").on('click', function(e){
                            
                            e.preventDefault();
                            $("#spinner").show();
                            let form = $("#trabajadores_form");
                            if(form.parsley().isValid()){  
                                let formData = new FormData(document.getElementById("trabajadores_form"));
                                let  url = "{{route('norma.implementacion.lista.importar')}}";
                                $.ajax({
                                    type: "post",
                                    url: url,
                                    data: formData,
                                    cache:false,
                                    contentType: false,
                                    processData: false,
                                    success: function(response) {
                                  
                                        if(response.ok == 1){
                                            
                                            $('#divTrabajadores').html('<label for="trabajadores" class="font-weight-bold" >Seleccionados:</label><select class=" dropdown-primary form-control-lg" style="height:500px;margin:10px 10px;font-size:14px;width:95%" multiple  name="trabajadores[]" id="trabajadores"></select><label class="font-weight-bold">Excentos:</label><select class=" dropdown-primary form-control-lg" style="height:500px;margin:10px 10px;font-size:14px;width:95%" multiple  name="excentos[]" id="excentos"></select><input type="hidden" value="' + response.total + '" name="totalEmpleados" id"totalEmpleados"/><br/>');
                                            empleados(response);
                                            $("#nuevaListaModal #trabajadores_form #trabajadores, #excentos").attr("disabled","disabled");
                                            $("#botones").html('<div class="mx-auto" ><button id="regresar" type="button" data-dismiss="modal" aria-label="Close" class="button-cancel-style cancelar">Cancelar</button><button class="button-style guardar ml-1" id="btn-guardar">Guardar</button></div>');
                                        
                                        }else{
                                        
                                            swal("Ocurrió un error. Intente nuevamente  o contacta a tu administrador", {
                                                icon: "error",
                                                timer: 3000,
                                            });
                                        }
                                        
                                        $("#spinner").toggle();           
                                    },
                                    error: function(data){
                                        
                                        swal("Ocurrió un error . Algunos datos no se pudieron procesar..!!", {
                                            icon: "error",
                                            dangerMode: true,
                                        }).then((willDelete) => {
                                            if(willDelete){
                                                location.reload();
                                            }
                                        });
                                    }
                                });
                            }else{ 
                                form.parsley().validate();
                            }    
                        });
                    }
                    return false;
                }
                function validarFormulario(){
                    $("#trabajadores_form").validate({
                        errorClass: "text-danger",
                        errorElement: "span",
                        errorPlacement: function(error, element) {
                            error.appendTo( $('label[for='+element.attr("name")+']') );
                        },
                        rules: {
                            trabajadores: {required: true},
                        },submitHandler: function(form) {
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                     
                            $("#btn-guardar").html(`<span class="spinner-grow " role="status" aria-hidden="true"></span> Espere...`).attr("disabled","disabled");
                            var url = "{{route('norma.implementacion.lista.empleados.crear.api')}}"
                            $("#nuevaListaModal #trabajadores_form #trabajadores, #excentos").attr("disabled",false);
                            $.ajax({
                                url: url,
                                type:"POST",
                                data: $('#trabajadores_form').serialize(),
                                success:function(response){
                                
                                    if(response.ok == 1) {
                                        cerrarModal();
                                        $("#panel-btn").append('<span data-toggle="tooltip" data-html="true" data-placement="right" title="Recordatorio masivo"><a id ="recordatorioLista" href="#" class="btn btn-warning" data-toggle="modal" data-target="#recordatorioListaModal">Recordatorio Masivo</a></span>');
                                        $("#nuevaLista").remove();
                                        location.reload();
                                    }else if(response.ok == 2) {
                        
                                        swal("El correo no se pudo enviar contacta a tu administrador.", {
                                            icon: "error",
                                            timer: 3000,
                                        });
                                        location.reload();
                                    }else{
                                        swal("La lista de empleados no pudo crearse con éxito", {
                                            icon: "error",
                                            timer: 3000,
                                        });
                                        location.reload();
                                    }
                                }
                            });
                        }
                    });
                }
                function cerrarModal(){
                    $("#nuevaListaModal").modal('hide');//ocultamos el modal
                    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
                    $('.modal-backdrop').remove();
                }
                function format (data) {
                    const {edad,estado_civil,nivel_estudios,tipo_puesto,tipo_contratacion,tipo_personal,tipo_jornada,rotacion_turnos,experiencia_puesto_actual,experiencia_laboral} = data;
                    
                    let mensaje = ' <div class="col-12 themed-grid-col"><center>El empleado no ha confirmado sus datos</center></div>';
                    if(edad){ mensaje = '<div class="col-3 themed-grid-col"><strong>Edad </strong>: '+catalogos["edad"][edad]+'</div>'; }
                    if(estado_civil){ mensaje += ' <div class="col-3 themed-grid-col"><strong>Estado civil </strong>: '+catalogos["estado_civil"][estado_civil]+'</div>';   }
                    if(nivel_estudios){   mensaje += ' <div class="col-3 themed-grid-col"><strong>Nivel estudios </strong>: '+catalogos["nivel_estudios"][nivel_estudios]+'</div>'; }
                    if(tipo_puesto){  mensaje += ' <div class="col-3 themed-grid-col"><strong>Tipo puesto </strong>: '+catalogos["tipo_puesto"][tipo_puesto]+'</div>';  }
                    if(tipo_contratacion){    mensaje += ' <div class="col-3 themed-grid-col"><strong>Tipo contratación </strong>: '+catalogos["tipo_contratacion"][tipo_contratacion]+'</div>';    }
                    if(tipo_personal){    mensaje += ' <div class="col-3 themed-grid-col"><strong>Tipo personal </strong>: '+catalogos["tipo_personal"][tipo_personal]+'</div>';    }
                    if(tipo_jornada){ mensaje += ' <div class="col-3 themed-grid-col"><strong>Tipo jornada </strong>: '+catalogos["tipo_jornada"][tipo_jornada]+'</div>';   }
                    if(rotacion_turnos){  mensaje += ' <div class="col-3 themed-grid-col"><strong>Rotación turnos </strong>: '+catalogos["rotacion_turnos"][rotacion_turnos]+'</div>';  }
                    if(experiencia_puesto_actual){    mensaje += ' <div class="col-3 themed-grid-col"><strong>Experiencia puesto actual </strong>: '+catalogos["experiencia_puesto_actual"][experiencia_puesto_actual]+'</div>';    }
                    if(experiencia_laboral){  mensaje += ' <div class="col-3 themed-grid-col"><strong>Experiencia laboral </strong>: '+catalogos["experiencia_laboral"][experiencia_laboral]+'</div>';  }
                    
                    return '<div class="row mb-3">'+mensaje+'</div><br/>';
                }
            });
            $("#spinner").toggle();
            let idActividad = '';
            $('#resultadosListaModal').on('shown.bs.modal', function (e) {

                let idEmpleado = $(e.relatedTarget).data('ipersonal');
                let informacion = table.row( $(".seleccionados tbody tr[id=" + idEmpleado + "]") ).data();
                $("#idEmpleadoResultados").val(idEmpleado);
                $("#divTrabajadoresResultados").html('');

                let url = "{{route('norma.implementacion.lista.empleados.resultadosEmpleado')}}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: $('#trabajadores_resultados_form').serialize(),
                    dataType: 'html',
                    success: function (response) {
                        $("#divTrabajadoresResultados").append(response);
                        return false;
                    }, error: function(jqXHR, textStatus, errorThrown){
                        swal("Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.", {
                            icon: "error",
                        });
                    }
                });
            });
        })



        </script>
    </body>
</html>