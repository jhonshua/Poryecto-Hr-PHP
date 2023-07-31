@extends('layouts.principal')
@section('tituloPagina', "Credenciales")
@section('content')

<h1>Crear Credenciales</h1>

<div class="row my-3 d-flex justify-content-start">
    <a href="" id="crear-credenciales" class="btn btn-warning text-white"  data-toggle="tooltip" data-placement="top" title="Crear Credenciales"><i class="fa fa-download"></i>  Crear Credenciales</a>    
</div>


<div class="row">

    <div class="col-4 form-group">
        <label for="">Empresas</label>
        <select name="empresas" class="form-control" id="empresas" style="width: 100%"></select>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="article border text-center">
            <div class="mb-3 d-flex justify-content-between">
                <h3 class="m-0">Empleados</h3>
                <div class="col-3">
                    <input class="form-control" type="text" placeholder="Buscar" id="busquedaEmpleados">
                </div>
            </div>
            <table class="table w-100" id="table-empleados">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Fotografía</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Acciones</th>
                </tr>
                </thead>
            </table>
        </div>


    </div>
</div>

@endsection

@push('scripts')

<script>
var empresas = @json($empresas);
$('#empresas').select2({
    searchInputPlaceholder: 'Buscar',
    placeholder: 'Seleccione',
    data: $.map(empresas, function(item) {
        return {
            id: item.id,
            text: item.razon_social
        }
    }),

});
var src = 'https://hrsystem.com.mx/storage/repositorio/';
/*No borrar comentario(ver nota en el controlador GeneradorCredenciales)*/
/*var src = '{{ asset('storage/repositorio/') }}';*/

$('#empresas').on("select2:select", function(e) {
    document.getElementById('crear-credenciales').href='{{ route('sistema.credencial.createCredenciales') }}/' + e.params.data.id;

    const table_empleados = $('#table-empleados').DataTable({
    lengthChange: false,
    destroy: true,
    ajax: `{{route('sistema.credencial.empleados')}}/`+e.params.data.id,
    language: {
        url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
    },
    fixedHeader: true,
    orderCellsTop: true,
    pageLength: 20,
    columns: [
        {data: 'numero_empleado'},
        {
            render: function(data, type, row, meta){
                let empresa_id = document.getElementById('empresas').value;
                return `<img width="94" src="${(row.file_fotografia) ? src + '/' + empresa_id + '/' + row.id + '/' + row.file_fotografia : '/img/avatar.png' }" class="rounded-circle">`;
            }
        },
        {
            data: 'nombre',
            render: function (data, type, row, meta) {
                let html=`${row.nombre} ${row.amaterno} ${row.apaterno}`;
                return html;
            }
        },
        {
            data: 'departamento.nombre'
        },
        {
            data: 'puesto.puesto'
        },{
            data: 'acciones',
            render: function (data, type, row, meta) {
                let html='';
                const downloadCredencial=`{{ route('sistema.credencial.descargarCredencial') }}/${e.params.data.id}/${row.id}`;
                html=`<a href="${downloadCredencial}" class="btn btn-warning text-white data-toggle="tooltip" data-placement="top"
                    title="Descargar Credencial""><i class="fa fa-id-card"></i></a>`;
                return html;
            }
        },
       
    ],
    order: [
        [0, 'desc']
    ],
    });

    $('#busquedaEmpleados').on( 'keyup', function () {table_empleados.search( this.value ).draw();});
});

</script>

@endpush