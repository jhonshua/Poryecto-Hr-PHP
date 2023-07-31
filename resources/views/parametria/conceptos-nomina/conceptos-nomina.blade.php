<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=>'Conceptos de nómina',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-nomina-conceptos.png',
        'route'=>'bandeja'])


    <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
    <br><br>
    @if(session()->has('success'))
    <div class="row">
        <div class="alert alert-success" style="width: 100%;" align="center">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>Notificación: </strong>
            {{ session()->get('success') }}
        </div>
    </div>

    @endif

    <div class="article border ">
        <table class="table w-100" id="tabla_conceptosNomina">
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
                @foreach ($conceptosNomina as $concepto)
                <tr id=" {{ $concepto->id }} ">

                    <td width="20%"> {{ $concepto->nombre_concepto }} </td>
                    <td> {{ ($concepto->tipo) ? 'DEDUCCIÓN' : 'PERCEPCIÓN' }} </td>
                    <td width="27%">
                        {{ ($concepto->nomina) ? 'NOMINA' : '' }}
                        {{ ($concepto->finiquito) ? 'FINIQUITO' : '' }}
                    </td>
                    <td>
                        {{ ($concepto->file_rool < 250) ? 'FISCALES' : 'SINDICALES' }}
                    </td>
                    <td>
                        <button data-id="{{$concepto->id}}" data-name_cuenta="{{$concepto->name_cuenta}}" data-nombre_concepto="{{$concepto->nombre_concepto}}" data-cuenta_contable="{{$concepto->cuenta_contable}}" data-integra_variables="{{$concepto->integra_variables}}" data-debe_haber="{{$concepto->debe_haber}}" data-rutinas="{{$concepto->rutinas}}" data-name_cuenta_isr="{{$concepto->name_cuenta_isr}}" data-cuenta_contable_isr="{{$concepto->cuenta_contable_isr}}" class="editar button-style" alt="Editar concepto" title="Editar concepto" data-toggle="modal" data-target="#conceptosNominaModal">Editar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    </div>
    @include('includes.footer')
    @include('parametria.conceptos-nomina.editar-conceptos-nomina')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function() {

        let dataSrc = [];
        let table = $('#tabla_conceptosNomina').DataTable({
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

        });
    </script>