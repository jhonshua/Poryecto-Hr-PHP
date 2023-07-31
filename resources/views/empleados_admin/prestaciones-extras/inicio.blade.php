<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
    @php
   
    @endphp
    <div class="container">
        
    @include('includes.header',['title'=>'Prestaciones extras',
        'subtitle'=>'Empleados', 'img'=>'img/header/parametria/icono-isr.png',
        'route'=>'bandeja'])

        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12 mb-3 ml-3 px-0">
                    <button class="button-style mb-2" data-toggle="modal" data-target="#importarModal" type="button"><img src="{{asset('/img/icono-importar.png')}}" class="button-style-icon">Importar</button>
                    <a href="{{route('prestaciones.extras.exportar')}}" target="_blank" class="button-style text-nowrap mb-2"><img src="{{asset('/img/icono-exportar.png')}}" class="button-style-icon">Exportar</a>                    
                </div>
                <div class="">                    
                </div>
            </div>
            <div class="dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {!! session()->get('success') !!}
            </div>
        </div>
        @endif
        @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {!!session()->get('danger') !!}
            </div>
        </div>
        @endif
        <div class="article border">
            <table class="table w-100 impuestos" id="tabla_prestaciones_extras">
                <thead>
                    <tr class="text-center">
                        <th width="">ID</th>
                        <th width=""># Emp.</th>
                        <th width="">Nombre</th>                       
                        <th width="">RFC</th>
                        <th width="">Fecha de alta</th>
                        <th width="">Antigüedad</th>
                        <th width="">Estatus</th>
                        <th width="">No. certificado</th>
                        <th width="">Valor seguro gastos médicos</th>
                        <th width="">Valor plan espejo</th>
                        <th width="">Acciones</th>
                    </tr>
                </thead>
                    <tbody>
                    @foreach ($empleados as $empleado)
                        <tr 
                            id="{{$empleado->empId}}"
                            data-id="{{$empleado->empId}}"
                            data-rfc="{{$empleado->rfc}}"
                            data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}"
                        >

                            <td width="40px">{{$empleado->empId}}</td>
                            <td width="60px">{{$empleado->numero_empleado}}</td>
                            <td width="150px">
                                    {{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}
                            </td>
                            <td width="80px">{{$empleado->rfc}}</td>
                            <td width="110px">{{formatoAFecha($empleado->fecha_alta)}}</td>
                            <td width="90px">
                                {{Carbon\Carbon::parse($empleado->fecha_alta)->diff(Carbon\Carbon::now())->format('%y años %m meses')}}
                                {{-- {{Carbon\Carbon::createFromDate(1991, 7, 19)->diff(Carbon\Carbon::now())->format('%y años, %m meses')}} --}}
                            </td>
                            <td width="100px">
                                <select name="estatus" id="estatus" class="form-control">
                                    <option value="0" {{($empleado->estatus_extras == 0) ? 'selected' : ''}}>INACTIVO</option>
                                    <option value="1" {{($empleado->estatus_extras == 1) ? 'selected' : ''}}>ACTIVO</option>
                                </select>
                            </td>
                            <td width="110px">
                                <input type="number" name="numero_certificado" id="numero_certificado" class="form-control" value="{{$empleado->num_certificado}}" pattern="[0-9]{15}">
                            </td>
                            <td width="110px">
                                <input type="number" name="valor_seguro_gastos_m" id="valor_seguro_gastos_m" class="form-control" value="{{$empleado->valor_seguro_GM}}">
                            </td>
                            <td width="110px">
                                <input type="number" name="valor_plan_espejo" id="valor_plan_espejo" class="form-control" value="{{$empleado->valor_plan_espejo}}">
                            </td>

                            <td width="80px" class="text-center position-relative">
                                <button class="guardar button-style-custom mr-2 font-weight-bold" data-id="{{$empleado->empId}}">
                                    Guardar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
            </table>
        </div>
        @include('empleados_admin.prestaciones-extras.importar_modal')
    </div>
    @include('includes.footer')
    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_prestaciones_extras').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por tipo tabla',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [0]).every(function(){

                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }});
                dataSrc.sort();
                
                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value){
                            api.search(value).draw();
                        }
                    }
                );

                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
        });
        table.order([2, 'asc']).draw();     
        $(function(){
            $("td .guardar").click(function(){             
                let btn = $(this);
                estatus = $(this).parents('tr').find('#estatus').val();
                numero_certificado = $(this).parents('tr').find('#numero_certificado').val();
                valor_seguro_gastos_m = $(this).parents('tr').find('#valor_seguro_gastos_m').val();
                valor_plan_espejo = $(this).parents('tr').find('#valor_plan_espejo').val();
                id = $(this).data('id');

                if(estatus == '' || numero_certificado.trim() == '' || valor_plan_espejo.trim() == '' || valor_seguro_gastos_m.trim() == ''){       
                    swal("", "Todos los campos son requeridos. Por favor Verifique.", "warning");
                    return;
                }

                if(numero_certificado.length != 15){                
                    swal("", "El número de certificado no tiene los 15 digitos requeridos. Verifique.", "error");
                    return;
                }

                btn.text('ESPERE...').prop('disabled', true);
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                let data = {
                    'id' : id,
                    'estatus': estatus,
                    'numero_certificado': numero_certificado,
                    'valor_plan_espejo': valor_plan_espejo,
                    'valor_seguro_gastos_m': valor_seguro_gastos_m,
                    '_token': CSRF_TOKEN
                }

                let url = "{{route('prestaciones.extras.guardar')}}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    success: function (response) {
                        console.log(response);
                        if(response.ok == 1) {                           
                            swal("El registro se actualizó correctamente.", {
                                icon: "success",
                            });
                        } else {                           
                            swal("", "Ocurrió un error!", "error");
                        }
                    },
                    error: function() {                  
                        swal("", "Ocurrió un error", "error");
                    }
                }).always( function(){
                    btn.text('Guardar').prop('disabled', false);
                });
            });
        });
    </script>
