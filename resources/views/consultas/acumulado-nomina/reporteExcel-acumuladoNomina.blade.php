<table>
    <tr>
        <td>RAZÓN SOCIAL:</td>
        <td>{{Session::get('empresa')['razon_social']}}</td>
    </tr>
    <tr>
        <td>RFC:</td>
        <td><strong>{{Session::get('empresa')['rfc']}}</strong></td>
    </tr>
    <tr>
        <td>Periodo:</td>
        <td><strong>{{$Periodicidad}}</strong></td>
    </tr>
    <tr>
        <td>Fecha del reporte:</td>
        <td>{{ formatoAFecha(date('Y-m-d H:i:s'), true) }}</td>
    </tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
</table>

<table class="" id="empleadostbl" cellspacing="0" style="width:100%;border-collapse:collapse;">
    <tr class="GridViewScrollHeader bg-dark text-white">
        <th style="background:#343a40; color: #ffffff;">TOTAL DE EMPLEADOS</th>
        <th style="background:#343a40; color: #ffffff;" class="text-nowrap">PAGADORA</th>
        
        @if($tipo_nomina != 'solosindical')


            @if (count($columnas1) > 0)
                @foreach ($columnas1 as $col)
                    <th style="background:#343a40; color: #ffffff;" class="text-nowrap" data-idConcepto="{{$col->id}}">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif
           

            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">TOTAL PERCEPCIONES</th>

            @if (count($columnas2) > 0)
                @foreach ($columnas2 as $col)
                    <th style="background:#343a40; color: #ffffff;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">SUBSIDIO</th>
            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">TOTAL DEDUCCIONES</th>
            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">IMPORTE DEPO FISCAL</th>

        @endif

        <th style="background:#343a40; color: #ffffff;" class="text-nowrap"></th>


        @if($tipo_nomina == 'solosindical' || $tipo_nomina == 'sindical')


            @if ($columnasSindical)
                @foreach ($columnasSindical as $col)
                    <th style="background:#343a40; color: #ffffff;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            

            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">BENEFICIO SINDICAL</th>

           

            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">TOTAL PERCEPCIONES</th>

            {{-- Sindical Deducciones --}}
            @if ($columnasDEDUCC)
                @foreach ($columnasDEDUCC as $col)
                    <th style="background:#343a40; color: #ffffff;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">TOTAL DEDUCCIONES</th>
            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">IMPORTE DEPO SINDICAL</th>
        @endif
    </tr>

    @foreach ($emisoras as $emisora)
    <tr>
        <td>{{$emisora->num_empleados}}</td>        
        <td>{{$emisora->razon_social}}</td>
         @if($tipo_nomina != 'solosindical')
                @if ($columnas1)
                    @foreach ($columnas1 as $col)
                        <td>${{ number_format(round($valores[$emisora->id][0]->{'total'.$col->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

        <td>${{$valores[$emisora->id][0]->percep_fiscal}}</td>
                @if ($columnas2)
                    @foreach ($columnas2 as $col2)
                        <td>${{ number_format(round($valores[$emisora->id][0]->{'total'.$col2->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

        <td>${{$valores[$emisora->id][0]->subsidio}}</td>
        <td>${{$valores[$emisora->id][0]->deducion_f}}</td>
        <td>${{$valores[$emisora->id][0]->neto_fiscal}}</td>
        
        @endif
        <td>                     </td>
        @if($tipo_nomina == 'solosindical' || $tipo_nomina == 'sindical')


            @if ($columnasSindical)
                @foreach ($columnasSindical as $col2)
                    <td>${{ number_format(round($valores[$emisora->id][0]->{'total'.$col2->id},2),2,'.',',') }}</td>
                @endforeach
            @endif
        <td>${{$valores[$emisora->id][0]->beneficio_s}}</td>
        <td>${{$valores[$emisora->id][0]->total_p_s}}</td>
        @if ($columnasDEDUCC)
                @foreach ($columnasDEDUCC as $col3)
                    <td>${{ number_format(round($valores[$emisora->id][0]->{'total'.$col3->id},2),2,'.',',') }}</td>                       
                @endforeach
        @endif
        <td>${{$valores[$emisora->id][0]->total_d_s}}</td>
        <td>${{$valores[$emisora->id][0]->neto_s}}</td>
        @endif
    </tr>
    @endforeach
    <tr></tr>
    <tr></tr>
    <tr class="GridViewScrollHeader bg-dark text-white">
        <th style="background:#343a40; color: #ffffff;">TOTAL DE EMPLEADOS</th>
        <th style="background:#343a40; color: #ffffff;" class="text-nowrap">PAGADORA</th>
        
        @if($tipo_nomina != 'solosindical')


            @if (count($columnas1_fini) > 0)
                @foreach ($columnas1_fini as $col)
                    <th style="background:#343a40; color: #ffffff;" class="text-nowrap" data-idConcepto="{{$col->id}}">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif
           

            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">TOTAL PERCEPCIONES</th>

            @if (count($columnas2_fini) > 0)
                @foreach ($columnas2_fini as $col)
                    <th style="background:#343a40; color: #ffffff;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">SUBSIDIO</th>
            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">TOTAL DEDUCCIONES</th>
            <th style="background:#343a40; color: #ffffff;" class="text-nowrap">IMPORTE DEPO FISCAL</th>

        @endif
    </tr>

    @foreach ($emisoras_fini as $emisora_fini)
    <tr>
        <td>{{$valores_fini[$emisora_fini->id][0]->numero_empleados}}</td>        
        <td>{{$emisora_fini->razon_social}}</td>
         @if($tipo_nomina != 'solosindical')
                @if ($columnas1_fini)
                    @foreach ($columnas1_fini as $col)
                        <td>${{ number_format(round($valores_fini[$emisora_fini->id][0]->{'total'.$col->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

        <td>${{$valores_fini[$emisora_fini->id][0]->percep_fiscal}}</td>
                @if ($columnas2_fini)
                    @foreach ($columnas2_fini as $col2)
                        <td>${{ number_format(round($valores_fini[$emisora_fini->id][0]->{'total'.$col2->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

        <td>${{$valores_fini[$emisora_fini->id][0]->subsidio}}</td>
        <td>${{$valores_fini[$emisora_fini->id][0]->deducion_f}}</td>
        <td>${{$valores_fini[$emisora_fini->id][0]->neto_fiscal}}</td>
        
        @endif
    </tr>
    @endforeach
    
</table>
