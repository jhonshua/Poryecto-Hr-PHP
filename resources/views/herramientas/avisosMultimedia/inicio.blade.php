<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('includes.head')
<body>
    @include('includes.navbar')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.css">
    <link href="{{asset('css/bootstrap-switch/bootstrap-switch.min.css')}}" rel="stylesheet">
    <link href="{{asset('css/fileinput/fileinput.min.css')}}" rel="stylesheet">
    <style>
        td.details-control {
            background-size: 30px 30px !important;
            background: url("{{asset('img/icono-detalle.png')}}") no-repeat center ;
            cursor:pointer; }

        tr.details td.details-control {
            background-size: 30px 30px !important;
            background: url("{{asset('img/icono-contrario-detalle.png')}}") no-repeat center;
            cursor:pointer;}

        .bootstrap-switch.bootstrap-switch-focused{
            border-color: #e3e3e3;}
        .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-primary{
            background: #f0c018;
        }

    </style>
    @php
    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Avisos RH',
        'subtitle'=>'Información', 'img'=>'img/header/parametria/icono-puestos.png',
        'route'=>'empleados.vacaciones'])

        @if(\Session::get('mensaje'))
        <div class="alert alert-info" role="alert" id="alerta">
            {!! \Session::get('mensaje') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <div class="row">
            <div class="col-lg-9 col-md-12">
                <button class="button-style" 
                        data-toggle="modal" 
                        data-target="#avisoModal"
                        data-toggle="tooltip" 
                        title="Crear nuevo aviso" >
                        <img src="/img/icono-crear.png" class="button-style-icon">Crear aviso
                </button>
            </div>
            <div class="dataTables_filter mb-12 col-xs-12 col-md-12 col-lg-3 mt-1" id="div_buscar"></div>
        </div>
        <br>
        <div class="article border">
            <table class="table w-100 tabla_datos seleccionados"  id="listado_avisos">
                <thead>
                    <tr>
                        <th>Detalle</th>
                        <th>ID</th>
                        <th>Titulo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estatus</th>
                        <th>Tipo</th>
                        <th>Acciones</th>         
                    </tr>
                </thead>
                <tbody>
               
                </tbody>
            </table>
        </div>
        @include('herramientas.avisosMultimedia.modals.agregar')
    </div>
    @include('includes.footer')
</body>
</html>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.js"></script>
<script src="{{asset('js/bootstrap-switch/bootstrap-switch.min.js') }}"></script>
<script src="{{asset('js/fileinput/fileinput.min.js')}}"></script>
<script src="{{asset('js/fileinput/locales/es.js')}}"></script>
<script src="{{asset('js/fileinput/plugins/piexif.min.js')}}"></script>
<script src="{{asset('js/fileinput/plugins/purify.min.js')}}"></script>
<script src="{{asset('js/fileinput/plugins/sortable.min.js')}}"></script>
<script src="{{asset('js/fileinput/themes/fas/theme.js')}}"></script>
<script src="{{asset('js/fileinput/themes/explorer-fas/theme.js')}}"></script>
<script src="{{asset('js/typeahead.js')}}"></script>
<script>
    
    var table;
    let dataSrc = [];
    table = $('#listado_avisos').DataTable({
        processing: true,
        serverSide: true,
        lengthChange: false,
        scrollY: '65vh',
        scrollCollapse: true,
        "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por puesto',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
        },
        ajax: {
            "url":"{{ route('herramientas.avisos.multimedia.inicio') }}",
            "type":"POST",
            "data": {'_token':"{{ csrf_token() }}","implementacion" : $("#implementacion").val()}
        },
        columns: [
            {
                "class":          "details-control",
                "orderable":      false,
                "data":           null,
                "defaultContent": ""
            },
            {data: 'id', name: 'id'},
            {data: 'titulo', name: 'titulo'},
            {data: 'inicio', name: 'inicio'},
            {data: 'fin', name: 'fin'},
            {data: 'estatus', name: 'estatus'},
            {data: 'tipo_aviso', name: 'tipo_aviso'},
            {data: 'acciones', name: 'acciones', orderable: false, searchable: false}
        ],
        order:  [ 1, 'desc' ],
        columnDefs: [
                { className: 'text-center', targets: [0,1,2,3,4,5,6,7] },
        ],
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
    $(function() {
        var detailRows = [];

        $('.seleccionados').on( 'click', 'tr td.details-control', function () {
            var tr = $(this).closest('tr');
            //console.log(tr);
            var row = table.row( tr );
            //console.log(row);
            var idx = $.inArray( tr.attr('id'), detailRows );
            //console.log(idx)
            if ( row.child.isShown() ) {
                tr.removeClass( 'details' );
                row.child.hide();
                // Remove from the 'open' array
                detailRows.splice( idx, 1 );
            }
            else {
                tr.addClass( 'details' );
                row.child( format( row.data() ) ).show();
                // Add to the 'open' array
                if ( idx === -1 ) {
                    detailRows.push( tr.attr('id') );
                }
            }
        } );

        table.on( 'draw', function () {
            $.each( detailRows, function ( i, id ) {
            // alert(i+ " - " + id);
                $('#'+id+' td.details-control').trigger( 'click' );
            } );
        } );

        table.on("click",".eliminar-multimedia",function(){
            var multimedia = $(this).data("multimedia");
            swal({
                title: "",
                text: "¿Esta seguro de eliminar esta multimedia?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    borrarMultimedia(multimedia);
                }
            });
        });

        table.on("click",".borrar",function(){
            var aviso = $(this).data("aviso");
            swal({
                title: "",
                text: "¿Está seguro de eliminar este aviso?, el contenido ya no podrá recuperarse?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    borrarAviso(aviso);
                }
            });
           
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({
            alwaysShowClose: true
        });
        });
        
        $("#spinner").addClass("ocultar");
    });

    function format (d) {
        var mensaje = d.multimedia;
        return '<div class="row justify-content-center"><div class="row mb-8">'+mensaje+'</div></div>';
    }

    function borrarMultimedia(id) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'idMultimedia' : id,
            '_token': CSRF_TOKEN
        }

        var url = "{{route('herramientas.avisos.multimedia.multimedia.borrar')}}";
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: "JSON",
            success: function (response) {
                if(response.ok == 1) {
                    $("#multi" + id).slideUp("slow",function(){
                        $(this).remove();
                    });
               
                    swal("", `${response.msg}`, "success");
                } else {
                
                    swal("", response.msg, "error");
                }
            }
        });
    }

    function borrarAviso(id){
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'idAviso' : id,
            '_token': CSRF_TOKEN
        }

        var url = "{{route('herramientas.avisos.multimedia.multimedia.borrar.aviso')}}";
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    table.ajax.reload();
                    swal("", `${response.msg}`, "success");
                } else {
                    swal("", response.msg, "error");
                }
            }
        });
    }

</script>
