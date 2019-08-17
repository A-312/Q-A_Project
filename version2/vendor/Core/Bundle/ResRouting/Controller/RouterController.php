<?php
namespace Core\Bundle\ResRouting\Controller;

use Core\Kernel\Kernel;
use Core\Kernel\Controller\Controller;
use Symfony\HttpFoundation\Response;
use Core\Kernel\Exception\NotFoundHttpException;

class RouterController extends Controller {
	public function routerAction($url) {
		$kernel = Kernel::getCore();

		if (1 >= count($path = explode("/", $url))) {
			throw new NotFoundHttpException(sprintf("The '%s' url is not valid.", $url));
		}

		if (in_array($path[0], array("public", "res"))) {
			$res = Kernel::getCore()->getConfig()["res"];
			$path = realpath(__BASEDIR__."/www/".implode("/", $path));
		} else {
			if (!$kernel->hasBundle($path[0])) {
				throw new NotFoundHttpException(sprintf("Bundle '%s' does not exist in the '%s' url.", $path[0], $url)); 
			}
			$path = realpath($kernel->useBundle(array_shift($path))->getPath()."/Resources/public/".implode("/", $path));
		}

		if ($path) {
			$response = new Response();
			$mime = include "mime.types.php";
			$ext = pathinfo($path, PATHINFO_EXTENSION);
			if ($ext == "woff") $response->headers->set("Accept-Ranges", "bytes");
			//$response->headers->set("Access-Control-Allow-Origin", "*");
			$response->headers->set("Content-Type", isset($mime[$ext]) ? $mime[$ext] : "");
			$response->setContent($content = file_get_contents($path));

			$dir = dirname(__BASEDIR__."/res/cache/$url");
			if (!is_dir($dir)) { mkdir($dir, 0777, true); }
			file_put_contents(__BASEDIR__."/res/cache/$url", $content);

			return $response;
		} else {
			throw new NotFoundHttpException(sprintf("The '%s' ressource does not exist in the '%s' url.", $path, $url));
		}
	}
}