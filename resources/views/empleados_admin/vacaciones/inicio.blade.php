<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('includes.head')
<link href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/alt/AdminLTE-bootstrap-social.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/alt/AdminLTE-fullcalendar.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/alt/AdminLTE-select2.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/alt/AdminLTE-without-plugins.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css" rel="stylesheet">
<style>
    .card_detalle_solicitud{
        min-height: 300px;
        background-color: #eee;
    } 

    .card_solicitudes_pendientes{
        max-height: 400px;
    } 

    .list-group-item.active {
        background-color : #f4f4f4;
        color : #212529;
        border: 1px solid #ddd;
    }

    /* .list-group-item.active */
    .list-group-item.active {        
        border-left: 2px solid #f0c018;        
        outline:none;      
    }   

    #divLoading{        
        margin: 0 auto;               
        text-align: center;
        display:block; 
        width:600px; 
        height:400px; 
        position:absolute; 
        top:50%; 
        left:50%; 
        /*margin-top:-100px; */
        margin-left:-300px;
    }   
    
</style>
<body>
    @include('includes.navbar')
    @php
    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Puestos de la empresa',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-puestos.png',
        'route'=>'bandeja'])

        @if(\Session::get('mensaje'))
        <div class="alert alert-info" role="alert" id="alerta">
            {!! \Session::get('mensaje') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                <div>
                  <a href="{{route("empleados.calendario")}}"><div type="button" class="button-style ml-3 mb-3" data-toggle="modal"> <img src="/img/icono-crear.png" class="button-style-icon">Crear nueva solicitud</div></a>
                </div>
                <div>
                    <a href="{{route("empleados.control.vacaciones")}}"><div type="button" class="button-style ml-3 mb-3" data-toggle="modal"> <img src="/img/icono-crear.png" class="button-style-icon">Control de empleados</div></a>
                  </div>
            </div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <div class="row">
                <div class="col-md-7">
                    <div class="text-center">
                        <h4>Solicitudes Pendientes</h4>
                    </div>
                    <div class="list-group card_solicitudes_pendientes overflow-auto" role="tablist">
                        <input type="hidden" id="datos_solicitud" value="{{json_encode($datos_solicitud)}}">
                        <input type="hidden" id="id_empresa" value="{{$id_empresa}}">
                        @php $total_solicitudes = 0; @endphp
                        @foreach ($datos_solicitud as $key => $item)
                            @if($item->estatus_solicitud==2)
                                <button class="list-group-item list-group-item-action d-flex justify-content-left align-items-center" data-toggle="list" data-id-empleado="{{$item->id_empleado}}" data-id-solicitud="{{$item->id}}" role="tab">
                                    <div class="text-left mr-4 ">
                                        @php
                                            //echo $url_img, '<br>';
                                            $url_img_val = public_path().'/storage/repositorio/'.$id_empresa.'/'.$item->id_empleado.'/'.$item->file_fotografia;
                                           
                                            $url_img = 'public/storage/repositorio/'.$id_empresa.'/'.$item->id_empleado.'/'.$item->file_fotografia;
                                            $url_imagen = 'storage/repositorio/'.$id_empresa.'/'.$item->id_empleado.'/'.$item->file_fotografia;
                                            //echo $url_img, '<br>';
                                            $total_solicitudes++;
                                        @endphp
                                        @if(file_exists($url_img_val) && is_file( $url_img_val))
                                            
                                            <img id="img_e{{$item->id_empleado}}" data-img="{{$url_imagen}}" class="direct-chat-img" src="{{asset($url_imagen)}}" alt="message user image">
                                        @else
                                            <img id="img_e{{$item->id_empleado}}" data-img="img/avatar.png" class="direct-chat-img" src="{{asset('img/avatar.png')}}" alt="message user image">
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column ">
                                        <span class=""><strong>{{$item->nombre}} {{$item->apaterno}} {{$item->amaterno}}</strong> </span>
                                        <span class="">Tipo: <strong class="text-warning">{{ucfirst($item->tipo_solicitud)}}</strong> </span>
                                    </div>    
                                    <div class="text-right">                                                                      
                                        <!-- <span class="badge badge-warning badge-pill">autorizar</span>  -->
                                    </div>                 
                                </button>
                            @endif
                        @endforeach
                        @if($total_solicitudes==0)
                            <p class="text-center text-justify">No existen Solicitudes Pendientes.</p>
                        @endif
                    </div>
                    <br>
                </div>
                <div class="col-md-5" >
                    <div class="text-center">
                        <h4>Detalle Solicitud</h4>
                    </div>
                    <div class="card card_detalle_solicitud">
                        <div id="titulo_tipo" class="text-center"></div>
                        <div class="d-flex justify-content-between mt-3"> 
                            <div id="dato_empleado" class="d-flex flex-column col-6"></div>
                            <div id="img_empleado" class="col-6"></div>
                            
                        </div>
                        <div class="d-flex justify-content-between mt-3">  
                            <div id="fechas_solicitud" class="col-4"></div>         
                            <div id="detalle_solicitud" class="col-8"></div>
                        </div>
                        <div id="nota_solicitud" class="form-group text-center mx-1 my-0"></div>
                        <div id="autoriza_cancela" class="row justify-content-center"></div>
                        </br>       
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6 col-sm-12">
                    <div class="form-row">
                        <div class="form-group col-md-3 col-xs-12">
                            <h6 class="mt-2"><strong> Estatus del periodo:</strong></h6>
                        </div>
                        <div class="form-group col-md-3 col-xs-12">
                            <button class="btn btn-danger btn-sm btn-block" >Fecha vencida</button>
                        </div>
                        <div class="form-group col-md-3 col-xs-12">
                            <button class="btn btn-success btn-sm btn-block">Fecha actual</button>
                        </div>
                        <div class="form-group col-md-3 col-xs-12">
                            <button class="btn btn-primary btn-sm btn-block">Fecha próxima</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="text-center">
                        <h4>Solicitudes Autorizadas</h4>
                    </div>
                </div>
            </div>
            <table class="table w-100 tabla_datos empleados"  id="idTable">
                <thead>
                    <tr>
                        <th>Nombre completo</th>              
                        <th>Periodo solicitado</th>
                        <th>Archivo adjunto</th>
                        <th>Situación</th>   
                    </tr>
                </thead>
                <tbody>
                    @foreach($datos_solicitud as $d)
                        @if($d->estatus_solicitud == 1)
                        @php                    
                            //$fechas_aut = json_decode($d->fechas, true);
                            $fechas_aut = explode(',', $d->fechas_solicitud);
                            $fecha_actual = now()->format('Y-m-d');
                        @endphp
                        <tr>
                            <td>{{$d->nombre}} {{$d->apaterno}} {{$d->amaterno}}</td>                  
                            <td>
                                @foreach($fechas_aut as $key => $fa)
                                    @if($fa == $fecha_actual)
                                        <input class="border rounded datepicker_icon border-0 bg-green text-center" id="{{$d->id_empleado}}_{{$key}}" data-id_emp="{{$d->id_empleado}}" data-id_elem="{{$key}}" value="{{$fa}}" size="7" title="Fecha Actual" readonly> 
                                    @elseif($fa > $fecha_actual)
                                        <input class="border rounded datepicker_icon border-0 bg-blue text-center" id="{{$d->id_empleado}}_{{$key}}" data-id_emp="{{$d->id_empleado}}" data-id_elem="{{$key}}" value="{{$fa}}" size="7" title="Fecha Próxima" readonly> 
                                    @elseif($fa < $fecha_actual)
                                        <input class="border rounded datepicker_icon border-0 bg-red text-center" id="{{$d->id_empleado}}_{{$key}}" data-id_emp="{{$d->id_empleado}}" data-id_elem="{{$key}}" value="{{$fa}}" size="7" title="Fecha Vencida" readonly> 
                                    @endif                              
                                @endforeach
                            </td>
                            <td> <a href="{{asset('storage/repositorio/'.$id_empresa.'/'.$d->id_empleado.'/vacaciones/'.$d->file_solicitud)}}" target="_blank">{{$d->file_solicitud}}</a> </td>
                            <td> <span class="text-success"><strong>Autorizado</strong></span> </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('parametria.puestos.crear-editar-modal')

    </div>
    @include('includes.footer')


    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js'></script>
    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{ asset('js/datetimepicker/ui/i18n/ui.datepicker-es.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-multidatespicker/1.6.6/jquery-ui.multidatespicker.js"></script>
    <script>
        let dataSrc = [];
        let table = $('#idTable').DataTable({
            scrollY: '65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por puesto',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {

                let api = this.api();

                api.cells('tr', [1]).every(function() {
                    let data = $('<div>').html(this.data()).text();
                    if (dataSrc.indexOf(data) === -1) {
                        dataSrc.push(data);
                    }
                });
                dataSrc.sort();

                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value) {
                            api.search(value).draw();
                        }
                    });
                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            }
        });  
    </script>
    <script>    
        $(function(){ 
            actualiza_estatus();
            // muestraLoader("#loading");               
            $('[data-toggle="tooltip"]').tooltip();
            $( ".list-group-item" ).click(function(e) {  
                // console.log($(e.currentTarget).data('id-solicitud'));          
                let id_empleado = $(e.currentTarget).data('id-empleado');   
                let id_solicitud_pendiente = $(e.currentTarget).data('id-solicitud');      
                detalle_empleado(id_empleado, id_solicitud_pendiente); 
    
                $("#btnAutoriza_solicitud").click(function(){
              
                    let id_solicitud = $("#id_solicitud").val();
                    let id_empleado = $("#id_empleado").val();
                    // console.log($("#lbl_url_archivo").text().trim().length); return;       
                            
                    if($("#validar_fechas_solicitados").val() == 0){                     
                        if($("#lbl_url_archivo").text().trim().length > 0){
                            autoriza_solicitud(id_solicitud, id_empleado);
                        }else{
                          
                            swal("", "Se requiere document. Intentelo nuevamente", "error");
                        }
    
                    }else{
         
                        swal("", "Existen fechas no vigentes, verifique por favor..!! ", "error");
                    }
                    
                });
    
                $("#btnCancela_solicitud").click(function(){ 
                    // console.log('xxxx');
                    let id_solicitud = $("#id_solicitud").val();
                    let id_empleado = $("#id_empleado").val();

                    swal({
                        title: "Cancelar solicitud",
                        content: {
                            element: "input",
                            attributes: {
                            placeholder: "Ingrese un motivo de cancelación",
                            type: "text",
                            required:true
                            },
                        },
                    }).then((value) => {
                        
                        if (value) {
                            cancela_solicitud(id_solicitud, id_empleado, value.trim());

                            swal("Poof! Your imaginary file has been deleted!", {
                            icon: "success",
                            });
                        } else {
                            swal("", "Debe ingresar un motivo. Intentelo nuevamente", "error");
                        }
                    });
                    
                });
    
            }); 
    
            datepicker_show();               
                   
        });       
    
        function detalle_empleado(id_empleado, id_solicitud_pendiente){        
            let array_datos_solicitud = JSON.parse($("#datos_solicitud").val());
            let id_empresa_archivo = $("#id_empresa").val();
    
            array_datos_solicitud.forEach( function(valor, indice, array) {
                // console.log(id_solicitud_pendiente,'-', id_empleado); return;
                if(id_empleado === array_datos_solicitud[indice].id_empleado && id_solicitud_pendiente === array_datos_solicitud[indice].id){               
                    
                    let url_img = $("#img_e"+id_empleado).data("img");
                    let nombre_empleado = array_datos_solicitud[indice].nombre+" "+array_datos_solicitud[indice].apaterno+" "+array_datos_solicitud[indice].amaterno;
                    let puesto_empleado = array_datos_solicitud[indice].puesto;
                    let tipo_solicitud = `<strong>`+array_datos_solicitud[indice].tipo_solicitud_mayuscula+`</strong>`;
                    let nombre_autoriza = JSON.parse(array_datos_solicitud[indice].usuario_autoriza);
                    let fecha_solicitud = array_datos_solicitud[indice].fecha_solicitud;
                    let dias_vacaciones = array_datos_solicitud[indice].dias_vacaciones2;
                    let nota = array_datos_solicitud[indice].nota;
                    let id_solicitud = array_datos_solicitud[indice].id;   
                    let nombre_archivo_solicitud = array_datos_solicitud[indice].file_solicitud;
                    let url_archivo_solicitud = '';
    
                    let fechas = "";
                    let fecha_vencidas = 0;
                    let formato_fecha_hoy = new Date();
                    let fecha_hoy = new Date(formato_fecha_hoy.getFullYear(), formato_fecha_hoy.getMonth(), formato_fecha_hoy.getDate());
             
                    let array_fechas = array_datos_solicitud[indice].fechas_solicitud.split(',').sort();               
                    for(let x=0; x<array_fechas.length; x++){
                        let formato_fecha = new Date(array_fechas[x]);                    
                        let validar_fecha_solicitud = new Date(formato_fecha.getFullYear(), formato_fecha.getMonth(), formato_fecha.getDate()+1);
                    
                        if(validar_fecha_solicitud <= fecha_hoy){            
                            fecha_vencidas++;
                        } 
                        fechas += `<div><input class="text-center w-75 border-0" id="${id_empleado}_${x}" type="text" value="${array_fechas[x]}" readonly> <i class="fas fa-calendar-alt datepicker_icon" data-id_emp="${id_empleado}" data-id_elem="${x}" value="${array_fechas[x]}"></i></div>`;
                    }
                    let elemento_img_empleado;
                    (url_img.indexOf('public')!== -1) ? elemento_img_empleado = `<img class="img-fluid rounded-circle" src="{{asset('${url_img}')}}" width="104" height="36">` : elemento_img_empleado = `<img class="img-fluid rounded-circle" src="{{asset('${url_img}')}}" width="104" height="36">`;
                    
                    let dato_empleado = `
                            <div class="mt-5" >     
                                <input type="hidden" id="id_solicitud" name="id_solicitud" value="${id_solicitud}"> 
                                <input type="hidden" id="id_empleado" name="id_empleado" value="${id_empleado}">                  
                                <span class=""><strong>${id_empleado} ${nombre_empleado}</strong> </span>
                                <span class="">${puesto_empleado}</span>
                            <div> `;
                    
                    let fechas_solicitud = `                    
                        <div id="dias_solicitados" class="box box-solid box-default">
                            <input type="hidden" id="validar_fechas_solicitados" value="${fecha_vencidas}">
                            <div class="box-header text-center">
                                <span>Días Solicitados:</span>
                            </div>
                            <div class="box-body text-center">
                                ${fechas}
                            </div>
                            
                        </div> `;
                        let url_path=`{{asset('storage/repositorio')}}`;
                        let url_imagen =`${url_path}/${id_empresa_archivo}/${id_empleado}/vacaciones/${nombre_archivo_solicitud}`;
                        
                    let detalle_solicitud = `
                        <div class="">
                            <div class="mx-1">Autoriza: <strong>${nombre_autoriza.join(', ')}</strong></div>
                            <div class="mx-1">Fecha Solicitud: <strong>${fecha_solicitud}</strong></div>
                            <div class="mx-1">Archivo Adjunto: <a id="lbl_url_archivo" href="${url_imagen}" target="_blank">${nombre_archivo_solicitud}</a></div>
                            <div class="d-flex justify-content-left mx-1"> Días disponibles:<h5><span class="badge badge-success badge-pill mx-1">${dias_vacaciones}</span></h5> </div>
                        </div> `;
                    
                    let nota_solicitud = `
                        <label class=""><strong>Nota:</strong></label>
                        <p class="form-control px-1 text-left">${nota}</p> `;
                    
                    let autoriza_cancela = `
                        <div class="col-sm-3 col-md-1 col-lg-3"  id="btnCancela_solicitud">
                            <button class="button-style-cancel">Cancelar</button>
                        </div>
                        <div class="col-sm-3 col-md-1 col-lg-3" id="btnAutoriza_solicitud" >
                            <button class="button-style">Autorizar</button>
                        </div>`;
    
                    $("#img_empleado").empty();
                    $("#img_empleado").append(elemento_img_empleado);
    
                    $("#dato_empleado").empty();
                    $("#dato_empleado").append(dato_empleado);
    
                    $("#fechas_solicitud").empty();
                    $("#fechas_solicitud").append(fechas_solicitud);
    
                    $("#titulo_tipo").empty();
                    $("#titulo_tipo").append(tipo_solicitud);
    
                    $("#detalle_solicitud").empty();
                    $("#detalle_solicitud").append(detalle_solicitud);
    
                    $("#nota_solicitud").empty();
                    $("#nota_solicitud").append(nota_solicitud);
    
                    $("#autoriza_cancela").empty();
                    $("#autoriza_cancela").append(autoriza_cancela);
    
                    $('[data-toggle="tooltip"]').tooltip();   
                    datepicker_show();                           
                  
                }
    
            });
            
        }
    
        function datepicker_show(){
            $('.datepicker_icon').click(function(e) {
                id_dtpicker = e.currentTarget.attributes.value.nodeValue;
                id_emp = e.currentTarget.dataset.id_emp;
                id_elem = e.currentTarget.dataset.id_elem;     
                // console.log(id_emp);
                $("#"+id_emp+"_"+id_elem).datepicker({ 
                    // startDate: '-3d',	   		
                    dateFormat: 'yy-mm-dd',		
                    minDate: id_dtpicker,	
                    maxDate: id_dtpicker,	
                    // autoclose: true,	
                    // closeOnDateSelect: true		
                }); 
                //.click(function() { $(this).hide(); });
    
                $("#"+id_emp+"_"+id_elem).focus();
                      
            });
        }
    
        function autoriza_solicitud(id_solicitud, id_empleado){
            // console.log(id_solicitud); return;
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: 'empleados-autorizar-solicitud',
                type: 'GET',
                // data: { 'id_empresa': id_empresa, 'rutav1': ruta_v1, 'rutav2': ruta_v2 },
                data: {_token: CSRF_TOKEN, 'id_solicitud':id_solicitud, 'id_empleado': id_empleado},
                beforeSend: function() {	        	
                    muestraLoader("#loading");
                },
                success: function (data) {
                    ocultaLoader("#loading");	
                    // console.log(data);
                    window.location.href = 'empleados-vacaciones';
                    
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {			    
                    ocultaLoader("#loading");                
                }
            });
            
        }
    
        function cancela_solicitud(id_solicitud, id_empleado, motivo_cancelacion){
            // console.log(id_solicitud); return;
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: 'empleados-cancelar-solicitud',
                type: 'GET',
                // data: { 'id_empresa': id_empresa, 'rutav1': ruta_v1, 'rutav2': ruta_v2 },
                data: {_token: CSRF_TOKEN, 'id_solicitud':id_solicitud, 'id_empleado': id_empleado, 'motivo_cancelacion': motivo_cancelacion},
                beforeSend: function() {	        	
                    muestraLoader("#loading");
                },
                success: function (data) {
                    ocultaLoader("#loading");	
                    // console.log(data);
                    window.location.href = 'empleados-vacaciones';
                    
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {			    
                    ocultaLoader("#loading");                
                }
            });        
        }
    
        function actualiza_estatus(){
            // console.log(id_solicitud); return;
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: 'empleados-actualizar-estatus',
                type: 'GET',
                // data: { 'id_empresa': id_empresa, 'rutav1': ruta_v1, 'rutav2': ruta_v2 },
                data: {_token: CSRF_TOKEN},
                beforeSend: function() {	        	
                    // muestraLoader("#loading");
                },
                success: function (data) {
                    // ocultaLoader("#loading");	
                    console.log(data);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {			    
                    // ocultaLoader("#loading");                
                }
            });
            
        }    
    
        function muestraLoader(nombreObj){        
            $(nombreObj).html('<div id="divLoading"><img src={{asset("public/img/spinnerN.gif")}} width="150" height="150"></div>');
        }
    
        function ocultaLoader(nombreObj){
            $(nombreObj).html('&nbsp;');
        }
    
    </script>