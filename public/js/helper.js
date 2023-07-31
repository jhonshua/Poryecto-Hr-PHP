const cambiarIcono = param =>{

    document.getElementById(`file${param}`).onchange =()=> {
        let preview = document.getElementById(`imgicon${param}`);   
        let file = document.getElementById(`file${param}`).files[0];
        let imgdefault=urlImg;
        const  reader  = new FileReader();
        reader.onloadend =()=>{
            preview.src = reader.result;
        }
        if(file){
           
            if(!(/\.(png|svg)$/i).test(document.getElementById(`file${param}`).files[0].name)){
                preview.src =imgdefault;
                document.getElementById(`file${param}`).value='';
                alertify.error('No es un icono valido, intentalo nuevamente.!!');
            }else{
                reader.readAsDataURL(file);
            }
        }else{  
            preview.src =imgdefault;
        }
    }
};

const eliminarIcono = index =>{
    
    $("#rowsicons"+index).remove();
    count_params_icons--;

    let nicon = document.getElementsByClassName("nicon");
    let iconfile = document.getElementsByClassName("iconfile");
    let imgicon =document.getElementsByClassName("imgicon"); 

    let nitem=4;
    let nfile=4;
    let nimg=4;
    for(let x=0; x < nicon.length; x++ ){

        nicon[x].setAttribute('value',nitem);
        nitem++;
    } 
    
    for(let i=0; i < iconfile.length; i++ ){

        iconfile[i].setAttribute('id',`file${nfile}`);
        iconfile[i].setAttribute('onclick',`changeIcon(${nfile});`);
        nfile++;
    }
    for(let y=0; y < imgicon.length; y++ ){

        imgicon[y].setAttribute('id',`imgicon${nimg}`);
        nimg++;
    } 
}

const soloNumeros = e =>{
    
    let keynum = window.event ? window.event.keyCode : e.which;
    return keynum === 8 ? true : /\d/.test(String.fromCharCode(keynum));
}

const checkFecha= () =>{

    let valchecked = document.getElementById('valchecked');

    if(document.getElementById('checkfecha').checked){ 
        
        valchecked.value=1;
        $("#div_fecha").removeClass('d-none');
    
    }else{ 
        
        valchecked.value=0; // Si es 1 = lleva fecha , si es 0= no lleva fecha
        $("#div_fecha").addClass('d-none'); 
    }
}
