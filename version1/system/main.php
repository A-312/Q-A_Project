<?php
define("__PATH__", "/Q-A_Project/version1/");
define("__BASEURL__", "://127.0.0.1/version/");

function ChargerInclude($nom) {
	$class = __PATH__."system/class/class.$nom.php";
	$function = __PATH__."system/function/function.$nom.php";
	if (file_exists($class)) {
		include_once $class;
	} elseif (file_exists($function)) {
		include_once $function;
	}
}

spl_autoload_register("ChargerInclude");
setlocale(LC_ALL, "fr_FR.utf8", "fra"); 

$System = new System();
$System->debug();

$BasedeDonnee = new BdD("localhost", "sdd_old", "sdd", "{{db_password}}", function ($e) {
	global $System;
	$System->gererErreurBdD($e);
});
$BasedeDonnee->debug();

session_set_save_handler((new Session()), true);
//session_save_path(__PATH__."system/session");
session_start();

ob_start("ob_gzhandler");

$MembreActuel = (isset($_SESSION["id"])) ? new Membre($_SESSION["id"]) : null;

if ((!isset($_SERVER['PHP_AUTH_USER']) || !is_string($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != "{{auth_user}}")
 || (!isset($_SERVER['PHP_AUTH_PW'])   || !is_string($_SERVER['PHP_AUTH_PW'])   || $_SERVER['PHP_AUTH_PW'] != "{{auth_pwd}}")) {
	$bool = isset($_GET["secureback"]);
	
	!$bool || header('WWW-Authenticate: Basic realm="Authentification necessaire."');
	header('HTTP/1.0 401 Unauthorized');
	$bool || header('location:comingsoon/'); 
	exit;
}
?>