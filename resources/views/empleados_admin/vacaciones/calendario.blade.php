<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('includes.head')

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.6.0/main.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-multidatespicker/1.6.6/jquery-ui.multidatespicker.css" rel="stylesheet"/>
<style>
a{
    color: #000;}

a:hover{
    color: #f39c12;}
</style>
<body>
    @include('includes.navbar')
    @php
    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Vacaciones',
        'subtitle'=>'Nueva solicitud', 'img'=>'img/header/parametria/icono-puestos.png',
        'route'=>'empleados.vacaciones'])

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
                    <div type="button" class="button-style ml-3 mb-3" data-toggle="modal" data-target="#nueva_solicitud"> <img src="/img/icono-crear.png" class="button-style-icon">Generar solicitud</div>
                </div>
            </div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <div class="card my-5 mx-5 card_estatus">
                <div class="d-flex justify-content-between row">
                    <div class="form-group text-center col-xs-12 col-md-12 col-lg-5">
                        <label>Solicitudes Autorizada:</label> 
                        <div>
                            <span class="fecha_vencida rounded px-1">Fecha Vencida</span>
                            <span class="fecha_actual rounded px-1">Fecha Actual</span>
                            <span class="fecha_proxima rounded px-1">Fecha Próxima</span>
                        </div>
                    </div>
            
                    <div class="form-group text-center col-xs-12 col-md-12 col-lg-5"> 
                        <label>Solicitudes Pendientes:</label> 
                        <div>
                            <span class="solicitud_pendiente rounded px-1">Solicitud Pendiente</span>        
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <p class="text-center text-justify text-danger text_sin_fechas">No existen solicitudes pendientes o autorizadas.</p>
            </div>
            
            <div class="mb-5">
                <div id='calendar' class="mx-5"></div>
            </div>
        </div>
        @include('empleados_admin.vacaciones.modals.nueva_solicitud_modal')
        @include('empleados_admin.vacaciones.modals.editar_solicitud_modal')
    </div>

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{ asset('js/datetimepicker/ui/i18n/ui.datepicker-es.js') }}"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.6.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.6.0/locales-all.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-multidatespicker/1.6.6/jquery-ui.multidatespicker.js"></script>
    <script> 
        $(function(){  
            window.setTimeout(function () { 
                $(".alert").alert('close'); 
            }, 10000);
            // valida_solicitud_usuario();  
            let fechas_vac;   
            let fechas_vac_valida = [];     
            let date = new Date();    
            $("#datepicker").multiDatesPicker({
                changeYear: true,
                changeMonth: true,
                minDate: date,     
                beforeShowDay : $.datepicker.noWeekends,
                dateFormat: 'yy-mm-dd',
                // addDates: [date.setDate(13), date.setDate(23)],
                // addDates: ["23/04/2021", "21/04/2021"],
                // disabled: true,
                onSelect: function (date){
                    fechas_vac_valida = $('#datepicker').multiDatesPicker('getDates');
                    fechas_vac = JSON.stringify($('#datepicker').multiDatesPicker('getDates'));
                    console.log(fechas_vac);
                    
                    if ( $("#fechas_datepicker").length > 0 ){
                        $("#fechas_datepicker").val("");
                    
                    }
                    
                    $("#form_nueva_solicitud").append('<input type="hidden" id="fechas_datepicker" name="fechas_datepicker" value="'+btoa(fechas_vac)+'">');
                }            
                
            });
            // $('#datepicker').multiDatesPicker('value', '20/04/2021, 21/04/2021');        

            // $( "#datepicker" ).datepicker("setDate","20/04/2021", "21/04/2021");
            // $( "#datepicker" ).datepicker("setDate","22/04/2021");        

            $("#fechainicio").datepicker({ 
                // startDate: '-3d',	   		
                dateFormat: 'yy-mm-dd',			
                changeYear: true,
                changeMonth: true,
                onSelect: function (date) {	
                    var fechaFinal=date.split("-");
                    // console.log(fechaFinal);				
                    // var fechaFinal2=fechaFinal[0]+"-"+fechaFinal[1]+"-"+(parseInt(fechaFinal[2])+1);            
                    
                    $("#fechafin").prop('disabled', false); 	
                    $("#fechafin" ).val('');
                    $("#fechafin" ).datepicker( "option", "minDate", date );
                    $("#fechafin").datepicker({	
                        changeYear: true,
                        changeMonth: true,
                        dateFormat: 'yy-mm-dd',	
                        minDate: date
                    });
                        
                }
            });

            $('#form_nueva_solicitud').submit( function() {
            
                let valida = false;          
                let txtAutoriza = $("#txtAutoriza1").val(); 
                // let resultado_validacion = valida_solicitud_usuario();
                // console.log(resultado_validacion);
                // return false;   
                if(txtAutoriza.trim().length>0){
                    if(fechas_vac_valida.length > 0){
                        
                        let id_empleado = $("#empleado").val();
                        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        let request = $.ajax({
                            async: false,
                            url: 'empleado-validar-solicitud',
                            method: 'GET',
                            data: {_token: CSRF_TOKEN, 'id_empleado': id_empleado},
                            // dataType: "html"
                            });                        
                            request.done(function( data ) {                             
                                // ocultaLoader("#loading");                                         
                                if(data.length>0){                                
                                    // console.log(data[0].estatus_solicitud);  
                                    alertify.error('Ya existe una solicitud');  
                                    valida = false;  
                                    // return valida;                          
                                }else{
                                    $('#btn_subir').attr('disabled', true).text('ESPERE...');                                
                                    valida = true;                                
                                }
                            });                        
                            request.fail(function( jqXHR, textStatus ) {
                                // alert( "Request failed: " + textStatus );
                                console.log(textStatus, ':', jqXHR);
                            });                                           
                        
                    }else{
                    
                        swal("", "Seleccione por lo menos una fecha.", "error");                
                    }
                    
                }else{
                    $( "#txtAutoriza1" ).focus();
                    swal("", "Ingrese al menos un nombre del responsable para autorizar.", "error");
                            
                }     
                
                return valida;
                
            });    
            
            
                
        });    
   
        document.addEventListener('DOMContentLoaded', function() {  
            let calendarEl = document.getElementById('calendar');
            let calendar = new FullCalendar.Calendar(calendarEl, {          
                initialView: 'dayGridMonth',
                events: 'empleados-fechas-vacaciones',          
                eventClick: function(info) {
                    // console.log(info.event.id);  
                    // console.log(info.event.extendedProps.fechas);              
                    
                    if(info.el.href.trim().length>0){
                        let edit_id = info.event.id;
                        let edit_tipo = info.event.extendedProps.tipo;
                        let tipo_solicitud_mayuscula = info.event.extendedProps.tipo_solicitud_mayuscula;
                        let edit_id_empleado = info.event.extendedProps.id_empleado;
                        let edit_autoriza = JSON.parse(info.event.extendedProps.autoriza);
                        let edit_nota = info.event.extendedProps.nota;
                        let url_archivo = info.event.extendedProps.url_archivo;
                        let url_archivo2 = info.event.extendedProps.url_archivo2;
                        let file_solicitud = info.event.extendedProps.file_solicitud;
                        let periodo = info.event.extendedProps.periodo;
                        // var fechas = JSON.parse(info.event.extendedProps.fechas);
                        // console.log(edit_autoriza[1]);
    
                        $("#edit_tipo option[value='"+edit_tipo+"']").prop("selected", true);
                        $("#edit_empleado option[value='"+edit_id_empleado+"']").prop("selected", true);     
                        $("#edit_periodo option[value='"+periodo+"']").prop("selected", true);               
                        $("#edit_autoriza_solicitud option[value='"+edit_autoriza+"']").prop("selected", true);
                        $("#form_editar_solicitud").append('<input type="hidden" name="id_editar" value="'+edit_id+'">');
                        $("#form_editar_solicitud").append('<input type="hidden" name="id_empleado_edit" value="'+edit_id_empleado+'">');
                        $("#form_editar_solicitud").append('<input type="hidden" name="actualiza_archivo" value="'+url_archivo+'">');
                        $("#form_editar_solicitud").append('<input type="hidden" id="tipo_solicitud_nombre_edit" name="tipo_solicitud_nombre" value="'+tipo_solicitud_mayuscula+'">');
                        $("#edit_textAreaNota").val(edit_nota);
                        $("#txtAutoriza1_editar").val(edit_autoriza[0]);
                        $("#txtAutoriza2_editar").val(edit_autoriza[1]);
                        $("#txtAutoriza3_editar").val(edit_autoriza[2]);
                        $("#archivo_solicitud").attr('href', url_archivo2);
                        $("#archivo_solicitud").prop('value', url_archivo);
                        $("#archivo_solicitud").text(file_solicitud);
                        // $('#datepicker_edit').multiDatesPicker('setDate', null);
                        // $('#datepicker_edit').multiDatesPicker('resetDates');
                        // $('#datepicker_edit').multiDatesPicker('addDates', fechas);
               
                        $('#editar_solicitud').modal('show');
                    }               
                },
                /*events:[   
                        {
                            'title':  'My Event juan',
                            description: 'Lecture',
                            start:  "2021-04-21",
                            allDay: true,                        
                        },{
                            title:  'My Event jn',
                            description: 'Lecturexx',
                            start:  '2021-04-15',
                            allDay: 1
                        }   
                ] ,*/
                eventDidMount: function(info) {
                    // console.log(info.el);               
                    $(".card_estatus").show(); 
                    // $(".text_sin_fechas").hide();
                    $(info.el).attr('title', info.event.extendedProps.description);
                }          
                    
            });
        
            calendar.render();
            calendar.setOption('locale', 'es'); 
            
            // var date_view = calendar.getDate(); 
            // console.log(date_view.getDate());
            // console.log(date_view.getMonth()+1);
            // console.log(date_view.getFullYear());  
            
            $(".fc-prev-button").click(function () {
                // console.log(date_view.toISOString());
            });
            $(".fc-next-button").click(function () {
                // console.log(date_view.toISOString());
            });
            $(".fc-today-button").click(function () {
                // console.log(date_view.toISOString());
            });

        }); 

        function valida_solicitud_usuario(){
            let id_empleado = $("#empleado").val();
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                async: false,
                url: 'validar-solicitud-empleado',
                type: 'GET',
                // data: { 'id_empleado': id_empleado },
                data: {_token: CSRF_TOKEN, 'id_empleado': id_empleado},
                beforeSend: function() {	        	
                    // muestraLoader("#loading");
                },
                success: function (data) {
                    // ocultaLoader("#loading");	
                    // console.log(data[0].estatus_solicitud);  
                    // console.log(data.length);              
                    if(data.length>0){
                        resultado_validacion = data[0].estatus_solicitud;
                        return data[0].estatus_solicitud;
                        // console.log(resultado_validacion);  
                    }else{
                        resultado_validacion = 0;
                        return 0;
                        // console.log(resultado_validacion);
                    }
                    
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {			    
                    // ocultaLoader("#loading");                
                }
            });	
        }
</script>
@include('includes.footer')