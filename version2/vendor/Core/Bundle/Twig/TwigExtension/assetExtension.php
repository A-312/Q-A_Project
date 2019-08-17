<?php
namespace Core\Bundle\Twig\TwigExtension;

use Core\Kernel\Kernel;

class assetExtension extends \Twig_Extension {
	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction("asset", array($this, "asset"))
		);
	}

	public function asset($url) {
		$kernel = Kernel::getCore();
		$res = $kernel->getConfig()["res"];
		$router = ($kernel->isDevEnvironment()) ? "/res_dev.php" : "";
		return $res["scheme"]."://".$res["host"].$router.(($url[0]!="/")?"/":"").$url;
	}

	public function getName() {
		return "assetExtension";
	}
}
