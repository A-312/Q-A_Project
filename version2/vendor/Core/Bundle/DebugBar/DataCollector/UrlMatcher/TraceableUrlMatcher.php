<?php
namespace Core\Bundle\DebugBar\DataCollector\UrlMatcher;

use Symfony\Routing\Route;
use Symfony\Routing\RouteCollection;
use Symfony\Routing\Matcher\UrlMatcher;

// see Symfony\Routing\Matcher\TraceableUrlMatcher
class TraceableUrlMatcher extends UrlMatcher {
	const ROUTE_DOES_NOT_MATCH = 0;
	const ROUTE_ALMOST_MATCHES = 1;
	const ROUTE_MATCHES		= 2;

	protected $traces;

	public function getTraces() {
		return $this->traces;
	}

	protected function matchCollection($pathinfo, RouteCollection $routes) {
		foreach ($routes as $name => $route) {
			$compiledRoute = $route->compile();
			$debut = microtime(true);

			if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
				// does it match without any requirements?
				$r = new Route($route->getPath(), $route->getDefaults(), array(), $route->getOptions());
				$cr = $r->compile();
				if (!preg_match($cr->getRegex(), $pathinfo)) {
					$this->addTrace(sprintf('Path "%s" does not match', $route->getPath()), self::ROUTE_DOES_NOT_MATCH, $name, $route, $debut);

					continue;
				}

				foreach ($route->getRequirements() as $n => $regex) {
					$r = new Route($route->getPath(), $route->getDefaults(), array($n => $regex), $route->getOptions());
					$cr = $r->compile();

					if (in_array($n, $cr->getVariables()) && !preg_match($cr->getRegex(), $pathinfo)) {
						$this->addTrace(sprintf('Requirement for "%s" does not match (%s)', $n, $regex), self::ROUTE_ALMOST_MATCHES, $name, $route, $debut);

						continue 2;
					}
				}

				continue;
			}

			// check host requirement
			$hostMatches = array();
			if ($compiledRoute->getHostRegex() && !preg_match($compiledRoute->getHostRegex(), $this->context->getHost(), $hostMatches)) {
				$this->addTrace(sprintf('Host "%s" does not match the requirement ("%s")', $this->context->getHost(), $route->getHost()), self::ROUTE_ALMOST_MATCHES, $name, $route, $debut);

				continue;
			}

			// check HTTP method requirement
			if ($req = $route->getRequirement('_method')) {
				// HEAD and GET are equivalent as per RFC
				if ('HEAD' === $method = $this->context->getMethod()) {
					$method = 'GET';
				}

				if (!in_array($method, $req = explode('|', strtoupper($req)))) {
					$this->allow = array_merge($this->allow, $req);

					$this->addTrace(sprintf('Method "%s" does not match the requirement ("%s")', $this->context->getMethod(), implode(', ', $req)), self::ROUTE_ALMOST_MATCHES, $name, $route, $debut);

					continue;
				}
			}

			// check condition
			if ($condition = $route->getCondition()) {
				if (!$this->getExpressionLanguage()->evaluate($condition, array('context' => $this->context, 'request' => $this->request))) {
					$this->addTrace(sprintf('Condition "%s" does not evaluate to "true"', $condition), self::ROUTE_ALMOST_MATCHES, $name, $route, $debut);

					continue;
				}
			}

			// check HTTP scheme requirement
			if ($scheme = $route->getRequirement('_scheme')) {
				if ($this->context->getScheme() !== $scheme) {
					$this->addTrace(sprintf('Scheme "%s" does not match the requirement ("%s"); the user will be redirected', $this->context->getScheme(), $scheme), self::ROUTE_ALMOST_MATCHES, $name, $route, $debut);

					return $this->getAttributes($route, $name, array_replace($matches, $hostMatches));
				}
			}

			$this->addTrace('Route matches!', self::ROUTE_MATCHES, $name, $route, $debut);

			return $this->getAttributes($route, $name, array_replace($matches, $hostMatches));
		}
	}

	private function addTrace($log, $level = self::ROUTE_DOES_NOT_MATCH, $name = null, $route = null, $debut = null) {
		$this->traces[] = array(
			'log'   => $log,
			'name'  => $name,
			'level' => $level,
			'path'  => null !== $route ? $route->getPath() : null,
			'time'	=> ($debut !== null) ? microtime(true)-$debut : 0
		);
	}
}
