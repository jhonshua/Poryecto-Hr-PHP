<div class="row d-flex justify-content-between mb-4 text-center">
    <div class="text-center col-xs-12 mb-2">
        <div class="text-center">
            <button type="submit" title="Regresar" class="border-0 bg-transparent">@include('includes.back')</button>
            <label class="font-size-1em mb-3 under-line font-weight">Regresar</label>
        </div>
    </div>
    <div class="text-center col-xs-12 col-lg-4 mb-2">
        <div class="text-center">
            <div>
                <img src="{{asset($img)}}" alt="Periodo de implementación" class="w-px-35 text-center">
            </div>
            <div class="text-center">         
                <span class="custom-title text-center">{{$title}}</span>
            </div>
            <div class="text-center">            
                <span class="top-line-black">{{$subtitle}}</span>
            </div>
        </div>
    </div>
    <div></div>
</div>