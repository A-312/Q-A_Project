<?php
namespace Core\Bundle\Routing;

use Core\Kernel\Bundle\Bundle;

class Router extends Bundle {
	public function boot($dev = false) {
		if ($dev) { return $this->bootdev(); }

		$this->class = new \Core\Routing\Router();
	}

	public function bootdev() {
		$this->class = new \Core\Routing\TraceableRouter();
	}
}