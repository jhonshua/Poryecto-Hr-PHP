<div style="display: flex; justify-content:center;">
            <img src="{{asset($img)}}" alt="Periodo de implementación" class="w-px-35 text-center" style="margin-left: 5%;">
        </div><div style="float:left;">
      <a href="{{ route($route) }}" data-toggle="tooltip" title="Regresar" ref="Bandeja de notificaciones">
            @include('includes.back')
        </a>
        <label class="font-size-1em mb-3 under-line font-weight">Regresar</label>
        </div>
        <div style="display: flex; justify-content:center;margin-bottom: -8px;">
            <label class="custom-title mr-5 text-center">{{$title}}</label>
        </div>
        <div style="display: flex; justify-content:center; margin-bottom:4%;">
            <label class=" text-center top-line-black mr-5">{{$subtitle}}</label>
        </div>