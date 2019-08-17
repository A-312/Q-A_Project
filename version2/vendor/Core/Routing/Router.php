<?php

namespace Core\Routing;

use Symfony\HttpFoundation\Request;
use Symfony\Routing\RouterInterface;
use Symfony\Routing\Matcher\RequestMatcherInterface;
use Symfony\Routing\RouteCollection;
use Symfony\Routing\RequestContext;
use Symfony\Routing\Matcher\UrlMatcher;
use Symfony\Routing\Generator\UrlGenerator;
use Core\Routing\YamlFileLoader;

class Router implements RouterInterface, RequestMatcherInterface {
	protected $fileLoader;
	protected $context = null;

	protected $matcher = null;
	protected $generator = null;
	protected $collection = null;

	public function load(Request $request, $fileLoader) {
		$this->context = new RequestContext();
		$this->context->fromRequest($request);
		$this->fileLoader = $fileLoader;
	}

	public function getRouteCollection() {
		if (null === $this->collection) {
			$this->collection = new RouteCollection();
			$this->collection->addCollection((new YamlFileLoader())->load($this->fileLoader));
		}

		return $this->collection;
	}

	public function setContext(RequestContext $context) {
		$this->context = $context;

		if (null !== $this->matcher) {
			$this->getMatcher()->setContext($context);
		}
		if (null !== $this->generator) {
			$this->getGenerator()->setContext($context);
		}
	}

	public function getContext() {
		return $this->context;
	}

	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH) {
		return $this->getGenerator()->generate($name, $parameters, $referenceType);
	}

	public function match($pathinfo) {
		return $this->getMatcher()->match($pathinfo);
	}

	public function matchRequest(Request $request) {
		$matcher = $this->getMatcher();
		if (!$matcher instanceof RequestMatcherInterface) {
			return $matcher->match($request->getPathInfo());
		}

		return $matcher->matchRequest($request);
	}

	public function getMatcher() {
		if (null !== $this->matcher) {
			return $this->matcher;
		}

		return $this->matcher = new UrlMatcher($this->getRouteCollection(), $this->context);
	}

	public function getGenerator() {
		if (null !== $this->generator) {
			return $this->generator;
		}

		return $this->generator = new UrlGenerator($this->getRouteCollection(), $this->context);
	}
}
