<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PonderadorController extends Controller
{
    public $nivelRiesgoTotal = array();
    public $nivelRiesgoCatDom = array();
    public $categoriasYdominios = array();

    public $nivelRiesgoEstatusCuestionarioII = array(
        0 => array(0,20,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
        1 => array(20,45,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
        2 => array(45,70,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
        3 => array(70,90,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
        4 => array(90,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
    );

    public $nivelRiesgoEstatusCuestionarioCatDomII = array(
        1 => array( // Ambiente de trabajo
            0 => array(0,3,"rgb(84,226,248)","Nulo o despreciable",0,"NO CANALIZAR"),
            1 => array(3,5,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(5,7,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(7,9,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(9,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        2 => array(), 3 => array(),
        4 => array( //Factores propios de la actividad
            0 => array(0,10,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(10,20,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(20,30,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(30,40,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(40,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        5 => array( //Organización del tiempo de trabajo
            0 => array(0,4,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(4,6,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(6,9,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(9,12,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(12,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        6 => array( // Liderazgo y relaciones en el trabajo
            0 => array(0,10,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(10,18,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(18,28,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(28,38,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(38,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        7 => array(),
        8 => array( //Condiciones en el ambiente de trabajo
            0 => array(0,3,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(3,5,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(5,7,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(7,9,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(9,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        9 => array( //Carga de trabajo
            0 => array(0,12,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(12,16,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(16,20,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(20,24,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(24,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        10 => array( //Falta de control sobre el trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(5,8,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(8,11,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(11,14,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(14,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        11 => array( //Jornada de trabajo
            0 => array(0,1,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(1,2,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(2,4,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(4,6,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(6,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        12 => array( //Interferencia en la relación trabajo-familia
            0 => array(0,1,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(1,2,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(2,4,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(4,6,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(6,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        13 => array( //Liderazgo
            0 => array(0,3,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(3,5,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(5,8,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(8,11,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(11,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        14 => array( //Relaciones en el trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(5,8,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(8,11,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(11,14,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(14,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        15 => array( // Violencia
            0 => array(0,7,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(7,10,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(10,13,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(13,16,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(16,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        16 => array(),17 => array()
    );



    public $nivelRiesgoEstatusCuestionarioIII = array(
            0 => array(0,50,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(50,75,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(75,99,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(99,140,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(140,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
    );

    public $nivelRiesgoEstatusCuestionarioCatDomIII = array(
        1 => array( // Ambiente de trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo o despreciable",0,"NO CANALIZAR"),
            1 => array(5,9,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(9,11,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(11,14,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(14,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),2 => array(), 3 => array(),
        4 => array( //Factores propios de la actividad
            0 => array(0,15,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(15,30,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(30,45,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(45,60,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(60,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        5 => array( //Organización del tiempo de trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(5,7,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(7,10,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(10,13,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(13,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        6 => array( // Liderazgo y relaciones en el trabajo
            0 => array(0,14,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(14,29,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(29,42,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(42,58,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(58,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        7 => array( // Entorno organizacional
            0 => array(0,10,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(10,14,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(14,18,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(18,23,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(23,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),

        8 => array( //Condiciones en el ambiente de trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(5,9,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(9,11,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(11,14,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(14,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        9 => array( //Carga de trabajo
            0 => array(0,15,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(15,21,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(21,27,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(27,37,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(37,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        10 => array( //Falta de control sobre el trabajo
            0 => array(0,11,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(11,16,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(16,21,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(21,25,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(25,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        11 => array( //Jornada de trabajo
            0 => array(0,1,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(1,2,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(2,4,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(4,6,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(6,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        12 => array( //Interferencia en la relación trabajo-familia
            0 => array(0,4,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(4,6,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(6,8,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(8,10,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(10,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        13 => array( //Liderazgo
            0 => array(0,9,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(9,12,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(12,16,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(16,20,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(20,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        14 => array( //Relaciones en el trabajo
            0 => array(0,10,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(10,13,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(13,17,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(17,21,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(21,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        15 => array( // Violencia
            0 => array(0,7,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(7,10,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(10,13,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(13,16,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(16,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        16 => array( //Reconocimiento del desempeño
            0 => array(0,6,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(6,10,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(10,14,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(14,18,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(18,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        17 => array( //Insuficiente sentido de pertenencia e, inestabilidad
            0 => array(0,4,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(4,6,"rgb(100,247,129)","Bajo",0,"NO CANALIZAR"),
            2 => array(6,8,"rgb(249,249,83)","Medio",0,"NO CANALIZAR"),
            3 => array(8,10,"rgb(243,152,54)","Alto",0,"CANALIZAR"),
            4 => array(10,1000,"rgb(249,51,27)","Muy alto",0,"CANALIZAR")
        ),
        82 => array(
            0 => array(0,5,"rgb(84,226,248)","Nulo",0,"NO CANALIZAR"),
            1 => array(5,1000,"rgb(249,51,27)","Bajo",0,"CANALIZAR"),
        )
    );

    //arreglo para el reporte de resultados
    public $categoria_dominio = array(
        //Categoria
        1 => array("Ambiente de trabajo","categoría",array()),
        4 => array("Factores propios de la actividad","categoría",array()),
        5 => array("Organización del tiempo de trabajo","categoría",array()),
        6 => array("Liderazgo y relaciones en el trabajo","categoría",array()),
        7 => array("Entorno organizacional","categoría",array()),
        // Dominio
        8 => array("Condiciones en el ambiente de trabajo","dominio",array()),
        9 => array("Carga de trabajo","dominio",array()),
        10 => array("Falta de control sobre el trabajo","dominio",array()),
        11 => array("Jornada de trabajo","dominio",array()),
        12 => array("Interferencia en la relación trabajo-famila","dominio",array()),
        13 => array("Liderazgo","dominio",array()),
        14 => array("Relaciones en el trabajo","dominio",array()),
        15 => array("Violencia","dominio",array()),
        16 => array("Reconocimiento del desempeño","dominio",array()),
        17 => array("Insuficiente sentido de pertenencia e inestabilidad","dominio",array())
    );

    

    public $categoriasYdominiosII = array(
        1 =>  "Ambiente de trabajo",
        4 => "Factores propios de la actividad",
        5 => "Organización del tiempo de trabajo",
        6 => "Liderazgo y relaciones en el trabajo",

        8 => "Condiciones en el ambiente de trabajo",
        9 => "Carga de trabajo",
        10 => "Falta de control sobre el trabajo",
        11 => "Jornada de trabajo",
        12 => "Interferencia en la relación trabajo-famila",
        13 => "Liderazgo",
        14 => "Relaciones en el trabajo",
        15 => "Violencia"
    );

    public $categoriasYdominiosIII = array(
        //Categoria
        1 => "Ambiente de trabajo",
        4 => "Factores propios de la actividad",
        5 => "Organización del tiempo de trabajo",
        6 => "Liderazgo y relaciones en el trabajo",
        7 => "Entorno organizacional",
        // Dominio
        8 => "Condiciones en el ambiente de trabajo",
        9 => "Carga de trabajo",
        10 => "Falta de control sobre el trabajo",
        11 => "Jornada de trabajo",
        12 => "Interferencia en la relación trabajo-famila",
        13 => "Liderazgo",
        14 => "Relaciones en el trabajo",
        15 => "Violencia",
        16 => "Reconocimiento del desempeño",
        17 => "Insuficiente sentido de pertenencia e inestabilidad",
    );

    public $factorRiesgoTotales = array(
        0 => array("rgb(84,226,248)","Nulo",0),
        1 => array("rgb(100,247,129)","Bajo",0),
        2 => array("rgb(249,249,83)","Medio",0),
        3 => array("rgb(243,152,54)","Alto",0),
        4 => array("rgb(249,51,27)","Muy alto",0)
    );
/*
    public $categoria_dominioIII = array(
        //Categoria
        1 => array("Ambiente de trabajo","categoría",array()),
        4 => array("Factores propios de la actividad","categoría",array()),
        5 => array("Organización del tiempo de trabajo","categoría",array()),
        6 => array("Liderazgo y relaciones en el trabajo","categoría",array()),
        7 => array("Entorno organizacional","categoría",array()),
        // Dominio
        8 => array("Condiciones en el ambiente de trabajo","dominio",array()),
        9 => array("Carga de trabajo","dominio",array()),
        10 => array("Falta de control sobre el trabajo","dominio",array()),
        11 => array("Jornada de trabajo","dominio",array()),
        12 => array("Interferencia en la relación trabajo-famila","dominio",array()),
        13 => array("Liderazgo","dominio",array()),
        14 => array("Relaciones en el trabajo","dominio",array()),
        15 => array("Violencia","dominio",array()),
        16 => array("Reconocimiento del desempeño","dominio",array()),
        17 => array("Insuficiente sentido de pertenencia e inestabilidad","dominio",array())
    );
*/
    public function __construct(){
    }


    public $clasificacionEstatus = array(
        0 => array( // Total
            0 => array(0,50,"rgb(84,226,248)","Nulo"),
            1 => array(50,75,"rgb(100,247,129)","Bajo"),
            2 => array(75,99,"rgb(249,249,83)","Medio"),
            3 => array(99,140,"rgb(243,152,54)","Alto"),
            4 => array(140,1000,"rgb(249,51,27)","Muy alto")
        ),
        1 => array( //Ambiente de trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo"),
            1 => array(5,9,"rgb(100,247,129)","Bajo"),
            2 => array(9,11,"rgb(249,249,83)","Medio"),
            3 => array(11,14,"rgb(243,152,54)","Alto"),
            4 => array(14,1000,"rgb(249,51,27)","Muy alto")
        ), 
        2 => array(//Condiciones deficientes e insalubres
            0 => array(0,50,"rgb(84,226,248)","Nulo"),
            1 => array(51,75,"rgb(100,247,129)","Bajo"),
            2 => array(76,99,"rgb(249,249,83)","Medio"),
            3 => array(100,140,"rgb(243,152,54)","Alto"),
            4 => array(141,1000,"rgb(249,51,27)","Muy alto")
        ),
        3 => array(//Trabajos peligrosos
            0 => array(0,50,"rgb(84,226,248)","Nulo"),
            1 => array(51,75,"rgb(100,247,129)","Bajo"),
            2 => array(76,99,"rgb(249,249,83)","Medio"),
            3 => array(100,140,"rgb(243,152,54)","Alto"),
            4 => array(141,1000,"rgb(249,51,27)","Muy alto")
        ),
        4 => array( //Factores propios de la actividad
            0 => array(0,15,"rgb(84,226,248)","Nulo"),
            1 => array(15,30,"rgb(100,247,129)","Bajo"),
            2 => array(30,45,"rgb(249,249,83)","Medio"),
            3 => array(45,60,"rgb(243,152,54)","Alto"),
            4 => array(60,1000,"rgb(249,51,27)","Muy alto")
        ),
        5 => array( //Organización del tiempo de trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo"),
            1 => array(5,7,"rgb(100,247,129)","Bajo"),
            2 => array(7,10,"rgb(249,249,83)","Medio"),
            3 => array(10,13,"rgb(243,152,54)","Alto"),
            4 => array(13,1000,"rgb(249,51,27)","Muy alto")
        ),
        6 => array( //Liderazgo y relaciones en el trabajo
            0 => array(0,14,"rgb(84,226,248)","Nulo"),
            1 => array(14,29,"rgb(100,247,129)","Bajo"),
            2 => array(29,42,"rgb(249,249,83)","Medio"),
            3 => array(42,58,"rgb(243,152,54)","Alto"),
            4 => array(58,1000,"rgb(249,51,27)","Muy alto")
        ),
        7 => array( //Entorno organizacional
            0 => array(0,10,"rgb(84,226,248)","Nulo"),
            1 => array(10,14,"rgb(100,247,129)","Bajo"),
            2 => array(14,18,"rgb(249,249,83)","Medio"),
            3 => array(18,23,"rgb(243,152,54)","Alto"),
            4 => array(23,1000,"rgb(249,51,27)","Muy alto")
        ),
        8 => array( //Condiciones en el ambiente de trabajo
            0 => array(0,5,"rgb(84,226,248)","Nulo"),
            1 => array(5,9,"rgb(100,247,129)","Bajo"),
            2 => array(9,11,"rgb(249,249,83)","Medio"),
            3 => array(11,14,"rgb(243,152,54)","Alto"),
            4 => array(14,1000,"rgb(249,51,27)","Muy alto")
        ),
        9 => array( //Carga de trabajo
            0 => array(0,15,"rgb(84,226,248)","Nulo"),
            1 => array(15,21,"rgb(100,247,129)","Bajo"),
            2 => array(21,27,"rgb(249,249,83)","Medio"),
            3 => array(27,37,"rgb(243,152,54)","Alto"),
            4 => array(37,1000,"rgb(249,51,27)","Muy alto")
        ),
        10 => array( //Falta de control sobre el trabajo
            0 => array(0,11,"rgb(84,226,248)","Nulo"),
            1 => array(11,16,"rgb(100,247,129)","Bajo"),
            2 => array(16,21,"rgb(249,249,83)","Medio"),
            3 => array(21,25,"rgb(243,152,54)","Alto"),
            4 => array(25,1000,"rgb(249,51,27)","Muy alto")
        ),
        11 => array( //Jornada de trabajo
            0 => array(0,1,"rgb(84,226,248)","Nulo"),
            1 => array(1,2,"rgb(100,247,129)","Bajo"),
            2 => array(2,4,"rgb(249,249,83)","Medio"),
            3 => array(4,6,"rgb(243,152,54)","Alto"),
            4 => array(6,1000,"rgb(249,51,27)","Muy alto")
        ),
        12 => array(//Interferencia en la relación trabajo-famila
            0 => array(0,4,"rgb(84,226,248)","Nulo"),
            1 => array(4,6,"rgb(100,247,129)","Bajo"),
            2 => array(6,8,"rgb(249,249,83)","Medio"),
            3 => array(8,10,"rgb(243,152,54)","Alto"),
            4 => array(10,1000,"rgb(249,51,27)","Muy alto")
        ),
        13 => array(//Liderazgo
            0 => array(0,9,"rgb(84,226,248)","Nulo"),
            1 => array(9,12,"rgb(100,247,129)","Bajo"),
            2 => array(12,16,"rgb(249,249,83)","Medio"),
            3 => array(16,20,"rgb(243,152,54)","Alto"),
            4 => array(20,1000,"rgb(249,51,27)","Muy alto")
        ),
        14 => array( //Relaciones en el trabajo
            0 => array(0,10,"rgb(84,226,248)","Nulo"),
            1 => array(10,13,"rgb(100,247,129)","Bajo"),
            2 => array(13,17,"rgb(249,249,83)","Medio"),
            3 => array(17,21,"rgb(243,152,54)","Alto"),
            4 => array(21,1000,"rgb(249,51,27)","Muy alto")
        ),
        15 => array( //Violencia
            0 => array(0,7,"rgb(84,226,248)","Nulo"),
            1 => array(7,10,"rgb(100,247,129)","Bajo"),
            2 => array(10,13,"rgb(249,249,83)","Medio"),
            3 => array(13,16,"rgb(243,152,54)","Alto"),
            4 => array(16,1000,"rgb(249,51,27)","Muy alto")
        ),
        16 => array( //Reconocimiento del desempeño
            0 => array(0,6,"rgb(84,226,248)","Nulo"),
            1 => array(6,10,"rgb(100,247,129)","Bajo"),
            2 => array(10,14,"rgb(249,249,83)","Medio"),
            3 => array(14,18,"rgb(243,152,54)","Alto"),
            4 => array(18,1000,"rgb(249,51,27)","Muy alto")
        ),
        17 => array( //Insuficiente sentido de pertenencia e, inestabilidad
            0 => array(0,4,"rgb(84,226,248)","Nulo"),
            1 => array(4,6,"rgb(100,247,129)","Bajo"),
            2 => array(6,8,"rgb(249,249,83)","Medio"),
            3 => array(8,10,"rgb(243,152,54)","Alto"),
            4 => array(10,1000,"rgb(249,51,27)","Muy alto")
        )
    );





    public function seleccionarPonderadores($cuestionario){
        if($cuestionario == 2){
            $this->nivelRiesgoTotal = $this->nivelRiesgoEstatusCuestionarioII;
            $this->nivelRiesgoCatDom = $this->nivelRiesgoEstatusCuestionarioCatDomII;
            $this->categoriasYdominios = $this->categoriasYdominiosII;
        }else if($cuestionario == 3){
            $this->nivelRiesgoTotal = $this->nivelRiesgoEstatusCuestionarioIII;
            $this->nivelRiesgoCatDom = $this->nivelRiesgoEstatusCuestionarioCatDomIII;
            $this->categoriasYdominios = $this->categoriasYdominiosIII;
        }
    }

}
