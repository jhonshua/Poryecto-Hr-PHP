<?php

namespace App\Http\Controllers\norma;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActividadesNorma;
use App\Models\Actividad;
use App\Models\PeriodoImplementacion;
use App\Models\PeriodoNorma;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Session;

class ActividadController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    protected const ACTIVIDAD_CREADA = 1;
    protected const ACTIVIDAD_ELIMINADA = 0;
    protected const PERIODO_NORMA_CREADO = 1;
    protected const PERIODO_NORMA_ELIMINADO = 0;

    /* Reporte en excel de las actividades de la norma 035 */
    public function exportarActividades($id_implementacion)
    {
        return Excel::download(
            new ActividadesNorma($id_implementacion),
            'ActividadesNorma' . date('d-m-Y_H:i') . '.xlsx'
        );
    }

    public function verActividades(Request $request)
    {
        elegirBase();
        $actividad = Actividad::find($request->idactividad);
        return response()->json(['ok' => 1, 'actividad' => $actividad]);
    }

    public function crearActividades(Request $request)
    {
        elegirBase();
        $fecha_final = $request->fecha_fin;
        $inicio = new DateTime($request->fecha_inicio);
        $fin_expansion = new DateTime($fecha_final);
        $fin = new DateTime($fecha_final);
        $notificacion = ($request->notificacion == 1) ? 1 : 0;
        $id_periodo_norma = null;
        $actual = new DateTime();
     
        if ($request->apertura_formulario == 1) {
            $id_periodo_norma = PeriodoNorma::insertGetId(
                [
                    'fecha_inicio' => $inicio->format('Y-m-d') . ' 00:00:00',
                    'fecha_fin' => $fin->format('Y-m-d') . ' 23:59:59',
                    'fecha_fin_expansion' => $fin_expansion->add(new DateInterval('P3D'))->format('Y-m-d') . ' 23:59:59',
                    'estatus' => 1,
                    'created_at' => $actual->format('Y-m-d h:i')
                ]
            );
        }
        if ($id_periodo_norma) {
            $actividad = Actividad::insert(
                [
                    'descripcion' => $request->descripcion,
                    'fecha_inicio' => $inicio->format('Y-m-d') . ' 00:00:00',
                    'fecha_fin' => $fin->format('Y-m-d') . ' 23:59:59',
                    'notificacion' => $notificacion,
                    'estatus' => 1,
                    'idperiodo_implementacion' => $request->idimplementacion,
                    'apertura_formulario' => $id_periodo_norma,
                    'created_at' => $actual->format('Y-m-d h:i')
                ]
            );
            mailNotificarEncargado($request->idimplementacion, 'Actividad de llenado de cuestionarios llamada <b>"' . $request->descripcion . '"</b> creada para la implementación de ' . Session::get('empresa')['razon_social'] . '<br/>
            Fecha inicio: ' . $inicio->format('d-m-Y') . '<br/>Fecha fin: ' . $fin->format('d-m-Y') . "<br/>Fecha fin periodo expansión: " . $fin_expansion->add(new DateInterval('P3D'))->format('d-m-Y'), "Actividad de llenado de cuestionarios " . Session::get('empresa')['razon_social']);
        } else {
            $actividad = Actividad::insert(
                [
                    'descripcion' => $request->descripcion,
                    'fecha_inicio' => $inicio->format('Y-m-d') . ' 00:00:00',
                    'fecha_fin' => $fin->format('Y-m-d') . ' 23:59:59',
                    'notificacion' => $notificacion,
                    'estatus' => 1,
                    'idperiodo_implementacion' => $request->idimplementacion,
                    'created_at' => $actual->format('Y-m-d h:i')
                ]
            );
            if ($notificacion) {
                mailNotificarEncargado($request->idimplementacion, 'Actividad <b>"' . $request->descripcion . '"</b> creada para la implementación de ' . Session::get('empresa')['razon_social'] . '<br/>
                Fecha inicio: ' . $inicio->format('d-m-Y') . '<br/>Fecha fin: ' . $fin->format('d-m-Y'));
            }
        }
        if ($actividad) {
            return response()->json(['ok' => 1, 'msg' => 'La actividad se creó con éxito']);
        }
        return response()->json(['ok' => 0, 'msg' => 'La actividad no se pudo crear, vuelva a intentarlo.']);
    }

    public function modificarActividades(Request $request)
    {
        // cambiarBase(Session::get('base'));
        elegirBase();
        $inicio = new DateTime($request->fecha_inicio);
        $fin = new DateTime($request->fecha_fin);
        $notificacion = ($request->notificacion == 1) ? 1 : 0;
        $actividad = Actividad::find($request->id);

        $actividad->descripcion = $request->descripcion;
        $actividad->fecha_inicio = $inicio->format('Y-m-d') . ' 00:00:00';
        $actividad->fecha_fin = $fin->format('Y-m-d') . ' 11:59:59';
        $actividad->notificacion = $notificacion;

        if ($actividad->apertura_formulario) {
            $periodo_norma = PeriodoNorma::find($actividad->apertura_formulario);
            $periodo_norma->fecha_inicio = $inicio->format('Y-m-d') . ' 00:00:00';
            $periodo_norma->fecha_fin = $fin->format('Y-m-d') . ' 23:59:59';
            $periodo_norma->fecha_fin_expansion = $fin->add(new DateInterval('P3D'))->format('Y-m-d') . ' 23:59:59';

            if ($periodo_norma->update()) {
                if ($actividad->save()) {
                    mailNotificarEncargado($actividad->idimplementacion, 'Actividad <b>"' . $actividad->descripcion . '"</b> modificada para la implementación de ' . Session::get('empresa')['razon_social'] . '<br/>
                    Fecha inicio: ' . $inicio->format('d-m-Y') . '<br/>Fecha fin: ' . $fin->format('d-m-Y') . '<br/>Fecha fin Expansión: ' . $fin->add(new DateInterval('P3D'))->format('d-m-Y'), "Actividad formulario actualizada " . Session::get('empresa')['razon_social']);
                    return response()->json(['ok' => 1, 'msg' => 'La actividad se actualizó con éxito']);
                }
                return response()->json(['ok' => 0, 'msg' => 'El periodo de norma no se actualizó,intente nuevamente']);
            }
        }
        if ($actividad->save()) {
            mailNotificarEncargado($actividad->idimplementacion, 'Actividad <b>"' . $actividad->descripcion . '"</b> modificada para la implementación de ' . Session::get('empresa')['razon_social'] . '<br/>
            Fecha inicio: ' . $inicio->format('d-m-Y') . '<br/>Fecha fin: ' . $fin->format('d-m-Y'), "Actividad actualizada " . Session::get('empresa')['razon_social']);
            return response()->json(['ok' => 1, 'msg' => 'La actividad se actualizó con éxito']);
        }
        return response()->json(['ok' => 0, 'msg' => 'La actividad no se actualizó,intente nuevamente']);
    }

    public function validarPeriodo(Request $request)
    {
        elegirBase();
        $periodo = Actividad::whereNotNull('apertura_formulario')->where('estatus', self::ACTIVIDAD_CREADA)->where('idperiodo_implementacion', $request->implementacion)->get();
        return response()->json(['ok' => 1, 'periodo' => count($periodo)]);
    }

    public function borrarActividades(Request $request)
    {
        elegirBase();
        $actividad = Actividad::find($request->idactividad);
        $actividad->estatus = 0;
        if ($actividad->apertura_formulario) {
            $periodo_norma = PeriodoNorma::find($actividad->apertura_formulario);
            $periodo_norma->estatus = self::PERIODO_NORMA_ELIMINADO;
            if ($periodo_norma->update()) {
                if ($actividad->save()) {
                    mailNotificarEncargado($actividad->idimplementacion, 'Actividad <b>"' . $actividad->descripcion . '"</b> eliminada para la implementación de ' . Session::get('empresa')['razon_social'], "Actividad eliminada " . Session::get('empresa')['razon_social']);
                    return response()->json(['ok' => 1, 'msg' => 'La actividad se eliminó con éxito']);
                }
                return response()->json(['ok' => 0, 'msg' => 'El periodo de norma no se actualizó, intente nuevamente']);
            }
        }
        if ($actividad->save()) {
            mailNotificarEncargado($actividad->idimplementacion, 'Actividad <b>"' . $actividad->descripcion . '"</b> eliminada para la implementación de ' . Session::get('empresa')['razon_social'], "Actividad eliminada " . Session::get('empresa')['razon_social']);
            return response()->json(['ok' => 1, 'msg' => 'La actividad se eliminó con éxito']);
        }
        return response()->json(['ok' => 0, 'msg' => 'La actividad no se eliminó, intente nuevamente']);
    }

    /* Diagrama de actividades */
    public function diagramaActividades(Request $request)
    {
        if ($request->implementacion) {
            $implementacion = $request->implementacion;

            elegirBase();

            if ($request->ajax()) {
            } else {
                $datosImplementacion = PeriodoImplementacion::find($implementacion);
                $actividades = $datosImplementacion->actividades->where('estatus', 1);
            
                return view("norma.implementacion.diagrama-actividades", compact("datosImplementacion", "actividades"));
            }
        }
        return view("norma.norma-tabla");;
    }
}
