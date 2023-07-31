<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<style type="text/css">
    .wrapper-table{
        height: 530px;
        margin-bottom: 20px;
        overflow-y: scroll;
        width: 100%;
    }



</style>

<div class="container">

@include('includes.header',['title'=>'Catalogos',
        'subtitle'=>'Empleados', 'img'=>'img/catalogo-empleado.png',
        'route'=>'empleados.empleados'])

	<div class="article border">

		<div class="row">
		    <div class="col-md-2">
		        <table class="table mb-0">
		            <thead>
		                <tr>
		                    <th width="40px">CATEGORIAS</th>
		                </tr>
		            </thead>
		        </table>
		        <div class="wrapper-table">
		            <table class="table empleados">
		                
		                    @foreach ($categorias as $categoria)
		                        <tr>
		                            <td width="60px">{{$categoria->nombre}}</td>
		                        </tr>
		                    @endforeach
		                </tbody>
		            </table>
		        </div>
		    </div>

		    <div class="col-md-2">
		        <table class="table mb-0">
		            <thead >
		                <tr>
		                    <th width="40px">DEPARTAMENTO</th>
		                </tr>
		            </thead>
		        </table>
		        <div class="wrapper-table">
		            <table class="table empleados">
		                
		                    @foreach ($deptos as $depto)
		                        <tr>
		                            <td width="60px">{{$depto->nombre}}</td>
		                        </tr>
		                    @endforeach
		                </tbody>
		            </table>
		        </div>
		    </div>

		    <div class="col-md-3">
		        <table class="table mb-0">
		            <thead>
		                <tr>
		                    <th width="40px">PUESTOS</th>
		                </tr>
		            </thead>
		        </table>
		        <div class="wrapper-table">
		            <table class="table empleados">
		                
		                    @foreach ($puestos as $puesto)
		                        <tr>
		                            <td width="60px">{{$puesto->puesto}}</td>
		                        </tr>
		                    @endforeach
		                </tbody>
		            </table>
		        </div>
		    </div>

		    <div class="col-md-3">
		        <table class="table mb-0">
		            <thead>
		                <tr>
		                    <th width="40px">BANCOS</th>
		                </tr>
		            </thead>
		        </table>
		        <div class="wrapper-table">
		            <table class="table empleados">
		                
		                    @foreach ($bancos as $banco)
		                        <tr>
		                            <td width="60px">{{$banco->nombre}}</td>
		                        </tr>
		                    @endforeach
		                </tbody>
		            </table>
		        </div>
		    </div>

		    <div class="col-md-2">
		        <table class="table mb-0">
		            <thead>
		                <tr>
		                    <th width="40px">HORARIOS</th>
		                </tr>
		            </thead>
		        </table>
		        <div class="wrapper-table">
		            <table class="table empleados">
		                
		                    @foreach ($horarios as $horario)
		                        <tr>
		                            <td width="60px">{{$horario->alias}}</td>
		                        </tr>
		                    @endforeach
		                </tbody>
		            </table>
		        </div>
		    </div>

		</div>

	</div>
</div>

@include('includes.footer')
