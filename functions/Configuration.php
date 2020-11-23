<?php
setlocale(LC_TIME,"es_AR");
date_default_timezone_set("America/Argentina/Cordoba");
defined("_VALID") or die("Acceso restringido");
header("content-type:text/html;charset=utf-8");

define("DB_HOST","localhost");
define("DB_USUARIO","admin_rh");
define("DB_CONTRASENA","s21rh2020");
define("DB_NOMBRE","admin_rh");
define("ADMIN_EMAIL","lucasbaigorri@gmail.com");

define('FIRSTKEY','s4AWrqKPQi9FkMzZGJJMlfwQJTGtKL6UWFtd+HpbgfA=');
define('SECONDKEY','uNFzEHyB5SD/z5LdaXaRP5M4S0mkzEL7W+kjw8YQAEMdTMDJEHK3rJqi7DrKJrceJ7ljOqMhrGRhhqX85gE6bQ==');

//NO MODIFICAR
define("ROOT",dirname(dirname(__FILE__)));
define("RELDIR", str_replace("\\","/",substr(dirname(dirname(__FILE__)),strlen($_SERVER["DOCUMENT_ROOT"]))));
define("BASE_URL", ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") ? "https" : "http"). "://" . $_SERVER["HTTP_HOST"] . RELDIR);
require_once(ROOT."/functions/api.php" );

?>