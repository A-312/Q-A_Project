<?php
namespace Core\Bundle\Twig;

use Core\Kernel\Kernel;
use Core\Kernel\Bundle\Bundle;
use Core\Bundle\Twig\TwigExtension;

class Twig extends Bundle {
	public function boot($dev = false) {
		$loader = new \Twig_Loader_Filesystem("../app/views");
		$loader->addPath("../app/views", "res");
		$this->class = new \Twig_Environment($loader, array(
			"debug" => Kernel::getCore()->isDebug(),
			"cache" => "../app/cache/twig"
		));

		if ($dev) { return $this->bootdev(); }
	}

	public function bootdev() {
		$this->class = new \DebugBar\Bridge\Twig\TraceableTwigEnvironment($this->class);
	}

	public function build() {
		$this->class->addExtension(new TwigExtension\pathExtension());
		$this->class->addExtension(new TwigExtension\assetExtension());
	}
}
