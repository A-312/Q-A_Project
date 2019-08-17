<?php
namespace Core\Kernel\Controller;

use Core\Kernel\Kernel;
use Core\Kernel\Exception\NotFoundHttpException;
use Symfony\HttpFoundation\Response;
use Symfony\HttpFoundation\StreamedResponse;
use Symfony\HttpFoundation\RedirectResponse;

class Controller {
	private $name;
	private $twig;
	private $bundle;

	public function generateUrl($route, $parameters = array()) {
		return $this->get("Router")->generate($route, $parameters);
	}

	public function redirect($url, $status = 302, $headers = array()) {
		return new RedirectResponse($url, $status, $headers);
	}

	private function twig() {
		if (null !== $this->twig) { return $this->twig; }
		$this->twig = $this->get("Twig");

		$path = realpath($this->getThisBundle()->getPath()."/Resources/views");
		if (is_dir($path)) {
			$twigLoader = $this->twig->getLoader();
			$twigLoader->setPaths(array_merge(array($path), $twigLoader->getPaths()));
		}
		return $this->twig;
	}

	public function render($view, array $context = array()) {
		return $this->twig()->render($view, $context);
	}

	public function stream($view, array $context = array(), StreamedResponse $response = null) {
		$templating = $this->twig();

		$callback = function () use ($templating, $view, $context) {
			$templating->stream($view, $context);
		};

		if (null === $response) {
			return new StreamedResponse($callback);
		}

		$response->setCallback($callback);

		return $response;
	}

	public function createNotFoundException($message = "Not Found", \Exception $previous = null) {
		return new NotFoundHttpException($message, $previous);
	}

	public function createForm($type, $data = null, array $options = array()) {
		//here
	}

	public function getRequest() {
		return Kernel::getCore()->getRequest();
	}

	public function get($name) {
		return Kernel::getCore()->getBundle($name);
	}

	public function getThisBundle() {
		if (null !== $this->bundle) { return $this->bundle; }

		$class = get_class($this);
		foreach (Kernel::getCore()->getBundles() as $b) {
			if (strpos($class, $b->getNamespace()) === 0) {
				return $this->bundle = $b;
			}
		}

		throw new \LogicException(sprintf("Bundle of controller '%s' does not exist or it is not enabled. Maybe you forgot to register bundle.", $class));
	}

	final public function getName() {
		if (null !== $this->name) {
			return $this->name;
		}

		$name = get_class($this);
		$pos = strrpos($name, "\\");

		return $this->name = false === $pos ? $name : substr($name, $pos + 1);
	}
}