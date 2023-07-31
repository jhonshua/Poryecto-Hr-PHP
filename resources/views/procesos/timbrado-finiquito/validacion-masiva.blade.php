<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
$empleados_ok = array();
@endphp

<div class="container">  
    <div class="col-12 text-center">
        <form method="GET" action="{{route('timbrar.finiquito.inicio')}}">
              @csrf
              <input type="hidden" name="ejercicio" value="{{$anio_ejercicio}}">
              @include('includes.header-alt', ['title'=>'Timbrado de finiquito',
                'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png'])
          </form>       
    </div> 

    <div class="article border"> 

        <div class="row mt-3 mb-3">
            <div class=" col-12 text-center">
                <h5>Empleados</h5>
                @if ($errores['empleados'] == 1)
                    <div class="alert alert-danger" role="alert">
                        <span>Tienes <strong>{{ $errores['empleados'] }}</strong> error</span>
                    </div>
                @elseif($errores['empleados'] > 1)
                    <div class="alert alert-danger" role="alert">
                        <span>Tienes <strong>{{ $errores['empleados'] }}</strong> errores</span>
                    </div>
                @endif
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Validación</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empleados as $e)
                            @php
                                if( ($e['estatus_timbre']) ==0 ){
                                    if(!in_array($e['id'], $empleados_error)){
                                        $empleados_ok[] = [
                                            'id_empleado' => $e['id'], 
                                            'id_rutina' => $e['id_rutina'],
                                            ];
                                    }
                                }                        
                            @endphp                   
                            
                        <tr>
                            <td scope="row">{{ $e['id'] }}</td>
                            <td>{{ $e['nombre'] }} </td>
                            <td>
                                <ul> 
                                    @if($e['errores']['rfc'])
                                        <li class="text-danger">RFC Incorrecto</li> 
                                    @else
                                        <li class="text-success">RFC Correcto</li> 
                                    @endif
                                    @if($e['errores']['nss'])
                                        <li class="text-danger">NSS Incorrecto</li> 
                                    @else
                                        <li class="text-success">NSS Correcto</li> 
                                    @endif
                                    @if($e['errores']['curp'])
                                        <li class="text-danger">CURP Incorrecto</li> 
                                    @else
                                        <li class="text-success">CURP Correcto</li> 
                                    @endif
                                    @if($e['errores']['registro_patronal'])
                                        <li class="text-danger">Registro Patronal Incorrecto</li> 
                                    @else
                                        <li class="text-success">Registro Patronal Correcto</li> 
                                    @endif                            
                                </ul> 
                            </td>
                            <td>                        
                            <div id="sp{{ $e['id'] }}">
                                @if(!($e['estatus_timbre']) ==0 )
                                    <p class="text-success">Ya se realizó un timbrado</p>
                                @endif

                            </div> 
                            </td>
                        </tr>    
                        @endforeach                
                    </tbody>
                </table>
            </div>  

            <div id="accordion" class="card col-12 justify-content-center">
                <button class="btn btn-link collapsed font-weight-bold" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Conceptos
                </button>
                @if ($errores['conceptos'] == 1)
                    <div class="alert alert-danger text-center" role="alert">
                        <span>Tienes <strong>{{ $errores['conceptos'] }}</strong> error</span>
                    </div>
                @elseif($errores['conceptos'] > 1)
                    <div class="alert alert-danger text-center" role="alert">
                        <span>Tienes <strong>{{ $errores['conceptos'] }}</strong> errores</span>
                    </div>
                @endif
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                    <div class="col-12 justify-content-center">                
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Codigo SAT</th>
                                    <th>Validación</th>                        
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($conceptos as $c)
                                <tr>
                                    <td scope="row">{{ $c['id'] }}</td>
                                    <td>{{ $c['nombre'] }} </td>
                                    <td>{{ $c['codigo_sat'] }} </td>
                                    <td>
                                        <ul> 
                                            @if($c['errores']['sat'])
                                                <li class="text-danger">SAT Incorrecto</li> 
                                            @else
                                                <li class="text-success">SAT Correcto</li> 
                                            @endif
                                        </ul> 
                                    </td>
                                    
                                </tr>    
                                @endforeach                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>    
                    
        </div>        

    </div>
    <div class="row d-flex justify-content-center pt-3">
        <div id="proceso_timbrado">
            @if ($errores['empleados'] >= 0 && $errores['conceptos'] == 0 && count($empleados_ok) > 0) 
                <button class="button-style mb-3 mr-1" v-on:click="timbrar()">Timbrar</button>
            @endif            
        </div>
    </div>
</div>
@include('includes.footer')

<script>
    let sp ='<div class="inner" style="width: 38px; height: 38px;"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:#fff;display:block;" width="38px" height="38px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><defs><clipPath id="ldio-xnklxnav7ao-cp"><rect x="0" y="0" width="100" height="50"><animate attributeName="y" repeatCount="indefinite" dur="2.2222222222222223s" calcMode="spline" values="0;50;0;0;0" keyTimes="0;0.4;0.5;0.9;1" keySplines="0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7"></animate><animate attributeName="height" repeatCount="indefinite" dur="2.2222222222222223s" calcMode="spline" values="50;0;0;50;50" keyTimes="0;0.4;0.5;0.9;1" keySplines="0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7"></animate></rect><rect x="0" y="50" width="100" height="50"><animate attributeName="y" repeatCount="indefinite" dur="2.2222222222222223s" calcMode="spline" values="100;50;50;50;50" keyTimes="0;0.4;0.5;0.9;1" keySplines="0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7"></animate><animate attributeName="height" repeatCount="indefinite" dur="2.2222222222222223s" calcMode="spline" values="0;50;50;0;0" keyTimes="0;0.4;0.5;0.9;1" keySplines="0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7;0.3 0 1 0.7"></animate></rect></clipPath></defs><g transform="translate(50 50)"><g transform="scale(0.9)"><g transform="translate(-50 -50)"><g><animateTransform attributeName="transform" type="rotate" dur="2.2222222222222223s" repeatCount="indefinite" values="0 50 50;0 50 50;180 50 50;180 50 50;360 50 50" keyTimes="0;0.4;0.5;0.9;1"></animateTransform><path clip-path="url(#ldio-xnklxnav7ao-cp)" fill="#e9bc00" d="M54.864 50L54.864 50c0-1.291 0.689-2.412 1.671-2.729c9.624-3.107 17.154-12.911 19.347-25.296 c0.681-3.844-1.698-7.475-4.791-7.475H28.908c-3.093 0-5.472 3.631-4.791 7.475c2.194 12.385 9.723 22.189 19.347 25.296 c0.982 0.317 1.671 1.438 1.671 2.729v0c0 1.291-0.689 2.412-1.671 2.729C33.84 55.836 26.311 65.64 24.117 78.025 c-0.681 3.844 1.698 7.475 4.791 7.475h42.184c3.093 0 5.472-3.631 4.791-7.475C73.689 65.64 66.16 55.836 56.536 52.729 C55.553 52.412 54.864 51.291 54.864 50z"></path><path fill="#000000" d="M81 81.5h-2.724l0.091-0.578c0.178-1.122 0.17-2.243-0.022-3.333C76.013 64.42 68.103 54.033 57.703 50.483l-0.339-0.116 v-0.715l0.339-0.135c10.399-3.552 18.31-13.938 20.642-27.107c0.192-1.089 0.2-2.211 0.022-3.333L78.276 18.5H81 c2.481 0 4.5-2.019 4.5-4.5S83.481 9.5 81 9.5H19c-2.481 0-4.5 2.019-4.5 4.5s2.019 4.5 4.5 4.5h2.724l-0.092 0.578 c-0.178 1.122-0.17 2.243 0.023 3.333c2.333 13.168 10.242 23.555 20.642 27.107l0.338 0.116v0.715l-0.338 0.135 c-10.4 3.551-18.31 13.938-20.642 27.106c-0.193 1.09-0.201 2.211-0.023 3.333l0.092 0.578H19c-2.481 0-4.5 2.019-4.5 4.5 s2.019 4.5 4.5 4.5h62c2.481 0 4.5-2.019 4.5-4.5S83.481 81.5 81 81.5z M73.14 81.191L73.012 81.5H26.988l-0.128-0.309 c-0.244-0.588-0.491-1.538-0.28-2.729c2.014-11.375 8.944-20.542 17.654-23.354c2.035-0.658 3.402-2.711 3.402-5.108 c0-2.398-1.368-4.451-3.403-5.108c-8.71-2.812-15.639-11.979-17.653-23.353c-0.211-1.191 0.036-2.143 0.281-2.731l0.128-0.308 h46.024l0.128 0.308c0.244 0.589 0.492 1.541 0.281 2.731c-2.015 11.375-8.944 20.541-17.654 23.353 c-2.035 0.658-3.402 2.71-3.402 5.108c0 2.397 1.368 4.45 3.403 5.108c8.71 2.812 15.64 11.979 17.653 23.354 C73.632 79.651 73.384 80.604 73.14 81.191z"></path></g></g></g></g></svg></div>';
    let badgeOk ='<span class="badge badge-success">Timbrado</span>';
    let badgeError ='<span class="badge badge-danger">Error</span>';
    let urlBase ="{{ url('/procesos') }}";
    let errors = 0;
    let soap_mensaje = (id,codigo_error,msj_error,xml) => {
        return `
        <div class="col-sm-12">
            <div class="card border-danger mb-3">
                <div class="card-header" style="background-color:#fbba00;color: #fff;"><h5>${codigo_error} </h5></div>
                <div class="card-body text-danger">
                    <p class="card-text">${msj_error}</p>
                    <div id="acc1ordion">
                        <div class="card">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapse_${id}" aria-expanded="false" aria-controls="collapse_${id}">
                                XML Enviado
                            </button>
                            <div id="collapse_${id}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                                <div class="card-body">
                                    <textarea class="form-control" rows="6" cols="100" readonly="yes">
                                    ${xml}
                                    </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `
    };

    new Vue({
    el: '#proceso_timbrado',
    data: {
            empleados: @json($empleados_ok),
            anio : '{{$anio_ejercicio}}',
            errors: 0,
            ok: false
        },
        methods: {      
                async timbrar() {
                const promises = this.empleados.map(async (emp, idx) => {
                        let url = urlBase + '/timbrar-finiquito-empleado/'+btoa(emp.id_empleado)+'/'+this.anio+'/'+emp.id_rutina+'/2';                  
                        $("#sp"+emp.id_empleado).html(sp);
                        await axios.get(url).then(response => {
                            if(response.data.exito ){
                                $("#sp"+emp.id_empleado).html(badgeOk);
                            }else{
                                $("#sp"+emp.id_empleado).html(soap_mensaje(emp.id_empleado,response.data.data.codigo_error,response.data.data.MENSAJE_error,response.data.data.archivo_xml));
                                errors++;
                            }                
                        }).catch(error => {
                        $("#sp"+emp.id_empleado).html(badgeError);
                        errors++;
                        });
                });            

                await Promise.all(promises);
                if(this.errors == 0){
                    this.ok = true;
                }             
                swal("El proceso de timbrado finalizó.", {
                            icon: "success",
                        });
            }    
        }
    });
</script>