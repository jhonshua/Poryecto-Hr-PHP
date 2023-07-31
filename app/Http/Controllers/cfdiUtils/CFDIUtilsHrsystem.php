<?php

namespace App\Http\Controllers\cfdiUtils;

use App\Http\Controllers\Controller;
use CfdiUtils\OpenSSL\OpenSSL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CFDIUtilsHrsystem extends Controller
{
    //

    public static function convertKeyToPem($id, $password,$password_soap)
    {
        $keyDerFile = storage_path('app/public/trash/' . $id . '.key');
        $keyPemPath = storage_path('app/public/timbrado/archs_pem/');
        $keyPemFile = $keyPemPath .$id. '.key.pem';
        $keyPemPass = base64_decode($password_soap);

        $keyDerPass = $password;
        $openssl = new OpenSSL();

        if (!file_exists($keyPemPath))
            mkdir($keyPemPath, 0777, true);

        if (file_exists($keyPemFile)) {
            File::delete($keyPemFile);
            set_time_limit(3);
        }

        // convertir la llave original DER a formato PEM con nueva contraseña, guardar en $keyPemFile
        // lo mismo que los dos pasos anteriores pero en una llamada
        $openssl->derKeyProtect($keyDerFile, $keyDerPass, $keyPemFile, $keyPemPass);

        return $openssl;
    }

    public static function convertCetToPem($id)
    {

        $cerFile = storage_path('app/public/trash/' . $id . '.cer');
        $cerPemPath = storage_path('app/public/timbrado/archs_pem//');
        $cerPemFile = $cerPemPath .$id. '.cer.pem';
        $openssl = new OpenSSL();

        if (!file_exists($cerPemPath))
            mkdir($cerPemPath, 0777, true);

        if (file_exists($cerPemFile)) {
            File::delete($cerPemFile);
            set_time_limit(3);
        }

        // guardar el certificado en PEM a partir del archivo DER usando openssl
        $openssl->derCerConvert($cerFile, $cerPemFile);

        return $openssl;
    }

}
