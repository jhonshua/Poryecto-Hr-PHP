@extends('layouts.principal')
@section('tituloPagina', "Timbrado Asimilados")

@section('content')
<div class="row mt-5">
    <div class=" col-8 text-center">
    @if ($error)
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">No Disponible!</h4>
            <p>¡No hay Períodos de Nómina Disponibles a Calcular!</p>
            <hr>
        </div>
    @else
    <div class="card" >
        <div class="card-body">
            <h5 class="card-title">Departamentos</h5>
            <h6 class="card-subtitle mb-2 text-muted">Selecciona los departamentos que quieres timbrar</h6>
            <form method="POST" action="{{route('contabilidad.timbrar.asimilados')}}">
                @csrf
                <input type="hidden" name="id_periodo" value="{{$id_periodo}}" >
                <div style="text-align: left; width: 50%; margin: auto;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="todos" id="todos">
                        <label class="form-check-label" for="todos">
                            Todos
                        </label>
                    </div>
                    <hr>
                    @foreach ( $departamentos as $d )
                        <div class="form-check">
                            <input class="form-check-input check" type="checkbox" value="{{ $d->depto }}" name="deptos[]" id="{{ 
                            $d->depto}}">
                            <label class="form-check-label" for="{{$d->depto}}">
                                {{$d->nombre}}
                            </label>
                        </div> 
                    @endforeach 
                </div>
                <input type="submit" class="btn btn-dark" value="Generar">
            </form>
        </div>
    </div>
    @endif
    </div>        
</div>
@endsection
@push('scripts')
    <script>
        $("#todos").click(function () {
            $(".check").prop('checked', $(this).prop('checked'));
        });
  </script>
  @endpush

