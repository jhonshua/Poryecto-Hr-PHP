<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@include('contratos.renovar-contrato-modal')
<style>
    .dropdown:hover .dropdown-menu {
        display: block;
        margin-top: 0; 
    }  
    .dropdown-menu spam a{
        color: #000;
    }
    .dropdown:hover>.dropdown-menu spam:hover {
        background-color: #F0C018;  
        transition: background-color 0.5s ease-out;        
    }
    .dropdown:hover>.dropdown-menu spam:hover a, .dropdown:hover>.dropdown-menu spam:hover spam{        
        color: #fff;       
    } 
</style>

<div class="container">
    
@include('includes.header',['title'=>'Vigencia de Contratos',
        'subtitle'=>'Empleados', 'img'=>'/img/catalogo-empleado.png',
        'route'=>'empleados.empleados'])
		
    <div class="row d-flex justify-content-between">
        <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
            <div class="col-xs-12 col-md-12 col-lg-3 mb-3 ml-3 px-0">
                <select name="" id="vencidos_vigentes" class="form-control input-style-custom select-clase" style="width: 100%!important;">
                    <option value="">MOSTRAR TODOS</option>
                    <option value="VIGENTE">VIGENTES</option>
                    <option value="VENCIDO">VENCIDOS</option>
                </select>
            </div>                
        </div>
        <div class="dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
    </div>

    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif
	<div class="article border">	  
        <table class="table table-hover contratos"> 
            <thead>
                <tr>
                    <th width="8%">id</th>
                    <th width="28%">Empleado</th>
                    <th width="18%">Fecha Contrato</th>
                    <th width="12%">Estatus</th>
                    <th width="12%">Fecha Vencimiento</th>
                    <th width="13%">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contratos as $contrato)
                <tr id="{{$contrato->id}}" class="{{($contrato->fecha_vencimiento <= date('Y-m-d H:i:s') && $contrato->fecha_vencimiento != '0000-00-00 00:00:00') ? 'ven' : 'vig' }}">
                    <td width="8%">
                        {{$contrato->id_empleado}}
                    </td>
                    <td width="30%">
                        {{$contrato->apaterno}} {{$contrato->amaterno}} {{$contrato->nombre}}
                    </td>
                    <td width="20%">{{formatoAFecha($contrato->fecha_contrato)}}</td>
                    <td width="12%" class="font-weight-bold">
                        {!!($contrato->fecha_vencimiento <= date('Y-m-d H:i:s') && ($contrato->fecha_vencimiento != '0000-00-00 00:00:00' && $contrato->fecha_vencimiento != NULL)) ? '<span class="text-danger ">VENCIDO' : '<span class="text-success">VIGENTE'!!}</span>
                    </td>

                    <td width="10%">
                        @if ($contrato->fecha_vencimiento== '0000-00-00 00:00:00' || $contrato->fecha_vencimiento== NULL)
                            <div class="px-3 py-1 font-weight-bold text-center">INDETERMINADO</div>
                        @elseif($contrato->fecha_vencimiento > date('Y-m-d H:i:s'))
                            <div class="px-3 py-1 text-success font-weight-bold text-center">{{formatoAFecha($contrato->fecha_vencimiento)}}</div>
                        @else
                            <div class="px-3 py-1 bg-danger text-white text-center">{{formatoAFecha($contrato->fecha_vencimiento)}}</div>
                        @endif
                    </td>
                    <td width="13%" class="position-relative text-center">
                        <div class="btn-group">
                            @if($contrato->fecha_permite_renovar <= date('Y-m-d H:i:s') && ($contrato->fecha_vencimiento != '0000-00-00 00:00:00' && $contrato->fecha_vencimiento != NULL))
                                <a data-idempleado="{{$contrato->id_empleado}}" data-fecha_vencimiento="{{$contrato->fecha_vencimiento}}" 
                                    data-renovacion="1" data-nombre="{{$contrato->apaterno}} {{$contrato->amaterno}} {{$contrato->nombre}}" 
                                    data-urlactualizar="{{route('contratos.vigenciacontratos')}}" 
                                    class="editar  mr-2 py-1 px-2" alt="Renovar Contrato" 
                                    title="Renovar Contrato" data-toggle="modal" data-target="#renovarContratoModal">
                                    
                                    <img src="{{ asset('/img/icono-editar.png') }}" width="25px">  
                                </a>

                                @if (Session::get('usuarioPermisos')['eva_desempeno'])
                                    <a href="javascript:void(0)" class="evaluar mr-2 py-1 px-2" alt="Evaluación de Desempeño" title="Evaluación de Desempeño" data-id="{{$contrato->id}}">
                                        <img src="{{ asset('/img/grafica-contrato.png') }}" width="25px">
                                    </a>
                                @endif
                            @endif    
                                <div class="dropdown">
                                    <div class="dropdownMenu border-0" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <img src="{{ asset('/img/icono-reporte-asistencias.png') }}" width="25px">
                                    </div>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @foreach($contrato->contratos as $c)
                                        @if($c->archivo != "")                                            
                                                <spam class="dropdown-item d-flex justify-content-between mr-3">
                                                    <a href="{{$contrato->ruta}}/{{$c->archivo}}" class="" target="_blank"><u>{{$c->archivo}}</u></a>
                                                    <a href="javascript:void(0)" data-id="{{$c->id}}" data-nombre="{{$c->archivo}}" data-emp="{{$contrato->nombre}} {{$contrato->apaterno}}" class="fas fa-trash-alt text-danger borrar" title="Eliminar contrato"></a> 
                                                </spam>   
                                        @else
                                            @php 
                                                $f = new DateTime($c->fecha_contrato);
                                            @endphp                                           
                                                <spam class="dropdown-item d-flex justify-content-between mr-3">
                                                    <spam class="">{{$f->format('d-m-Y')}}</spam>
                                                    <a href="javascript:void(0)" class="fas fa-trash-alt text-danger borrar" data-id="{{$c->id}}" data-nombre="{{$f->format('d-m-Y')}}" data-emp="{{$contrato->nombre}} {{$contrato->apaterno}}"  title="Eliminar contrato"></a>
                                                </spam>                                            
                                        @endif
                                    @endforeach
                                    </div>
                                </div>                                
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>  
	</div>
</div>
<form method="post" action="{{ route('contratos.eliminarcontratoEmp') }}" id="enviar_form">
    @csrf
    <input type="hidden" name="id" id="id_form">
    <input type="hidden" name="nombre" id="nombre_form">
    <input type="hidden" name="empleado" id="empleado_form">
</form>

@include('includes.footer')

<script src="{{asset('js/helper.js')}}"></script>
<script src="{{asset('js/typeahead.js')}}"></script>
<script type="text/javascript">
    let dataSrc = [];
    let table = $('.contratos').DataTable({
        scrollY:'65vh',
        scrollCollapse: true,
        "language": {
            search: '',
            searchPlaceholder: 'Buscar registros',
            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
        },
        initComplete: function() {
            
            let api = this.api();
        
            api.cells('tr', [1]).every(function(){

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
        "drawCallback": function( settings ) {              
            $('.evaluar').click(function() {              
                swal({
                title: "Aviso",
                text: "Modulo pendiente",
                icon: "warning",
                button: "ok",
                });
            });
            $(".borrar").on("click",function(){
                let nombre = $(this).data('nombre');
                let id = $(this).data('id');
                let empleado = $(this).data('emp');        
                swal({
                    title: "¿Esta seguro de eliminar el contrato '"+ nombre +"' de '"+ empleado +"'?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                
                        swal("Espere un momento, la información esta siendo procesada", {
                            icon: "success",
                            buttons: false,
                        });
                        eliminarContrato(nombre,id,empleado);
                    } else {
                        // swal("La accion fue cancelada!");
                    }
                });
            });
            $('.dropdown').hover(function(){ 
                $('.dropdownMenu', this).trigger('click'); 
            });            
        },
    });
    table.order([1, 'asc']).draw(); 

    $(function() {        
        $('.select-clase').select2();
        //PAgina con modulos pendientes
        //alertify.alert('Aviso', 'Pagina con módulos pendientes: <br> - Generacion de contrato <br> - Evaluación de desempeño');

        $('#vencidos_vigentes').on('change', function() {
            table
                .columns(3)
                .search(this.value)
                .draw();  
        });

    });    

    

    function  eliminarContrato(nombre,id,empleado){
        $("#id_form").val(id);
        $("#nombre_form").val(nombre);
        $("#empleado_form").val(empleado);
        document.getElementById("enviar_form").submit();
    }
</script>