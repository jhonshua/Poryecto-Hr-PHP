function openMenu() {
    document.getElementById("navbar").style.left = "0";
    document.getElementById("navbarBack").style.left = "0";
    document.getElementById("iconOpenMenu").style.display = "none";
    document.getElementById("iconCloseMenu").style.display = "block";
    document.getElementById("iconCloseMenu").style.fontSize = "25px";
    var iconNavbar = document.querySelectorAll("img.icon-navbar");
    var intervalTime = 0.2;
    var countTime = 0;
    iconNavbar.forEach(function(icon) {
        countTime = countTime + intervalTime;
        icon.style.left = "0px";
        icon.style.transition = countTime+"s";
    });

    var intervalTimeName = 0.5;
    var countTimeName = 0;
    var nameNavbar = document.querySelectorAll("label.name-navbar");
    nameNavbar.forEach(function(titulo) {
        countTimeName = countTimeName + intervalTimeName;
        titulo.style.color = "#000";
        titulo.style.transition = countTimeName+"s";
    });
}

function closeMenu() {
    document.getElementById("navbar").style.left = "-315px";
    document.getElementById("navbarBack").style.left = "-100%";
    document.getElementById("iconOpenMenu").style.display = "block";
    document.getElementById("iconCloseMenu").style.display = "none";
    var iconNavbar = document.querySelectorAll("img.icon-navbar");
    var intervalTimeIcon = 0.2;
    var countTimeIcon = 0;
    iconNavbar.forEach(function(icon) {
        countTimeIcon = countTimeIcon + intervalTimeIcon;
        icon.style.left = "312px";
        icon.style.transition = countTimeIcon+"s";
    });

    var intervalTimeName = 0.2;
    var countTimeName = 0;
    var nameNavbar = document.querySelectorAll("label.name-navbar");
    nameNavbar.forEach(function(titulo) {
        countTimeName = countTimeName + intervalTimeName;
        titulo.style.color = "#fbba00";
        titulo.style.transition = countTimeName+"s";
    });
}

function arrowOpen(arrowIcon) {
    var arrow = document.getElementById(arrowIcon);
    var arrowStatus = arrow.getAttribute('value');
    if (arrowStatus == 0) {
        arrow.setAttribute('value', 1);
        document.getElementById(arrowIcon).style.transition = "all 0.5s";
        document.getElementById(arrowIcon).style.transform = "rotate(90deg)";
    }
    if (arrowStatus == 1) {
        arrow.setAttribute('value', 0);
        document.getElementById(arrowIcon).style.transition = "all 0.5s";
        document.getElementById(arrowIcon).style.transform = "rotate(0deg)";
    }
}



function permisosCambiarStatusSingh(usuario, permiso) {
    permiso = permiso.toLowerCase();

    let estatus = document.getElementById(permiso).checked;

    if (estatus) {
        estatus = 1;
    } else {
        estatus = 0;
    }

    $.get( "/sistema/permisos-del-usuario-actualizar/usuario/"+usuario+"/permiso/"+permiso+"/estatus/"+estatus+"/empresa/0", function( data ) {
        console.log(data);
    });
}



