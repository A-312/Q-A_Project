<?php
define("__BASEDIR__", realpath(getcwd()."/../"));
chdir(__DIR__);
$loader = require_once __DIR__."/../vendor/autoload.php";

use Core\Kernel\Kernel;
use Core\Bundle;

class AppKernel extends Kernel {
	public function registerBundles() {
		$bundles = array(
			new Bundle\BdD\BdD(),
			new Bundle\Twig\Twig(),
			new Bundle\Routing\Router(),
			new Bundle\ResRouting\ResRouting(),
			//My Bundle
			new \src\ComingSoon\ComingSoon(),
			new \src\Forum\Forum()
		);

		if ($this->devEnvironment) {
			$bundles[] = new \src\SqlView\SqlView();
			$bundles[] = new Bundle\DebugBar\DebugBar();
		}

		return $bundles;
	}
}
