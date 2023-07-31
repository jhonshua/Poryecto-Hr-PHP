<?php

namespace App\Imports;

use App\Models\Puesto;
use App\Models\Horario;
use App\Models\Empleado;
use App\Models\Departamento;
use Carbon\Carbon;
use DateTime;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};

class EmpleadosImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;

    private $errores = 0;
    private $mensajeDeError = '';
    private $importados = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));

        $tipoNomina = Session::get('empresa.parametros')[0]['tipo_nomina'];

        $esSindical = DB::connection('empresa')->table('conceptos_nomina')
            ->where('file_rool', 0)
            ->where('estatus', 1)
            ->count();

        $horarios = Horario::select('id', 'alias')->where('estatus', 1)->get()->keyBy('id');
        $horarios = $this->flat2ItemCollection($horarios, 'id', 'alias');

        $departamentos = Departamento::select('id', 'nombre')->get()->keyBy('id');
        $departamentos = $this->flat2ItemCollection($departamentos, 'id', 'nombre');

        $puestos = Puesto::select('id', 'puesto')->get()->keyBy('id');
        $puestos = $this->flat2ItemCollection($puestos, 'id', 'puesto');

        $categorias = DB::connection('empresa')->table('categorias')->get()->keyBy('id');
        $categorias = $this->flat2ItemCollection($categorias, 'id', 'nombre');

        $bancos = DB::table('bancos')->get()->keyBy('id');
        $bancos = $this->flat2ItemCollection($bancos, 'id', 'nombre');


        // Obtenemos los empleados YA existentes
        $empleadosExistentes = Empleado::select('id', 'nombre', 'apaterno', 'amaterno', 'rfc', 'curp', 'numero_empleado', 'nss', 'correo', 'estatus', 'fecha_baja')->get()->keyBy('id');


        // Se recorre cada fila de EXCEL
        foreach ($rows as $row){

            // Si el renglon no esta vacio
            if($row->filter()->isNotEmpty()){

                $nombreCompletoEmpleadoAImportar = $row['apaterno'].' '.$row['amaterno'].' '.$row['nombre'];

                // Buscamos que el empleado a importar no exista YA actualmente
                foreach($empleadosExistentes as $idEmpleadoExistente => $empleadoExistente){

                    $nombreCompletoEmpleadoExistente = $empleadoExistente->apaterno.' '.$empleadoExistente->amaterno.' '.$empleadoExistente->nombre;

                    // Busqueda de RFC
                    if($empleadoExistente->rfc == $row['rfc']){

                        if(($empleadoExistente->estatus == Empleado::EMPLEADO_BAJA || $empleadoExistente->estatus == Empleado::EMPLEADO_INACTIVO) && $empleadoExistente->fecha_baja != '0000-00-00'){
                            // empleado dado de baja anteriormente (quieren reingresarlo)
                            $this->mensajeDeError .= 'Este RFC: '.$row['rfc'].' ya fue agregado anteriormente. Verificar las Bajas.<br>';
                        }
                        elseif($empleadoExistente->estatus == Empleado::EMPLEADO_ACTIVO){
                            // Empleado activo
                            $this->mensajeDeError .= 'El RFC: '.$row['rfc'].' ya se encuentra asignado al empleado: '.$nombreCompletoEmpleadoExistente.'.<br>';
                        } else if($empleadoExistente->estatus == Empleado::EMPLEADO_BAJA_DEFINITIVO){
                            $this->mensajeDeError .= "El empleado ". $empleadoExistente->nombre . " " . $empleadoExistente->apaterno . " " . $empleadoExistente->amaterno  . " baja definitiva" ;
                        } else{
                            $this->mensajeDeError .="Error desconocido, consulte al area de desarrollo";
                        }
                        $this->errores++;
                    }

                    // Busqueda de CURP
                    if($empleadoExistente->curp == $row['curp'] && $empleadoExistente->estatus == Empleado::EMPLEADO_ACTIVO){
                        $this->mensajeDeError .= 'El CURP: '.$row['curp'].' ya se encuentra asignado al empleado: '.$nombreCompletoEmpleadoExistente.'.<br>';
                        $this->errores++;
                    }

                    // Busqueda de Num seguro social
                    if($empleadoExistente->nss == $row['nss'] && $empleadoExistente->estatus == Empleado::EMPLEADO_ACTIVO && $tipoNomina != 'soloSindical'){
                        $this->mensajeDeError .= 'El NSS: '.$row['nss'].' ya se encuentra asignado al empleado: '.$nombreCompletoEmpleadoExistente.'.<br>';
                        $this->errores++;
                    }

                    // Busqueda de Num empleado
                    if($empleadoExistente->numero_empleado == $row['numero_empleado'] && $empleadoExistente->estatus == Empleado::EMPLEADO_ACTIVO){
                        $this->mensajeDeError .= 'El numero de empleado: '.$row['numero_empleado'].' ya se encuentra asignado al empleado: '.$nombreCompletoEmpleadoExistente.'.<br>';
                        $this->errores++;
                    }

                    // Busqueda de Correo
                    if($empleadoExistente->correo == $row['correo'] && $empleadoExistente->estatus == Empleado::EMPLEADO_ACTIVO){
                        $this->mensajeDeError .= 'El correo: '.$row['correo'].' ya se encuentra asignado al empleado: '.$nombreCompletoEmpleadoExistente.'.<br>';
                        $this->errores++;
                    }

                }

                // Validacion de valores de los datos a importar
                $camposAValidar = ["numero_empleado", "apaterno", "amaterno", "nombre", "rfc", "curp", "fecha_nacimiento", "fecha_alta", "lugar_nacimiento", "genero", "categoria", "departamento", "ubicacion", "tipo_jornada", "tipo_contrato", "nss", "tipo_de_nomina", "salario_diario", "sueldo_neto", "salario_digital", "nacionalidad", "calle_numero", "colonia", "delegacion", "estado", "cp", "correo", "telefono_movil", "estado_civil", "escolaridad", "profesion", "avisar_a", "beneficiario", "beneficiario_parentesco", "fecha_antiguedad", "puesto"];

                $camposVacios = [];
                foreach ($camposAValidar as $campo) {
                    if(empty($row[$campo])) { $camposVacios[] = ucfirst(str_replace('_', ' ', $campo)); }
                }
                if(count($camposVacios) > 0){
                    $this->mensajeDeError .= 'El registro de '.$nombreCompletoEmpleadoAImportar.' le falta(n) '.count($camposVacios).' campo(s) por llenar ('.implode(', ', $camposVacios).'). <br>';
                    $this->errores++;
                }

                // Busqueda de IDs por strings
                $id_categoria = array_search($row['categoria'], $categorias);
                if($id_categoria === false && $tipoNomina != 'soloSindical'){
                    $this->mensajeDeError .= 'La prestacion: '.$row['categoria'].' de '.$nombreCompletoEmpleadoAImportar.' no existe, por favor consulta las prestaciones disponibles. <br>';
                    $this->errores++;
                }

                $id_departamento = array_search($row['departamento'], $departamentos);
                if($id_departamento === false){
                    $this->mensajeDeError .= 'El departamento: '.$row['departamento'].' de '.$nombreCompletoEmpleadoAImportar.' no existe, por favor consulta las prestaciones disponibles. <br>';
                    $this->errores++;
                }

                $id_puesto = array_search($row['puesto'], $puestos);
                if($id_puesto === false){
                    $this->mensajeDeError .= 'El puesto: '.$row['puesto'].' de '.$nombreCompletoEmpleadoAImportar.' no existe, por favor consulte los puestos disponibles. <br>';
                    $this->errores++;
                }


                // si hay errores no insertamos y saltamos al otro empleado
                if($this->errores > 0){
                    $this->mensajeDeError .= '<br>';
                    continue;
                }

                /////////////////////////////////////////////////
                $fecha_antiguedad = Carbon::instance($dt = new DateTime($row['fecha_antiguedad']));
                $fecha_alta = Carbon::instance($dt = new DateTime($row['fecha_alta']));
                $fecha_nacimiento = Carbon::instance($dt = new DateTime($row['fecha_nacimiento']));
                $hoy = Carbon::now();
                $antiguedad = $fecha_antiguedad->diffInYears($hoy);

                $prestaciones = DB::connection('empresa')->table('prestaciones')
                    ->select('*')->join('categorias', 'prestaciones.id_categoria', '=', 'categorias.id')
                    ->where('categorias.id', $id_categoria)
                    ->where('prestaciones.antiguedad', $antiguedad)
                    ->where('categorias.estatus', 1)
                    ->where('prestaciones.estatus', 1)
                    ->first();

                $salario_diario_integrado = round($prestaciones->factor_integracion * $row['salario_diario'], 2);
                $id_prestacion = $prestaciones->id;
                $dias_vacaciones = $prestaciones->vacaciones;
                $dias_aguinaldo = $prestaciones->aguinaldo;
                $porcentaje_prima = $prestaciones->prima_vacacional;

                $id_horario = array_search($row['horario'], $horarios);
                $id_puesto = array_search($row['puesto'], $puestos);
                $id_banco = array_search($row['banco'], $bancos);

                $empleado = [
                    'numero_empleado' => ($row['numero_empleado']) ?? '',
                    'estatus' => Empleado::EMPLEADO_ACTIVO,
                    'nombre' => ($row['nombre']) ? strtoupper($row['nombre']) : '',
                    'apaterno' => ($row['apaterno']) ? strtoupper($row['apaterno']) : '',
                    'amaterno' => ($row['amaterno']) ? strtoupper($row['amaterno']) : '',
                    'genero' => ($row['genero']) ? strtoupper($row['genero']) : '',
                    'correo' => ($row['correo']) ? strtolower($row['correo']) : '',
                    'rfc' => ($row['rfc']) ? strtoupper($row['rfc']) : '',
                    'curp' => ($row['curp']) ? strtoupper($row['curp']) : '',
                    'fecha_nacimiento' => ($row['fecha_nacimiento']) ? $fecha_nacimiento->format('Y-m-d') : '',
                    'lugar_nacimiento' => ($row['lugar_nacimiento']) ? $row['lugar_nacimiento'] : '',
                    'nacionalidad' => ($row['nacionalidad']) ? strtoupper($row['nacionalidad']) : '',
                    'estado_civil' => ($row['estado_civil']) ? strtoupper($row['estado_civil']) : '',
                    'escolaridad' => ($row['escolaridad']) ? strtoupper($row['escolaridad']) : '',
                    'profesion' => ($row['profesion']) ? strtoupper($row['profesion']) : '',
                    'calle_numero' => ($row['calle_numero']) ? strtoupper($row['calle_numero']) : '',
                    'colonia' => ($row['colonia']) ? strtoupper($row['colonia']) : '',
                    'delegacion' => ($row['delegacion']) ? strtoupper($row['delegacion']) : '',
                    'estado' => ($row['estado']) ? strtoupper($row['estado']) : '',
                    'cp' => ($row['cp']) ? $row['cp'] : '',
                    'telefono_movil' => ($row['telefono_movil']) ? $row['telefono_movil']: '',
                    'telefono_casa' => ($row['telefono_casa']) ? $row['telefono_casa']: '',
                    'nss' => ($row['nss']) ?? '',
                    'id_categoria' => ($id_categoria !== false) ? $id_categoria: '', //----------
                    'id_departamento' => ($id_departamento !== false) ? $id_departamento : '', //----------
                    'id_prestacion' => $id_prestacion, // ------------
                    'id_horario' => ($id_horario !== false) ? $id_horario: '', //----------
                    'id_puesto' => ($id_puesto !== false) ? $id_puesto: '', //----------
                    'repositorio' => '************************',
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'fecha_edicion' => date('Y-m-d H:i:s'),
                    'fecha_alta' => ($row['fecha_alta'])? $fecha_alta->format('Y-m-d') : $fecha_antiguedad->format('Y-m-d'),
                    'fecha_antiguedad' => $fecha_antiguedad->format('Y-m-d'),
                    'tipo_contrato' => ($row['tipo_contrato']) ?? '',
                    'tipo_empleado' => ($row['tipo_empleado']) ?? '',
                    'tipo_jornada' => ($row['tipo_jornada']) ?? '',
                    'tipo_de_nomina' => ($row['tipo_de_nomina']) ? isset($row['tipo_de_nomina']) ? $row['tipo_de_nomina'] : '' : '',
                    'forma_de_pago' => (isset($row['forma_de_pago'])) ? $row['forma_de_pago'] : '',
                    'salario_diario' => ($row['salario_diario']) ? $row['salario_diario'] : '',
                    'salario_diario_integrado' => $salario_diario_integrado,
                    'dias_vacaciones' => $dias_vacaciones,
                    'dias_aguinaldo' => $dias_aguinaldo,
                    'porcentaje_prima' => $porcentaje_prima,
                    'ubicacion' => ($row['ubicacion']) ?? '',
                    'id_banco' => ($id_banco !== false) ?: '', //----------
                    'tipo_cuenta' => ($row['tipo_cuenta']) ?? '',
                    'clabe_interbancaria' => ($row['clabe_interbancaria']) ?? '',
                    'cuenta_bancaria' => ($row['cuenta_bancaria']) ?? '',
                    'avisar_a' => ($row['avisar_a']) ? strtoupper($row['avisar_a']) : '',
                    'avisar_a_telefono' => ($row['avisar_a_telefono']) ?? '',
                    'avisar_a_parentesco' => ($row['avisar_a']) ? strtoupper($row['avisar_a']) : '',
                    'beneficiario' => ($row['beneficiario']) ? strtoupper($row['beneficiario']) : '',
                    'beneficiario_parentesco' => ($row['beneficiario_parentesco']) ? strtoupper($row['beneficiario_parentesco']) : '',
                    'tipo_salario' => strtoupper($row['tipo_salario']),
                    'num_credito_infonavit' => ($row['num_credito_infonavit']) ?? '',
                    'tipo_descuento' => ($row['tipo_descuento']) ?? '',
                    'valor_descuento' => ($row['valor_descuento']) ?? '',
                    'num_credito_fonacot' => ($row['num_credito_fonacot']) ?? '',
                    'valor_fonacot' => ($row['valor_fonacot']) ?? '',
                ];

                // Si NO es sindical se agregan campos adicionales
                if(!$esSindical){
                    $campos_adicionales = [
                        'sueldo_neto' => ($row['sueldo_neto']) ?? '', /* *** */
                        'salario_digital' => ($row['salario_digital']) ?? '', /*** */
                    ];

                    $empleado = array_merge($empleado, $campos_adicionales);
                }


                // Si inserta el registro
                if($nuevo_empleado = Empleado::create($empleado)){

                    // actualizar "repositorio"
                    $nuevo_empleado->repositorio = 'repositorio/' . Session::get('empresa')['id'] . '/' . $nuevo_empleado->id;
                    // crear el folder del usuario
                    $folderRepositorio = public_path() . '/' . $nuevo_empleado->repositorio;
                    File::makeDirectory($folderRepositorio, $mode = 0755, true, true);
                    $nuevo_empleado->save();
                    $this->importados++;
                }else{
                    dump($nuevo_empleado );   exit;
                }

            }
        }

    }

    /**
     * Aplana un Collection para facilitar la busqueda de datos
     */
    private function flat2ItemCollection($collection, $key, $value)
    {
        $flaten = [];
        foreach ($collection as $item) {
            $flaten[$item->$key] = $item->$value;
        }
        return $flaten;
    }

    /**
     * Regresa los resultados de la importacion
     */
    public function getImportedResults()
    {
        return [
            'importados' => $this->importados,
            'errores' => $this->errores,
            'mensajeDeError' => $this->mensajeDeError
        ];
    }
}
