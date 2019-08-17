<?php
namespace Core\Bundle\Twig\TwigExtension;

class pathExtension extends \Twig_Extension {
	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction("path", array($this, "path"))
		);
	}

	public function path($name, $params = array()) {
		try {
			return \Core\Kernel\Kernel::getCore()->getBundle("Router")->generate($name, $params);
		} catch (\Exception $e) {
			return (($name[0]!="/")?"/":"").$name.((count($params) != 0) ? "?".http_build_query($params) : "");
		}
	}

	public function getName() {
		return "pathExtension";
	}
}
