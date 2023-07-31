<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<div class="container">
	@include('includes.header',['title'=>'Facturador', 'subtitle'=>'Contabilidad', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])


	<div class="row">
		<div class="col-md-8">
            <a href="{{ route('factura.nueva') }}" class="btn button-style" >
                    <i class="fa fa-plus"></i> Nueva 
            </a>      
        </div>
		<div class="col-md-4">
			<div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
		</div>
	</div>

	<div class="article border mt-3"> 
        <table class="table facturas" id="fact-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Emisora</th>
                    <th>Metodo de Pago</th>
                    <th>Forma de Pago</th>
                    <th>Monto</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

               	@foreach($facturas as $fac)
                	<tr v-for="d in facturas">
                        <td>{{ $fac->id }}</td>
                        <td>{{ $fac->razon_social }}</td>
                        <td>{{ $fac->metodo_string }}</td>
                        <td>{{ $fac->forma_string }}</td>
                        <td>{{ number_format($fac->total, 2)}}</td>
                        <td>
                         	@if ($fac->estatus == 1)
                            	<span class="badge badge-success"  v-if="$fac->estatus == 1">Timbrado</span>
                         	@endif
                            @if ($fac->estatus == 0)
                                <span class="badge badge-warning" v-else-if="$fac->estatus == 0 ">En proceso</span>
                            @endif
                            @if ($fac->estatus == 2)
                                <span class="badge badge-danger" v-else-if="$fac->estatus == 2">Cancelado</span>
                            @endif
                                
                            {{-- <span class="badge badge-black" v-else>Desconocido</span> --}}
                        </td>
                        <td>  
                            <div id="botonera" >
                            	@if ($fac->estatus != 1)
                            		<button class="btn btn-warning btn-sm vern edit-fact" alt="Ver Poliza" title="Ver Poliza" data-id="{{ $fac->id }}">
                            			<i class="fas fa-eye"></i>
                            		</button>
                            	@endif

                            	@if ($fac->estatus == 1)
                            		<button class="btn btn-warning btn-sm vern ver-fact" alt="Ver Poliza" title="Ver Poliza" data-id="{{ $fac->id }}">
                            			<i class="fas fa-eye"></i>
                            		</button>
                            	@endif
                                  
                            </div>    
                        </td>
                	</tr>  
                @endforeach                      
            </tbody>
        </table>
	</div>


</div>


<script src="{{asset('js/typeahead.js')}}"></script>
<script type="text/javascript">
    let dataSrc = [];
    let table = $('#fact-table').DataTable({
    scrollY:'65vh',
    scrollCollapse: true,
	    "language": {
	        search: '',
	        searchPlaceholder: 'Buscar registros por nombre',
	        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
	    },
	    initComplete: function() {
	             
	        let api = this.api();
	        api.cells('tr', [2]).every(function(){
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

    $(".ver-fact").click(function(){
    	id = $(this).data('id');
        var url =`{{route('factura.ver','*ID*' )}}`;
        url = url.replace('*ID*', id);
        window.location.assign(url);
    });

    $(".edit-fact").click(function(){
    	id = $(this).data('id');
        var url = `{{route('factura.editar','*ID*')}}`;
        url = url.replace('*ID*', id);
        window.location.assign(url);
    });

</script>