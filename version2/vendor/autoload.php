<?php
require_once __DIR__."/Core/ClassLoader/ClassLoader.php";

use Core\ClassLoader\ClassLoader;

class AutoloaderInit {
	private static $loader;

	public static function getLoader() {
		if (null !== self::$loader) {
			return self::$loader;
		}

		self::$loader = $loader = new ClassLoader();

		$vendorDir = __DIR__;

		$map = array(
			"Psr\\Log\\" => array($vendorDir),
			"DebugBar" => array($vendorDir),
			"BdD" => array($vendorDir),
			"Core" => array($vendorDir),
			"Symfony" =>  array($vendorDir),
			"Twig" => array($vendorDir),
			"src" => array(dirname($vendorDir))
		);
		foreach ($map as $namespace => $path) {
			$loader->set($namespace, $path);
		}

		$loader->register(true);

		return $loader;
	}
}

return AutoloaderInit::getLoader();