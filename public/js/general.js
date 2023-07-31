$(function(){

    // Abrir submenus
    $('.MenuGen').click(function(){
        submenu = $(this).data('submenu');
        $('#' + submenu).toggle('slow');
    });

    // Campo fechas Jquery UI
    $( ".datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true
    });
    $( ".datepicker" ).datepicker( "option", "dateFormat", 'yy-mm-dd' );

    // Tooltips
    $('.tooltip_').tooltip();
});
