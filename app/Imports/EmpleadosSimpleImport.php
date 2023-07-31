<?php

namespace App\Imports;
use App\EmpleadoLogin;
use App\Models\Puesto;
use App\Models\Horario;
use App\Models\Empleado;
use App\Models\Sede;
use Illuminate\Support\Facades\Schema;
use App\Models\Departamento;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};

class EmpleadosSimpleImport implements ToCollection, WithHeadingRow
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

        $departamentos = Departamento::select('id', 'nombre')->get()->keyBy('id');
        $departamentos = $this->flat2ItemCollection($departamentos, 'nombre', 'id');
   
        $categorias = DB::connection('empresa')->table('categorias')->get()->keyBy('id');
        $categorias = $this->flat2ItemCollection($categorias, 'nombre', 'id');
    
         if(Session::get('empresa')['sede']){
             $sedes = Sede::get()->keyBy('id');
             $sedes = $this->flat2ItemCollection($sedes, 'nombre', 'id');
         } else {
             $sedes = [];
         }
        // dd($sedes);
        // Obtenemos los empleados YA existentes
        $empleadosExistentes = Empleado::select('id', 'nombre', 'apaterno', 'amaterno', 'rfc', 'curp', 'numero_empleado', 'nss', 'correo', 'estatus', 'fecha_baja')->get()->keyBy('id');

        //dd($rows);
        // Se recorre cada fila de EXCEL
        foreach ($rows as $row){
            
            // Si el renglon no esta vacio
            if($row->filter()->isNotEmpty()){

                $nombreCompletoEmpleadoAImportar = $row['apaterno'].' '.$row['amaterno'].' '.$row['nombre'];

                // Buscamos que el empleado a importar no exista YA actualmente
                foreach($empleadosExistentes as $idEmpleadoExistente => $empleadoExistente){

                    $nombreCompletoEmpleadoExistente = $empleadoExistente->apaterno.' '.$empleadoExistente->amaterno.' '.$empleadoExistente->nombre;
                    
    
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
                //$camposAValidar = ["numero_empleado", "apaterno", "amaterno", "nombre", "rfc", "curp", "fecha_nacimiento", "edad", "fecha_alta", "lugar_nacimiento", "genero", "categoria", "departamento", "ubicacion", "tipo_jornada", "tipo_contrato", "nss", "tipo_de_nomina", "salario_diario", "sueldo_neto", "salario_digital", "nacionalidad", "calle_numero", "colonia", "delegacion", "estado", "cp", "correo", "telefono_movil", "estado_civil", "escolaridad", "profesion", "avisar_a", "beneficiario", "beneficiario_parentesco", "fecha_antiguedad", "puesto", "tipo_empleado", "tipo_salario"];
                $camposAValidar = ["numero_empleado", "apaterno", "amaterno", "nombre","correo", "genero"];

                $camposVacios = [];
                foreach ($camposAValidar as $campo) {
                    if(empty($row[$campo])) { $camposVacios[] = ucfirst(str_replace('_', ' ', $campo)); }
                }
                if(count($camposVacios) > 0){
                    $this->mensajeDeError .= 'El registro de '.$nombreCompletoEmpleadoAImportar.' le falta(n) '.count($camposVacios).' campo(s) por llenar ('.implode(', ', $camposVacios).'). <br>';
                    $this->errores++;
                }
                //dd($row['sede'],$sedes,array_key_exists( $row['sede'],$sedes));
                if(!array_key_exists( $row['sede'],$sedes)){
                    $this->mensajeDeError .= 'La sede "'.$row['sede'].'" no existe. <br>';
                    $this->errores++;
                }
                
                // si hay errores no insertamos y saltamos al otro empleado
                if($this->errores > 0){
                    $this->mensajeDeError .= '<br>';
                    continue;
                }

                /////////////////////////////////////////////////


                $id_sede = "";
                if(!empty($row['sede'])){

                    $id_sede = $sedes[$row['sede']];
                }

                $id_categoria = "";
                if(!empty($row['categoria'])){

                    $id_categoria = $categorias[$row['categoria']];
                }

                $id_departamento = "";
                if(!empty($row['departamento']) && isset($departamentos[$row['departamento']])){
                    $id_departamento = $departamentos[$row['departamento']];
                }

                $empleado = [
                    'numero_empleado' => ($row['numero_empleado']) ?? '',
                    'estatus' => Empleado::EMPLEADO_ACTIVO,
                    'nombre' => ($row['nombre']) ? strtoupper($row['nombre']) : '',
                    'apaterno' => ($row['apaterno']) ? strtoupper($row['apaterno']) : '',
                    'amaterno' => ($row['amaterno']) ? strtoupper($row['amaterno']) : '',
                    'genero' => ($row['genero']) ? strtoupper($row['genero']) : '',
                    'correo' => ($row['correo']) ? strtolower($row['correo']) : '',
                    'rfc' => 'XXXXXXX',
                    'curp' => 'XXXXXXXXXXXXX',
                 /*   'fecha_nacimiento' => ($row['fecha_nacimiento']) ?? '',
                    'lugar_nacimiento' => ($row['lugar_nacimiento']) ?? '',
                    'nacionalidad' => ($row['nacionalidad']) ? strtoupper($row['nacionalidad']) : '',
                    'estado_civil' => ($row['estado_civil']) ? strtoupper($row['estado_civil']) : '',
                    'escolaridad' => ($row['escolaridad']) ? strtoupper($row['escolaridad']) : '',
                    'profesion' => ($row['profesion']) ? strtoupper($row['profesion']) : '',
                    'calle_numero' => ($row['calle_numero']) ? strtoupper($row['calle_numero']) : '',
                    'colonia' => ($row['colonia']) ? strtoupper($row['colonia']) : '',
                    'delegacion' => ($row['delegacion']) ? strtoupper($row['delegacion']) : '',
                    'estado' => ($row['estado']) ? strtoupper($row['estado']) : '',
                    'cp' => ($row['cp']) ?? '',
                    'telefono_movil' => ($row['telefono_movil']) ?? '',
                    'telefono_casa' => ($row['telefono_casa']) ?? '',
                    'nss' => ($row['nss']) ?? '',*/
                    'id_categoria' => $id_categoria, //----------
                    'id_departamento' => $id_departamento, //----------
                /*    'id_prestacion' => $id_prestacion, // ------------
                    'id_horario' => ($id_horario !== false) ?: '', //----------
                    'id_puesto' => ($id_puesto !== false) ?: '', //----------*/
                    'repositorio' => '************************',
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'fecha_edicion' => date('Y-m-d H:i:s'),
                    'fecha_alta' => date('Y-m-d H:i:s'),
                    'fecha_antiguedad' => date('Y-m-d H:i:s'),
                   /* 'tipo_contrato' => ($row['tipo_contrato']) ?? '',
                    'tipo_empleado' => ($row['tipo_empleado']) ?? '',
                    'tipo_jornada' => ($row['tipo_jornada']) ?? '',
                    'tipo_de_nomina' => ($row['tipo_de_nomina']) ? isset($row['tipo_de_nomina']) : '',
                    'forma_de_pago' => (isset($row['forma_de_pago'])) ?: '',
                    'salario_diario' => ($row['salario_diario']) ?? '',
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
                    'valor_fonacot' => ($row['valor_fonacot']) ?? '',*/
                     'sede' => $id_sede, //----------   
                ];

             
                // Si inserta el registro
                if($nuevo_empleado = Empleado::create($empleado)){

                    // actualizar "repositorio"
                    // $nuevo_empleado->repositorio = 'repositorio/' . Session::get('empresa')['id'] . '/' . $nuevo_empleado->id;
                    // crear el folder del usuario
                    // $folderRepositorio = public_path() . '/' . $nuevo_empleado->repositorio;
                    // File::makeDirectory($folderRepositorio, $mode = 777, true, true);
                    // $nuevo_empleado->save();
                    // insert en log_incidencias
               /*     DB::connection('generica')->table('log_incidencias')->insert([
                        'id_empleado' => $nuevo_empleado->id,
                        'fecha' => date('Y-m-d'),
                        'tipo' => 'ALTA',
                        'ejecutivo' => Auth::user()->email,
                        'descripcion' => 'ALTA',
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    ]);
*/

                    /*
                    EmpleadoLogin::create([
                        'email' => strtolower($row['correo']),
                        'password' => bcrypt("123456"),
                       // 'password' => '*6BB4837EB74329105EE4568DDA7DC67ED2CA2AD9',
                        'empresa' => Session::get('base'),
                        'estatus' => 1,
                        'tmp' => "123456"
                    ]);
*/

                    EmpleadoLogin::updateOrInsert(
                        [
                            'email' => strtolower($row['correo']),
                            'empresa' => Session::get('base'),
                        ], [
                            'email' => strtolower($row['correo']),
                            'password' => bcrypt("123456"),
                        // 'password' => '*6BB4837EB74329105EE4568DDA7DC67ED2CA2AD9',
                            'empresa' => Session::get('base'),
                            'estatus' => 1,
                            'tmp' => "123456"
                        ]);
                    
                    $this->importados++;
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
