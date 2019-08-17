<?php
namespace src\Forum;

use Core\Kernel\Bundle\Bundle;
use Core\Kernel\Kernel;
use src\Forum\Controller\CacheController;
use src\Forum\Type\Membre;

class Forum extends Bundle {
	private $membreActuel;

	public function onKernelRequest() {
		$kernel = Kernel::getCore();
		$request = $kernel->getRequest();

		$this->class = $this;

		$this->membreActuel = ($request->getSession() && $request->getSession()->has("id")) ? (new CacheController())->membre($request->getSession()->get("id")) : null;
	}

	public function getMembreActuel() {
		return $this->membreActuel;
	}
}