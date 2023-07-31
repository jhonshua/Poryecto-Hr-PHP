@extends('layouts.pantalla_sindicato')

@section('content')
    <section id="principal">
        <main class="mx-lg-8" id="appPantalla">
            <div class="container-fluid mt-2">
                <div class="card mb-2 fadeIn" id="barra-top">
                    <div class="card-body d-sm-flex justify-content-between">
                        <img src="{{asset('/img/logo-navbar.png')}}" id="logo">
                        <span class="mb-2 mb-sm-0" id="fecha">
                        <span id="reloj"></span>
                        <span id="fecha-texto"> {{date("d/m/Y")}}</span>
                       <a href="{{route('ajax.listadoInicio')}}"><div class="btn btn-dark btn-sm " data-toggle="toltip" title="Seleccionar empresa" ><i class="fas fa-sign-out-alt"></i></div></a>
                    </span>
                    </div>
                </div>

                <div class="row fadeIn">
                    <div class="col-12 px-3" id="panel-avisos">
                        <div class="flip" id="panel1">
                            <div class="card">
                                <div class="face front">
                                    <div class="card-header header-uno">
                                        <div class="titulo">VIDEOS IMPORTANTES</div>
                                        <hr class="hr-bottom">
                                    </div>
                                    <div class="card-body" id="avisos0">
                                        <div class="row mt-1">
                                            <div class="col-12 text-center" id="addvideo">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="face back">
                                    <div class="card-header header-uno">
                                        <div class="titulo">AVISOS IMPORTANTES</div>
                                        <hr class="hr-bottom">
                                    </div>
                                    <div class="card-body d-flex w-100" id="avisosC">
                                        <div id="slider-avisos" class="carousel slide z-depth-1-half m-auto" data-ride="carousel">
                                            <div class="carousel-inner text-center " id="avisos-carrousel">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>
@endsection

@push('css')
    <style>
        .imgnoticias{
            width: 100%;
        }
        .tituloqsmta{
            font-size: 16px;
        }
        .img-aviso{
            width: auto;
        }
        #panel1 {
            height: 90vh;
        }
        .card-body, .back{
            height: inherit;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function(){
            obiteneSalidas();
            obtieneVideo();
            $("#spinner").addClass("ocultar");
        });
        const obtieneVideo = async () =>{
            $("#addvideo").empty();
            let arr = [];
            let url ='{{route("api.video",$empresa->id)}}';
            const res= await fetch(url);
            const data= await res.json();
            let videos_arr=data.video;

            if(videos_arr.length > 0){

                videos_arr.map((value, key)=>{

                    const {inicio,fin }=value; // fechas de inicio y finalizacion para las imagenes
                    value.multimedia.map((value2, key2)=>{
                        let valores= `${value2.nombre}-#-${value.inicio}-#-${value.fin}-#-${value2.tiempo}`;
                        arr.push(valores);
                    });
                });
                for(let array_datos of arr) {

                    let fecha_actual=moment().format('YYYY-MM-DD');
                    let valores = array_datos.split('-#-');
                    let fecha_inicio= valores[1];
                    let fecha_fin=valores[2];
                    let tiempo = valores[3] *1000 ;

                    if(fecha_inicio >= fecha_actual  ||  fecha_fin >= fecha_actual  ){

                        let vid=`<iframe title="vimeo-player" src=${valores[0]}?autoplay=1&loop=1&muted=1 width="480" height="320" frameborder="0"  allowfullscreen></iframe>`;
                        $("#addvideo").append(vid);

                        await esperaTiempo(valores[0],tiempo).then((video) => {
                            $("#addvideo").empty('');
                            let vid=`<iframe title="vimeo-player" src=${video} width="480" height="320" frameborder="0" allowfullscreen></iframe>`;
                            $("#avisos-carrousel").append(vid);
                        });
                    }
                }
                $('#panel1,#avisosC').find('.card').toggleClass('flipped').offset();
                await obtiene_avisos();
            }else{

                $('#panel1,#avisosC').find('.card').toggleClass('flipped').offset();
                await obtiene_avisos();
            }
        }
        const obtiene_avisos= async()=>{

            $("#avisos-carrousel").empty('');

            let url = '{{route("api.avisos",$empresa->id)}}';
            const res =  await fetch(url);
            const data= await res.json();
            let avisos_arr=data.avisos;

            if(avisos_arr.length > 0){
                let arr = [];
                avisos_arr.map((value, key)=>{

                    const {inicio,fin }=value; // fechas de inicio y finalizacion para las imagenes

                    value.multimedia.map((value2, key2)=>{
                        let valores= `${value2.nombre}-#-${value.inicio}-#-${value.fin}-#-${value2.tiempo}`;
                        arr.push(valores);
                    });
                });

                for(let array_datos of arr) {

                    let fecha_actual=moment().format('YYYY-MM-DD');
                    let valores = array_datos.split('-#-');
                    let fecha_inicio= valores[1];
                    let fecha_fin=valores[2];
                    let tiempo = valores[3] *1000 ;

                    if(fecha_inicio >= fecha_actual  ||  fecha_fin >= fecha_actual  ){

                        let imagen=`<div class="active carousel-item"><img class="img-aviso" src="{{ '/storage/repositorio/'.$empresa->id }}/avisos/${valores[0]}"></div>`;
                        $("#avisos-carrousel").append(imagen);

                        await esperaTiempo(valores[0],tiempo).then((img) => {
                            $("#avisos-carrousel").empty('');
                            imagen=`<div class="active carousel-item"><img class="img-aviso" src="{{ '/storage/repositorio/'.$empresa->id }}/avisos/${img}"></div>`;
                            $("#avisos-carrousel").append(imagen);
                        });
                    }
                }

                $('#panel1,#avisos0').find('.card').toggleClass('flipped').offset();
                await obtieneVideo();

            }else{
                await actualizaPantalla();
            }
        };
        const  actualizaPantalla = async()=>{
            setInterval(()=> location.reload() , 60000);
        }
        const esperaTiempo = (valores,tiempo) =>{

            let promise = new Promise((resolve, reject) => {
                setTimeout(() => {
                    resolve(valores);
                }, tiempo);
            });
            return promise;
        }
        const obiteneSalidas=async()=>{
            $("#tiempoSalida").empty();
            let url ='{{route("ajax.obteneSalidas",$empresa->id)}}';
            const res= await fetch(url);
            const data= await res.json();
            data.map((resultados,i)=>{

                if(i < 4){

                    const {salida,salida_horario,nombre,apaterno,amaterno}=resultados;
                    let salidav = (Date.parse(salida))/100;
                    let salida_horariov = (Date.parse(salida_horario))/100;

                    if(salidav > salida_horariov){

                        let empezo=moment(salida_horario);
                        let salio =moment(salida);
                        let diferencia = moment.duration(salio.diff(empezo));
                        let milisegundos = moment.duration(diferencia);
                        let tiempo_diferencia=Math.floor(milisegundos.asHours()) + moment.utc(milisegundos.asMilliseconds()).format(":mm:ss");
                        let salida_tardada=` <div class="salida d-flex justify-content-between">
                                        <div><strong>${apaterno}</strong>  ${nombre}</div>
                                        <div class="tiempo">${tiempo_diferencia} min</div>
                                    </div>`;

                        $('#tiempoSalida').append(salida_tardada);
                    }
                }
            });
        }
    </script>
@endpush