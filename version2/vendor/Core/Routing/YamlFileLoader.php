<?php
namespace Core\Routing;

use Symfony\Routing\RouteCollection;
use Symfony\Routing\Route;
use Core\Kernel\Kernel;

class YamlFileLoader {
	private static $availableKeys = array(
		"resource", "type", "prefix", "pattern", "path", "host", "schemes", "methods", "defaults", "requirements", "options", "condition"
	);

	public function load($path) {
		$config = Kernel::getCore()->loadYamlFile($path);

		$collection = new RouteCollection();

		if (null === $config) {
			return $collection;
		}

		if (!is_array($config)) {
			throw new \InvalidArgumentException(sprintf("The file '%s' must contain a YAML array.", $path));
		}

		foreach ($config as $name => $config) {
			if (isset($config["pattern"])) {
				if (isset($config["path"])) {
					throw new \InvalidArgumentException(sprintf("The file '%s' cannot define both a 'path' and a 'pattern' attribute. Use only 'path'.", $path));
				}

				$config["path"] = $config["pattern"];
				unset($config["pattern"]);
			}

			$this->validate($config, $name, $path);

			if (isset($config["resource"])) {
				$this->parseImport($collection, $config, $path);
			} else {
				$this->parseRoute($collection, $name, $config, $path);
			}
		}

		return $collection;
	}

	protected function parseRoute(RouteCollection $collection, $name, array $config, $path)	{
		$defaults = isset($config["defaults"]) ? $config["defaults"] : array();
		$requirements = isset($config["requirements"]) ? $config["requirements"] : array();
		$options = isset($config["options"]) ? $config["options"] : array();
		$host = isset($config["host"]) ? $config["host"] : "";
		$schemes = isset($config["schemes"]) ? $config["schemes"] : array();
		$methods = isset($config["methods"]) ? $config["methods"] : array();
		$condition = isset($config["condition"]) ? $config["condition"] : null;

		$route = new Route($config["path"], $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

		$collection->add($name, $route);
	}

	protected function parseImport(RouteCollection $collection, array $config, $path) {
		$type = isset($config["type"]) ? $config["type"] : null;
		$prefix = isset($config["prefix"]) ? $config["prefix"] : "";
		$defaults = isset($config["defaults"]) ? $config["defaults"] : array();
		$requirements = isset($config["requirements"]) ? $config["requirements"] : array();
		$options = isset($config["options"]) ? $config["options"] : array();
		$host = isset($config["host"]) ? $config["host"] : null;
		$schemes = isset($config["schemes"]) ? $config["schemes"] : null;
		$methods = isset($config["methods"]) ? $config["methods"] : null;

		$subCollection = $this->load(dirname($path)."/".$config["resource"]);

		$subCollection->addPrefix($prefix);
		if (null !== $host) {
			$subCollection->setHost($host);
		}
		if (null !== $schemes) {
			$subCollection->setSchemes($schemes);
		}
		if (null !== $methods) {
			$subCollection->setMethods($methods);
		}
		$subCollection->addDefaults($defaults);
		$subCollection->addRequirements($requirements);
		$subCollection->addOptions($options);

		$collection->addCollection($subCollection);
	}

	protected function validate($config, $name, $path) {
		if (!is_array($config)) {
			throw new \InvalidArgumentException(sprintf("The definition of '%s' in '%s' must be a YAML array.", $name, $path));
		}
		if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
			throw new \InvalidArgumentException(sprintf(
				"The routing file '%s' contains unsupported keys for '%s': '%s'. Expected one of: '%s'.",
				$path, $name, implode("', '", $extraKeys), implode("', '", self::$availableKeys)
			));
		}
		if (isset($config["resource"]) && isset($config["path"])) {
			throw new \InvalidArgumentException(sprintf(
				"The routing file '%s' must not specify both the 'resource' key and the 'path' key for '%s'. Choose between an import and a route definition.",
				$path, $name
			));
		}
		if (!isset($config["resource"]) && isset($config["type"])) {
			throw new \InvalidArgumentException(sprintf(
				"The 'type' key for the route definition '%s' in '%s' is unsupported. It is only available for imports in combination with the 'resource' key.",
				$name, $path
			));
		}
		if (!isset($config["resource"]) && !isset($config["path"])) {
			throw new \InvalidArgumentException(sprintf(
				"You must define a 'path' for the route '%s' in file '%s'.",
				$name, $path
			));
		}
	}
}
