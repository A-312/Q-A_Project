<?php
if (isset($_SERVER["HTTP_CLIENT_IP"])
	|| isset($_SERVER["HTTP_X_FORWARDED_FOR"])
	|| !in_array(@$_SERVER["REMOTE_ADDR"], array("127.0.0.1", "fe80::1", "::1"))
) {
	header("HTTP/1.0 403 Forbidden");
	exit("You are not allowed to access this file. Check ".basename(__FILE__)." for more information.");
}

use Symfony\HttpFoundation\Request;

define("IS_RES_SERVER", true);

require_once __DIR__."/../app/AppKernel.php";
$kernel = new AppKernel("dev", true);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
