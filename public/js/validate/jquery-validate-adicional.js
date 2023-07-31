$(document).ready(function() {
    $.extend(jQuery.validator.messages, {
        required: "Campo requerido",
        remote: "Please fix this field.",
        email: "Ingrese un email valido",
        url: "Ingrese una url valida",
        date: " Ingrese una fecha valida.",
        dateISO: "Please enter a valid date (ISO).",
        number: "Ingresa un número valido",
        digits: "Please enter only digits.",
        creditcard: "Please enter a valid credit card number.",
        equalTo: "Please enter the same value again.",
        accept: "Please enter a value with a valid extension.",
        maxlength: $.validator.format("Caracteres maximos: {0}"),
        minlength: $.validator.format("Caracteres minimos: {0}"),
        rangelength: $.validator.format("Please enter a value between {0} and {1} characters long."),
        range: $.validator.format("Please enter a value between {0} and {1}."),
        max: $.validator.format("Ingresa un valor menor o igual a {0}."),
        min: $.validator.format("Ingresa un valor mayor o igual a {0}.")
	});

});

