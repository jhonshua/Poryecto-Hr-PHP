@extends('layouts.principal')
@section('tituloPagina', "Control de pólizas")

@section('content')

<div class="row" id="crudPeriodos">
    <div class="col-lg-12 col-md-12">
        <div class="table-responsive">
        <span data-toggle="tooltip" data-html="true" data-placement="right" title="Nueva póliza">
            <a href="#" class="font-weight-bold revisar btn btn-warning mb-3" data-toggle="modal" data-target="#modalExportar">
                <i class="fa fa-plus"></i> Nueva póliza
            </a>
        </span>
        
                <table class="table table-striped periodos">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Periodo</th>
                            <th>Fecha inicial</th>
                            <th>Fecha final</th>
                            <th>Fecha pago</th>
                            <th>Ejericio</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in periodos">
                            <td>@{{ d.id }}</td>
                            <td>@{{ d.nombre_periodo | mayuscula }}</td>
                            <td>@{{ d.fecha_inicial_periodo }}</td>
                            <td>@{{ d.fecha_final_periodo }}</td>
                            <td>@{{ d.fecha_pago }}</td>
                            <td>@{{ d.ejercicio }}</td>
                            <td>
                                <span class="badge badge-success"  v-if="d.activo == 1">Periodo en proceso de cálculo</span>
                                <span class="badge badge-warning" v-else-if="d.activo == 0 || d.activo == 3">Período sin calcular</span>
                                <span class="badge badge-secondary" v-else-if="d.activo == 2">Período cerrado</span>
                                <span class="badge badge-black" v-else>Desconocido</span>
                            </td>
                            <td>  
                                 <div id="botonera" v-if="d.activo == 2">
                                    {{-- <button class="btn btn-danger btn-sm cancelar" alt="Borrar póliza" title="Borrar póliza"  v-on:click.prevent="deleteBio(d)"><i class="fas fa-trash"></i></button>
                                    <button class="btn btn-warning btn-sm vern" alt="Ver Poliza" title="Ver póliza" v-on:click="verBio(d)"><i class="fas fa-eye"></i></button> --}}
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" :id="'dropdownMenuButton'+d.id" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          Opciones
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                          <a class="dropdown-item" href="calculaNomina2.php?periodo=0&&x=0" >Imprimir nómina</a>
                                          <a class="dropdown-item" href="#" v-on:click.prevent="verPoliza(d.id)">Póliza</a>
                                          <a class="dropdown-item" href="comprimir.php?id=0" target="_BLANK" >DESCARGAR CFDI'S</a>
                                          <a class="dropdown-item" href="comprimirPDF.php?id=0" target="_BLANK" >DESCARGAR CFDI'S PDF</a>
                                          <a class="dropdown-item" href="#" v-on:click.prevent="exportar(d.id)" >Exportar Póliza</a>
                                          <a class="dropdown-item" href="#" v-on:click.prevent="facturar(d.id)">Facturar período</a>
                                        </div>
                                      </div>
                                </div>    
                            </td>
                        </tr>                        
                    </tbody>
                </table>
                    <ul class="pagination">
                        <li v-if="pagination.current_page > 1" class="page-item">
                            <a href="#" @click.prevent="changePage(pagination.current_page - 1)" class="page-link">
                                <span>Atras</span>
                            </a>
                        </li>
                        <li v-for="page in pagesNumber" v-bind:class="[page == isActived ? 'active' : '']" class="page-item">
                            <a href="#" @click.prevent="changePage(page)" class="page-link">
                            @{{ page }}
                            </a>
                        </li>
                        <li v-if="pagination.current_page < pagination.last_page" class="page-item">
                            <a href="#" @click.prevent="changePage(pagination.current_page + 1)" class="page-link">
                                <span>Siguiente</span>
                            </a>
                        </li>
                    </ul>
                </nav>
        </div>
    </div>
    @include('contabilidad.polizas.exportar_modal')
<!--    <div id="spinner" class="spinner ocultar overlay"></div>-->
    {{-- @include('biometricos.ver') --}}
    {{-- @include('biometricos.agregar') --}}
</div>
@endsection

@push('scripts')
<script>
    new Vue({
    el: '#crudPeriodos',
    created: function() {
        $('#spinner').removeClass('ocultar'); 
        this.getPeriodos();
    },
    data: {
        periodos: [],
        id_poliza:null,
        newPeriodo : { 'numero_periodo': 'nombre_periodo' ,'fecha_inicial_periodo': '', 'fecha_final_periodo': '', 'fecha_pago':'', 'ejercicio': '','mes':'', 'bimestre': '','dias_periodo': '','especial': '', 'ok' : false },
        fillPeriodo : { 'id': '', 'numero_periodo': 'nombre_periodo' ,'fecha_inicial_periodo': '', 'fecha_final_periodo': '', 'fecha_pago':'', 'ejercicio': '','mes':'', 'bimestre': '','dias_periodo': '','especial': '', 'ok' : false },
        errors: [],
        pagination: {
            'total': 0,
            'current_page': 0,
            'per_page': 0,
            'last_page': 0,
            'from': 0,
            'to': 0,
        },
    },
    filters: {
        mayuscula: function (value) {
            if (!value) return ''
            value = value.toString();
            return value.toUpperCase(); 
        }
    },
    computed:{       
        isActived: function() {
            return this.pagination.current_page;
        },
        pagesNumber: function() {
            var offset = 2;
            if (!this.pagination.to) {
                return [];
            }

            var from = this.pagination.current_page - offset;
            if (from < 1) {
                from = 1;
            }

            var to = from + (offset * 2);

            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }

            return pagesArray;
        },

    },
    methods: {
        getPeriodos: function(page) {

            var url = '{{route('poliza.paginacion')}}/?page='+page;
            $('#spinner').removeClass('ocultar'); 
            axios.get(url).then(response => {
                console.log(response.data);
                this.periodos = response.data.periodos.data;
                this.pagination = response.data.pagination
                $('#spinner').addClass('ocultar'); 
            });
        },
        
        borrar(){
            this.newPeriodo = { 'numero_periodo': 'nombre_periodo' ,'fecha_inicial_periodo': '', 'fecha_final_periodo': '', 'fecha_pago':'', 'ejercicio': '','mes':'', 'bimestre': '','dias_periodo': '','especial': '', 'ok' : false };
            this.fillPeriodo = { 'id': '', 'numero_periodo': 'nombre_periodo' ,'fecha_inicial_periodo': '', 'fecha_final_periodo': '', 'fecha_pago':'', 'ejercicio': '','mes':'', 'bimestre': '','dias_periodo': '','especial': '', 'ok' : false };
            this.errors = [];
        },
        
        changePage: function(page) {
            this.pagination.current_page = page;
            this.getPeriodos(page);
        },
        verPoliza:function(id){
            var url ="{{ route('poliza.index')}}/"+ id;
            window.open(url);

        },
        exportar:function(p){
            this.id_poliza = p;
            $('#modalExportar').modal('show');
        },
        facturar:function(id){
            var url ="{{ route('factura.periodo')}}/"+ id;
            window.location.assign(url);
        }
    }
});
</script>
@endpush