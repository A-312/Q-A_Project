<?php
use Symfony\HttpFoundation\Request;

define("IS_RES_SERVER", true);

require_once __DIR__."/../app/AppKernel.php";
$kernel = new AppKernel("prod", false);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
