<div class="footer">
    <div class="row">
        <div class="col-md-6 p-1">
        	@if(Auth::user()->base_autofacturador==6)
            	<img id="logo_footer" src="/img/elqueretano-foot.png" class="w-px-90">
            @else
            	<img id="logo_footer" src="/img/logo-inverso.png" class="w-px-90">
            @endif
        </div>
        <div class="col-md-6 p-1">
        	@if(Auth::user()->base_autofacturador==6)
            	<p class="text-right font-size-0-8em"><label class="under-line">Co</label>pyright El Queretano 2021</p>
            @else
            	<p class="text-right font-size-0-8em"><label class="under-line">Co</label>pyright HR-System 2021</p>
            @endif
            
        </div>
    </div>
</div>
