<?php
use Symfony\HttpFoundation\Request;

require_once __DIR__."/../app/AppKernel.php";
$kernel = new AppKernel("prod", false);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

//autoload-> include en dure + rapide (10 ms)