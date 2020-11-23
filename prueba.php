<?php
// Save The Keys In Your Configuration File
define('_VALID', TRUE);
include("functions/Configuration.php");
include("functions/Database.php");
include('functions/Route.php');
include('functions/Page.php');
include('functions/Portal.php');
include('functions/Component.php');


if(verificarCredenciales("evaluador","asd123"))
    echo "Login OK";
else
    echo "Login failed";

    echo "<br>";

    echo var_dump(obtenerUsuario("evaluador"));
