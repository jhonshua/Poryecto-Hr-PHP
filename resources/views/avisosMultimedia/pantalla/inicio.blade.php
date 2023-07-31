@extends('layouts.principal_lista_avisos')
@section('tituloPagina', "Seleccionar empresa")
@section('content')
<div class="row">
	<div class="col-md-6 offset-3 text-center  contenido"  >
        <img src="{{asset('public/img/hr_logo.png')}}" alt="" class="img-thumbnail" width="70%">
        <form class="card mt-5 text-center" method="POST" action="{{route('pantalla.ver')}}" id="inicio_form">
            @csrf
            <div class="card-body text-center">
                <p>En este apartado podrás elegir la empresa en la que deseas proyectar su contenido para gestionar su información. </p>
                <h5 class="card-title ">Selecciona la empresa que quieres utilizar:</h5>
                <select name="empresa" id="empresa" class="form-control mt-4">
                    <option value="">Selecciona...</option>
                    @if(isset($empresas))
                        @foreach($empresas as $empresa)
                            <option value="{{encrypt($empresa->id)}}">{{$empresa->razon_social}}</option>
                        @endforeach
                    @else
                        @foreach ($usuario->empresas as $empresa)
                            @if ($empresa->estatus == 1)
                                <option value="{{encrypt($empresa->id)}}">{{$empresa->razon_social}}</option>
                            @endif
                        @endforeach

                    @endif
                </select>
                <input type="hidden" name="id" id="id">
          
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
    <style>
        #panelInicialHeader { display: none; }
    </style>
@endpush

@push('scripts')
<script>
$(function(){
    
    $(window).resize(function() {
        (window.innerWidth <= 768) ? $(".contenido").removeClass('offset-3') :  $(".contenido").addClass('offset-3');
    });

    $( "#empresa" ).change(function() {

        $("#id").val($(this).val())
        $("#inicio_form" ).submit();
    
    });
   
});
</script>
@endpush