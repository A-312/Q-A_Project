<?php
namespace Core\Kernel\Controller;

use Core\Kernel\Kernel;
use Psr\Log\LoggerInterface;
use Symfony\HttpFoundation\Request;

class ControllerResolver {
	private $logger;

	public function __construct(LoggerInterface $logger = null) {
		$this->logger = $logger;
	}

	public function getController(Request $request) {
		if (!$controller = $request->attributes->get("_controller")) {
			if (null !== $this->logger) {
				$this->logger->warning("Unable to look for the controller as the '_controller' parameter is missing");
			}

			return false;
		}

		if (is_array($controller) || (is_object($controller) && method_exists($controller, "__invoke"))) {
			return $controller;
		}

		if (false === strpos($controller, ":")) {
			if (method_exists($controller, "__invoke")) {
				return new $controller;
			} elseif (function_exists($controller)) {
				return $controller;
			}
		}

		$callable = $this->createController($controller);

		if (!is_callable($callable)) {
			throw new \InvalidArgumentException(sprintf("The controller for URI '%s' is not callable.", $request->getPathInfo()));
		}

		return $callable;
	}

	public function getArguments(Request $request, $controller) {
		if (is_array($controller)) {
			$r = new \ReflectionMethod($controller[0], $controller[1]);
		} elseif (is_object($controller) && !$controller instanceof \Closure) {
			$r = new \ReflectionObject($controller);
			$r = $r->getMethod("__invoke");
		} else {
			$r = new \ReflectionFunction($controller);
		}

		return $this->doGetArguments($request, $controller, $r->getParameters());
	}

	protected function doGetArguments(Request $request, $controller, array $parameters) {
		$attributes = $request->attributes->all();
		$arguments = array();
		foreach ($parameters as $param) {
			if (array_key_exists($param->name, $attributes)) {
				$arguments[] = $attributes[$param->name];
			} elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
				$arguments[] = $request;
			} elseif ($param->isDefaultValueAvailable()) {
				$arguments[] = $param->getDefaultValue();
			} else {
				if (is_array($controller)) {
					$repr = sprintf("%s::%s()", get_class($controller[0]), $controller[1]);
				} elseif (is_object($controller)) {
					$repr = get_class($controller);
				} else {
					$repr = $controller;
				}

				throw new \RuntimeException(sprintf("Controller '%s' requires that you provide a value for the '$%s' argument (because there is no default value or because there is a non optional argument after this one).", $repr, $param->name));
			}
		}

		return $arguments;
	}

	public function parse($controller) {
		if (3 != count($parts = explode(":", $controller))) {
			throw new \InvalidArgumentException(sprintf("The '%s' controller is not a valid 'a:b:c' controller string.", $controller));
		}

		list($bundle, $controller, $action) = $parts;
		$controller = str_replace("/", "\\", $controller);
		$bundles = array();

		foreach (Kernel::getCore()->useBundle($bundle)->getMap() as $b) {
			$try = $b->getNamespace()."\\Controller\\".$controller."Controller";
			if (class_exists($try)) {
				return $try."::".$action."Action";
			}

			$bundles[] = $b->getName();
			$msg = sprintf("Unable to find controller '%s:%s' - class '%s' does not exist.", $bundle, $controller, $try);
		}

		if (count($bundles) > 1) {
			$msg = sprintf("Unable to find controller '%s:%s' in bundles %s.", $bundle, $controller, implode(", ", $bundles));
		}

		throw new \InvalidArgumentException($msg);
	}

	protected function createController($controller) {
		if (false === strpos($controller, "::")) {
			$count = substr_count($controller, ":");
			if (2 == $count) {
				$controller = $this->parse($controller);
			} elseif (1 == $count) {
				list($service, $method) = explode(":", $controller, 2);

				return array(Kernel::getCore()->getBundle($service), $method);
			} else {
				throw new \LogicException(sprintf("Unable to parse the controller name '%s'.", $controller));
			}
		}

		list($class, $method) = explode("::", $controller, 2);

		if (!class_exists($class)) {
			throw new \InvalidArgumentException(sprintf("Class '%s' does not exist.", $class));
		}

		return array(new $class(), $method);
	}
}
