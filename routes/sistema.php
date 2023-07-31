<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sistema\GeneradorCredencialesController;
use App\Http\Controllers\sistema\UsuarioController;

/*USUARIOS DEL SISTEMA*/
Route::get('/usuarios-del-sistema', [App\Http\Controllers\Sistema\UsuarioController::class, 'sistemaUsuarios'])->name('sistema.usuarios.usuariosistema');

Route::get('/crear-usuario-del-sistema', [App\Http\Controllers\Sistema\UsuarioController::class, 'crearUsuario'])->name('usuarios.crear')->middleware('admin.hrsystem');

Route::get('/editar-usuario-del-sistema/{usuario}', [App\Http\Controllers\Sistema\UsuarioController::class, 'editarUsuario'])->name('usuarios.editar')->middleware('admin.hrsystem');

Route::post('/eliminar-usuario-del-sistema', [App\Http\Controllers\Sistema\UsuarioController::class, 'eliminarUsuario'])->name('usuarios.eliminar')->middleware('admin.hrsystem');

Route::post('/obtener-usuario-sistema/{usuario}',[App\Http\Controllers\Sistema\UsuarioController::class,'obtenerUsuario'])->name('sistema.usuarios.usuario');

Route::post('/usuario-create-update/{id?}',[App\Http\Controllers\Sistema\UsuarioController::class,'addUpdateUsuario'])->name('sistema.usuarios.addUpdateUsuario');
Route::post('/usuario-rel-update',[App\Http\Controllers\Sistema\UsuarioController::class,'addUrelpdateUsuario'])->name('sistema.usuarios.addUrelpdateUsuario');


/*PERMISOS DEL USUARIO DEL SISTEMA*/
Route::get('/permisos-del-usuario/{usuario}/empresa/{empresa}', [App\Http\Controllers\Sistema\UsuarioController::class, 'permisosUsuarioCambiarEmpresa'])->name('sistema.usuarios.permisos.cambiar.empresa')->middleware('admin.hrsystem');

Route::get('/permisos-del-usuario-actualizar/usuario/{usuario}/permiso/{permiso}/estatus/{estatus}/empresa/{empresa?}', [App\Http\Controllers\Sistema\UsuarioController::class, 'permisosUsuarioCambiarPermiso'])->name('usuarios.permisos.cambiar.permiso')->middleware('admin.hrsystem');

Route::post('/empresa-estatus-permiso', [App\Http\Controllers\Sistema\UsuarioController::class, 'permisosUsuarioCambiarPermiso'])->name('sistema.usuarios.cambiarp')->middleware('admin.hrsystem');

/*EMPRESAS, DEPARTAMENTOS Y SEDES DEL USUARIO DEL SISTEMA*/
Route::get('/empresas-del-usuario/{usuario}', [App\Http\Controllers\Sistema\UsuarioController::class, 'empresaUsuario'])->name('usuarios.empresa')->middleware('admin.hrsystem');

Route::get('/asignar-depto-empresa/{empresa}/{usuario}', [App\Http\Controllers\Sistema\UsuarioController::class, 'asignarDepartamentoEmpresa'])->name('usuarios.depto')->middleware('admin.hrsystem');

Route::post('/actualizar-depto-empresa/', [App\Http\Controllers\Sistema\UsuarioController::class, 'actualizarDepartamento'])->name('usuarios.actualizardepto')->middleware('admin.hrsystem');

Route::get('/asignar-sede-empresa/{empresa}/{usuario}', [App\Http\Controllers\Sistema\UsuarioController::class, 'asignarSedeEmpresa'])->name('usuarios.sede')->middleware('admin.hrsystem');

Route::post('/asociar-empresa/', [App\Http\Controllers\Sistema\UsuarioController::class, 'asociarEmpresa'])->name('usuarios.asociarempresa')->middleware('admin.hrsystem');

Route::post('/actualizar-sede-empresa/', [App\Http\Controllers\Sistema\UsuarioController::class, 'actualizarSede'])->name('usuarios.actualizarsede')->middleware('admin.hrsystem');

/*TIMBRADO DE USUARIOS*/
Route::get('/usuarios-timbrado', [App\Http\Controllers\Sistema\UsuarioController::class, 'timbradoUsuarios'])->name('usuarios.timbrado')->middleware('admin.hrsystem');

Route::get('/crear-timbrado-al-usuario/', [App\Http\Controllers\Sistema\UsuarioController::class, 'crearTimbradoUsuario'])->name('usuarios.creartimbrado')->middleware('admin.hrsystem');

Route::post('/agregar-timbrado-al-usuario/', [App\Http\Controllers\Sistema\UsuarioController::class, 'agregarTimbrado'])->name('usuarios.agregartimbrado')->middleware('admin.hrsystem');

Route::post('/eliminar-timbrado-del-usuario', [App\Http\Controllers\Sistema\UsuarioController::class, 'eliminarTimbradoUsuario'])->name('usuarios.eliminartimbrado')->middleware('admin.hrsystem');


/*CONTRATOS DE USUARIOS*/
Route::get('/contratos-de-hrsystem', [App\Http\Controllers\Sistema\ContratosController::class, 'contratosHr'])->name('contratos.contratosHr')->middleware('admin.hrsystem');

Route::get('/crear-contrato-de-hrsystem', [App\Http\Controllers\Sistema\ContratosController::class, 'crearcontratoHr'])->name('contratos.crear')->middleware('admin.hrsystem');

Route::post('/agregar-contrato', [App\Http\Controllers\Sistema\ContratosController::class, 'agregarContrato'])->name('contratos.agregar')->middleware('admin.hrsystem');

Route::get('/editar-contrato-de-hrsystem/{idcontrato}', [App\Http\Controllers\Sistema\ContratosController::class, 'editarContrato'])->name('contrato.editar')->middleware('admin.hrsystem');

Route::post('/actualizar-contratos/', [App\Http\Controllers\Sistema\ContratosController::class, 'actualizarContrato'])->name('contrato.actualizarcontrato')->middleware('admin.hrsystem');

Route::post('/eliminar-contrato', [App\Http\Controllers\Sistema\ContratosController::class, 'eliminarCotrato'])->name('contrato.eliminarcontrato')->middleware('admin.hrsystem');

Route::get('/contratopdf/{idcontrato}',  [App\Http\Controllers\Sistema\ContratosController::class, 'pdfContrato'])->name('contratos.pdfContrato');

Route::post('/generar-contrato-empleado', [App\Http\Controllers\Sistema\ContratosController::class, 'generarContrato'])->name('contratos.generarcontratoEmp');

Route::get('/vigencia-contratos',  [App\Http\Controllers\Sistema\ContratosController::class, 'vigenciaContratos'])->name('contratos.vigenciacontratos');

Route::post('/eliminar-contrato-empleado', [App\Http\Controllers\Sistema\ContratosController::class, 'eliminarContratoVigencia'])->name('contratos.eliminarcontratoEmp');


/*CONCEPTOS DE NOMINA*/
Route::get('/conceptos-de-nomina', [App\Http\Controllers\Sistema\ConceptoNominaController::class, 'conceptosNomina'])->name('conceptos.nominaconceptos')->middleware('admin.hrsystem');

Route::get('/crear-concepto-de-nomina', [App\Http\Controllers\Sistema\ConceptoNominaController::class, 'crearconceptoNomina'])->name('conceptos.crear')->middleware('admin.hrsystem');

Route::post('/agregar-concepto-de-nomina/', [App\Http\Controllers\Sistema\ConceptoNominaController::class, 'agregarConcepto'])->name('conceptos.agregar')->middleware('admin.hrsystem');

Route::get('/editar-concepto-de-nomina/{idconcepto}', [App\Http\Controllers\Sistema\ConceptoNominaController::class, 'editarConcepto'])->name('conceptos.editar')->middleware('admin.hrsystem');

Route::post('/eliminar-concepto-de-nomina', [App\Http\Controllers\Sistema\ConceptoNominaController::class, 'eliminarconceptonomina'])->name('conceptos.eliminarconcepto');


/*EMPRESA RECEPTORA*/
Route::get('/empresa-receptora', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'empresaReceptora'])->name('empresar.empresareceptora')->middleware('admin.hrsystem');

Route::get('/crear-empresa-receptora', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'crearempresaReceptora'])->name('empresar.crear')->middleware('admin.hrsystem');

Route::post('/agregar-empresa-receptora', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'agregarempresaReceptora'])->name('empresar.agregar')->middleware('admin.hrsystem');

Route::get('/editar-empresa-receptora/{empresa}', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'editarempresaReceptora'])->name('empresar.editar')->middleware('admin.hrsystem');

Route::post('/actualizar-empresa-receptora', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'actualizarempresaReceptora'])->name('empresar.actualizarempresa')->middleware('admin.hrsystem');

Route::get('/asignar-empresa-emisora/{empresa}', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'asignarempresaEmisora'])->name('empresar.asignarEmpresaEmisora')->middleware('admin.hrsystem');

Route::post('/asignar-empresa-emisora', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'agregarempresaEmisora'])->name('empresar.empresaemisora')->middleware('admin.hrsystem');

Route::post('/borrar-empresa-emisora', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'eliminarempresaEmisora'])->name('empresar.eliminarememisora')->middleware('admin.hrsystem');

Route::get('/asignar-conceptos/{empresa}', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'asignarempresaConceptos'])->name('empresar.asignarempresaConceptos')->middleware('admin.hrsystem');

Route::post('/agregar-concepto', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'agregarConcepto'])->name('empresar.agregarconcepto')->middleware('admin.hrsystem');

Route::post('/eliminar-concepto-empresa', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'eliminarconceptoEmpresa'])->name('empresar.eliminarconceptoempresa')->middleware('admin.hrsystem');

Route::get('/asignar-contratos/{empresa}', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'asignarempresaContratos'])->name('empresar.asignarempresaContratos')->middleware('admin.hrsystem');

Route::post('/agregar-contrato-empresa', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'agregarcontratoEmpresa'])->name('empresar.agregarcontratoempresa')->middleware('admin.hrsystem');

Route::post('/eliminar-contrato-empresa', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'eliminarcontratoEmpresa'])->name('empresar.eliminarcontratoempresa')->middleware('admin.hrsystem');

Route::post('/eliminar-empresa-receptora', [App\Http\Controllers\Sistema\EmpresaReceptoraController::class, 'borrarempresareceptora'])->name('empresar.borrarempresareceptora')->middleware('admin.hrsystem');


/*EMPRESA EMISORA*/
Route::get('/empresa-emisora', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'empresaEmisora'])->name('empresae.empresaemisora')->middleware('admin.hrsystem');

Route::get('/crear-empresa-emisora', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'crearempresaEmisora'])->name('empresae.crear')->middleware('admin.hrsystem');

Route::post('/agregar-empresa-emisora', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'agregarempresaEmisora'])->name('empresae.agregar')->middleware('admin.hrsystem');

Route::get('/editar-empresa-emisora/{empresa}', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'editarempresaEmisora'])->name('empresae.editar')->middleware('admin.hrsystem');

Route::post('/actualizar-empresa-emisora', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'actualizarempresaEmisora'])->name('empresae.actualizarempresae')->middleware('admin.hrsystem');

Route::post('/eliminar-empresa-emisora', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'borrarempresaemisora'])->name('empresae.borrarempresaemisora')->middleware('admin.hrsystem');

Route::get('/registro-patronal/{empresa}', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'registroPatronal'])->name('empresae.registropatronal')->middleware('admin.hrsystem');

Route::get('/crear-registro-patronal/{empresa}', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'crearregistroPatronal'])->name('empresae.crearregpatronal')->middleware('admin.hrsystem');

Route::post('/agregar-registro-patronal', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'agregarregistroPatronal'])->name('empresae.agregarregistro')->middleware('admin.hrsystem');

Route::get('/editar-registro-patronal/{empresa}/{registro}', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'editarregistroPatronal'])->name('empresae.editarregistrop')->middleware('admin.hrsystem');

Route::post('/actualizar-registro-patronal', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'actualizarregistroPatronal'])->name('empresae.actualizarregistropatronal')->middleware('admin.hrsystem');

Route::post('/eliminar-registro-patronal', [App\Http\Controllers\Sistema\EmpresaEmisoraController::class, 'borrarregistropatronal'])->name('empresae.borrarregistro')->middleware('admin.hrsystem');

/***** B I O M E T R I C O S ***** */

Route::get('/biometricos', [\App\Http\Controllers\Sistema\BiometricosController::class,'ver'])->name('sistema.biometricos.inicio');
Route::get('/get-biometricos', [\App\Http\Controllers\Sistema\BiometricosController::class,'getBiometricos'])->name('sistema.biometricos.getbiometricos');
Route::post('/biometricos', [\App\Http\Controllers\Sistema\BiometricosController::class,'crear'])->name('sistema.biometricos.crear');
Route::delete('/biometricos/{id}', [\App\Http\Controllers\Sistema\BiometricosController::class,'eliminar'])->name('sistema.biometricos.eliminar');

//Generacion de credencialess
Route::get('/credencial/crear', [GeneradorCredencialesController::class,'index'])->name('sistema.credencial.index');
Route::get('/credencial/empresa/{id?}', [GeneradorCredencialesController::class,'empleados'])->name('sistema.credencial.empleados');
Route::get('/credencial/empresa/credenciales/{id?}', [GeneradorCredencialesController::class,'createCredenciales'])->name('sistema.credencial.createCredenciales');
Route::get('/credencial/empresa/credencial/{id_empresa?}/{id?}', [GeneradorCredencialesController::class,'createCredencial'])->name('sistema.credencial.descargarCredencial');
