@extends('layouts.empleado')
@section('tituloPagina', "Cuestionario Norma 035")
@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        @include('norma.cuestionarios.informacion_personal_form')
    </div>
</div>
@endsection


@push('css')
<style>
    .preguntaCuestionario-4{
        background-color:#F0C018;
        color:#000;
        width:100%;
        padding:2%;
        font-weight:bold;
        margin-top:1%;
    }
    .preguntaCuestionario-12{
        background-color:#F0C018;
        color:#000;
        width:100%;
        padding:1%;
        font-weight:bold;
        margin-top:1%;
    }
    .invalido{
        color:#EE4A30;
        
    }   
</style>
@endpush


@push('scripts')
<script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/validate/jquery-validate-adicional.js') }}"></script>
<script>
//var form = $("#example-advanced-form").show();
$("#informacion_personal").validate({
  errorClass: "invalido",
  errorElement: "span",
  errorPlacement: function(error, element) {
    error.appendTo( $('label[for='+element.attr("name")+']') );
  },
  rules: {
    nombre: {required: true},
    paterno: {required: true},
    materno: {required: true},
    sexo:"required",
    edad:"required",
    estado_civil:"required",
    nivel_estudios:"required",
    tipo_puesto:"required",
    tipo_contratacion:"required",
    tipo_personal:"required",
    tipo_jornada:"required",
    rotacion_turnos:"required",
    experiencia_puesto_actual:"required",
    experiencia_laboral:"required"
  },
  submitHandler: function(form) {
     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      $('#btn-guardar').html('Guardando..').attr("disabled","disabled");
      var url = "{{route('empleado.norma.confirmarInformacionPersonal')}}";
      $.ajax({
        url: url,
        type: "POST",
        data: $('#informacion_personal').serialize(),
        success: function( response ) {
            if(response.ok == 1) {
                $('#btn-guardar').html('Guardado').attr("disabled",true);
                    alertify.alert('Información personal', 'Acaba de finalizar el llenado de su información personal. Favor de continuar con su cuestionario.', function(){ window.location="{{ route('empleado.norma') }}"; });
            }else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
            }
        }
      });
    }
});

</script>
@endpush