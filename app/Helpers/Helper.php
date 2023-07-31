<?php

use Illuminate\Http\Request;
use App\Models\Encargado;
use Illuminate\Support\Facades\Mail;
use App\Mail\enviarMail;
//funcion para validar metodo POST
    function validarMetodoPost(Request $request,$ruta)
    {
        if ($request->isMethod('get')){

            return redirect()->route($ruta);
        }
    }

    function mailNotificarEncargado($implementacion,$cuerpo,$titulo = 'Notificación Norma 035')
    {

        try {
            elegirBase();
            $correos = array();
            // dd(Encargado::where('idperiodo_implementacion', $implementacion)->select('correo')->get()->toArray());
            $encargados = Encargado::where('idperiodo_implementacion', $implementacion)->select('correo')->get()->toArray();

            foreach($encargados as $encargado) $correos[] = $encargado['correo'];

            $correos[] = 'desarrollo2@singh.com.mx';
            $correos[] = 'desarrollo@singh.com.mx';
            $correos[] = 'desarollo5@singh.com.mx';
            //$correos[] = 'gte.desarrollo@singh.com.mx';
            foreach($correos as $correo )Mail::to($correo)->later(now()->addSeconds(5), new enviarMail($titulo,$cuerpo,$btnUrl="",$btnTxt=""));
        }catch (\Exception $e){

        }

    }
 
    function basico($numero) {

        $valor = array ('uno','dos','tres','cuatro','cinco','seis','siete','ocho',
        'nueve','diez', 'once','doce','trece','catorce','quince','dieciséis','diecisiete',
        'dieciocho','diecinueve','veinte','veintiuno','veintidos','veintitres','veinticuatro','veinticinco',
        'veintiséis','veintisiete','veintiocho','veintinueve');
        return $valor[$numero - 1];
    }
    
    function decenas($n) {

        $decenas = array (30=>'treinta',40=>'cuarenta',50=>'cincuenta',60=>'sesenta',
        70=>'setenta',80=>'ochenta',90=>'noventa');
        if( $n <= 29) return basico($n);
        $x = $n % 10;
        if ( $x == 0 ) {
            return $decenas[$n];
        } else return $decenas[$n - $x].' y '. basico($x);
    }
        
    function centenas($n) {

        $cientos = array (100 =>'cien',200 =>'doscientos',300=>'trecientos',
        400=>'cuatrocientos', 500=>'quinientos',600=>'seiscientos',
        700=>'setecientos',800=>'ochocientos', 900 =>'novecientos');
        if( $n >= 100) {
        if ( $n % 100 == 0 ) {
        return $cientos[$n];
        } else {
        $u = (int) substr($n,0,1);
        $d = (int) substr($n,1,2);
        return (($u == 1)?'ciento':$cientos[$u*100]).' '.decenas($d);
        }
        } else return decenas($n);
    }
    
    function miles($n){

        if($n > 999) {
            if( $n == 1000) {return 'mil';}
            else {
                $l = strlen($n);
                $c = (int)substr($n,0,$l-3);
                $x = (int)substr($n,-3);
                if($c == 1) {$cadena = 'mil '.centenas($x);}
                else if($x != 0) {$cadena = centenas($c).' mil '.centenas($x);}
                else $cadena = centenas($c). ' mil';
                return $cadena;
            }
        } else return centenas($n);
    }
    
    function millones($n){

        if($n == 1000000) {return 'un millón';}
        else {
            $l = strlen($n);
            $c = (int)substr($n,0,$l-6);
            $x = (int)substr($n,-6);
            if($c == 1) {
                $cadena = ' millón ';
            } else {
                $cadena = ' millones ';
            }
            return miles($c).$cadena.(($x > 0)?miles($x):'');
        }
    }
    function convertir_letra($n){

        switch (true) {
            case ( $n >= 1 && $n <= 29) : return basico($n); break;
            case ( $n >= 30 && $n < 100) : return decenas($n); break;
            case ( $n >= 100 && $n < 1000) : return centenas($n); break;
            case ($n >= 1000 && $n <= 999999): return miles($n); break;
            case ($n >= 1000000): return millones($n);
        }
    }

    function generarCodigo($long){

        $characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $password = '';
        for ($i = 0; $i < $long; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }
        return $password;
    }


    /**
     * Validates a given latitude $lat
     *
     * @param float|int|string $lat Latitude
     * @return bool `true` if $lat is valid, `false` if not
     */
    function validateLatitude($lat) {
        return preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $lat);
    }
  
    /**
     * Validates a given longitude $long
     *
     * @param float|int|string $long Longitude
     * @return bool `true` if $long is valid, `false` if not
     */
    function validateLongitude($long) {
        return preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/', $long);
    }
    
    /**
     * Validates a given coordinate
     *
     * @param float|int|string $lat Latitude
     * @param float|int|string $long Longitude
     * @return bool `true` if the coordinate is valid, `false` if not
     */
    function isValidCoords($cords) {
        return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $cords);
    }
  
?>