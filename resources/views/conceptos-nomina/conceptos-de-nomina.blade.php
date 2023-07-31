<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>
<div class="container">
    
@include('includes.header',['title'=>'Conceptos de nómina', 
'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-conceptos.png',
 'route'=>'bandeja'])

	<div class="row">
		<div class="col-md-12">
		    <a data-toggle="modal" data-target="#crearconceptooModal" title="Crear concepto de nómina">
                <button type="button" class="button-style">
                    <img src="/img/icono-crear.png" class="button-style-icon">Crear
                </button>
		    </a>

            <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-3" id="div_buscar"> </div>
		    <br>
		    <br>
		</div>
	</div>


    @foreach ($errors->get('nombre_concepto') as $error)
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ $error }}
            </div>
        </div>
    @endforeach

    @foreach ($errors->get('nombre_corto') as $error)
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ $error }}
            </div>
        </div>
    @endforeach


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
        <table class="table w-100 text-center" id="conceptos_nomina">
            <thead>
                <tr>
                    <th width="22%">Nombre</th>
                    <th width="20%">Tipo</th>
                    <th>Finiquito/Nomina</th>
                    <th>Situación</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($conceptos as $concepto)
                    <tr id="{{$concepto->id}}">
                            <td width="20%">{{$concepto->nombre_concepto}}</td>
                            <td>{{ ($concepto->tipo) ? 'DEDUCCIÓN' : 'PERCEPCIÓN'}}</td>
                            <td width="27%">
                                {{($concepto->nomina) ? 'NOMINA' : '' }}
                                {{($concepto->finiquito) ? 'FINIQUITO' : '' }}
                            </td>
                            <td>
                                {{($concepto->file_rool < 250) ? 'FISCALES' : 'SINDICALES'}}
                            </td>
                            <td width="150px">
                                <a class="editar_concepto" onclick="editarConcepto(this)" data-id="{{$concepto->id}}" data-nombre="{{$concepto->nombre_concepto}}" data-corto="{{$concepto->nombre_corto}}" data-file_rool="{{$concepto->file_rool}}" data-tipo="{{$concepto->tipo}}" data-proceso="{{$concepto->tipo_proceso}}" data-nomina="{{ $concepto->nomina}}" data-finiquito="{{$concepto->finiquito}}" data-sat="{{ $concepto->codigo_sat}}" data-rutinas="{{$concepto->rutinas}}" data-alterno="{{$concepto->id_alterno}}" data-toggle="modal" data-target="#crearconceptooModal" ref="Crear concepto de nómina" title="Editar Concepto" >
                                <img src="/img/icono-editar.png" class="button-style-icon m-2">
                            	</a>

                            <a class="borrar" data-id="{{$concepto->id}}" data-alterno="{{$concepto->id_alterno}}" ref="Eliminar concepto de nómina" alt="Borrar Concepto" title="Borrar Concepto">
                            <img src="/img/eliminar.png" class="button-style-icon m-2">
                            </a>
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    <form method="post" id="submit_concepto_eliminar" action="{{ route('conceptos.eliminarconcepto') }}">
        @csrf
        <input type="hidden" name="idconcepto" id="id_delte" value="">
        <input type="hidden" name="idalterno" id="id_delte_alterno"  value="">
    </form>


	</div>

</div>



@include('includes.footer')
@include('conceptos-nomina.crear-concepto-de-nomina')


<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>
<script type="text/javascript">

        let dataSrc = [];
        let table = $('#conceptos_nomina').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
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
</script>


<script type="text/javascript">

    $(".borrar").click(function(){
       let id = $(this).data('id');
        let alterno = $(this).data('alterno');
                document.getElementById("id_delte").value = id;
                document.getElementById("id_delte_alterno").value = alterno;
        swal({
            title: "Estas seguro de eliminar el registro",
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
                document.getElementById("submit_concepto_eliminar").submit();

            } else {
                swal("La accion fue cancelada!");
            }
        });

    });

    function editarConcepto(event){
        let id = $(event).data('id');
        let nombre = $(event).data('nombre');
        let corto = $(event).data('corto');
        let tipo = $(event).data('tipo');
        let proceso = $(event).data('proceso');
        let nomina = $(event).data('nomina');
        let finiquito = $(event).data('finiquito');
        let sat = $(event).data('sat');
        let rutinas = $(event).data('rutinas');
        let alterno = $(event).data('alterno');
        let file_rool = $(event).data('file_rool');

        document.getElementById("id_upd").value = id;
        document.getElementById("id_alterno_upd").value = alterno;
        document.getElementById("nombre_concepto").value = nombre;
        document.getElementById("nombre_corto").value = corto;

        if(file_rool< 250){
            document.getElementById("fiscal").checked = true;
        }else{
            document.getElementById("sindical").checked = true;
        }

        if(tipo == 1){
            document.getElementById("deduccion").checked = true;
        }else{
            document.getElementById("percepcion").checked = true;
        }

        $('#codigo_sat').val(sat);


        if(proceso == 0){ document.getElementById("captura").checked = true; }

        if(proceso == 1){ document.getElementById("calculo").checked = true; }

        if(proceso == 2){ document.getElementById("programado").checked = true; }

        $("#rutinas").val(rutinas);

        if(finiquito == 1){ document.getElementById("finiquito").checked = true; }

        if(nomina == 1){ document.getElementById("nomina").checked = true; }
    }


</script>
