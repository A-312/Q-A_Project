<?php
namespace src\ComingSoon;

use Core\Kernel\Kernel;
use Core\Kernel\Bundle\Bundle;
use Symfony\HttpFoundation\Response;

class ComingSoon extends Bundle {
	public function onKernelRequest() {
		if (!$this->isAuthorized()) {
			$kernel = Kernel::getCore();
			$response = (new Controller\ComingSoonController())->indexAction();
			$kernel->setResponse($response);
			if ($kernel->getRequest()->query->has("secureback")) {
				throw new \Core\Kernel\Exception\UnauthorizedHttpException("Basic realm=\"Authentification necessaire.\"", "Authentification necessaire.");
			} else {
				throw new \Core\Kernel\Exception\AccessDeniedHttpException("Authentification necessaire.");
			}
		}
	}

	public function isAuthorized() {
		$kernel = Kernel::getCore();
		$request = $kernel->getRequest();
		$controller = $kernel->getResolver()->getController($request);

		return $kernel->isDevEnvironment() || is_object($controller[0]) && ($controller[0]->getThisBundle()->getName() === "ResRouting" ||
			(	$controller[0]->getName() === "ComingSoonController"
				|| ($request->headers->get("PHP_AUTH_USER") === "{{auth_user}}"
				&&  $request->headers->get("PHP_AUTH_PW") === "{{auth_pwd}}"))
			);
	}
}