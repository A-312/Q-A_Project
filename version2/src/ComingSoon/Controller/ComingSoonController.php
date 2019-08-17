<?php
namespace src\ComingSoon\Controller;

use Core\Kernel\Kernel;
use Core\Kernel\Controller\Controller;
use Symfony\HttpFoundation\Response;

class ComingSoonController extends Controller {
	public function indexAction() {
		$response = new Response();

		//$twig_array = array();
		$response->setContent($this->render("comingsoon.html.twig"));

		return $response;
	}
}