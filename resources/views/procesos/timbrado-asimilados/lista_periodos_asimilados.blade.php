@extends('layouts.principal')
@section('tituloPagina', "Timbrado Asimilados")

@section('content')
  <div class="card mt-4">
    <div class="card-body">
      @if (count($periodos) > 0)
          <table class="table table-striped avisos">
              <thead class="thead-dark">
                  <tr>
                      <th>#</th>
                      <th>Tipo</th>
                      <th>Periodo</th>
                      <th>Tipo</th>
                      <th></th>
                  </tr>
              </thead>

               @foreach ($periodos as $periodo)
                  <tr id="{{ $periodo->id }}">
                      <td>{{ $periodo->numero_periodo }}</td>
                      <td>{{ $periodo->nombre_periodo }}</td>
                      <td>{{ $periodo->fecha_inicial_periodo }} - {{ $periodo->fecha_final_periodo }}</td>
                      <td>
                          @if($periodo->especial !=0)
                            <span class="badge badge-warning">Especial</span>
                          @else
                            <span class="badge badge-light">Normal</span>
                          @endif
                      </td>
                      <td>                       
                            
                        <a href="{{ route('contabilidad.timbrar.asimilados.periodo',$periodo->id )}}" class="btn btn-success btn-sm aceptar" ><i class="fas fa-check-circle"></i> Ver Timbres</a>
                       
                    </td>
                  </tr>
              @endforeach 

          </table>
          {{ $periodos->links() }}
      @else
          <div class="text-black-50 mt-4 font-weight-bold">NO HAY PERIODOS</div>
      @endif
  </div>
</div>

@endsection