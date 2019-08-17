<?php
namespace Core\Routing;

use Core\Kernel\Kernel;
use Core\Bundle\DebugBar\DataCollector\UrlMatcher\TraceableUrlMatcher;
use Core\Bundle\DebugBar\DataCollector\UrlMatcher\UrlMatcherCollector;

class TraceableRouter extends Router {
	protected $fileLoader;

	public function getMatcher() {
		if (null === $this->matcher) {
			$this->matcher = new TraceableUrlMatcher($this->getRouteCollection(), $this->context);
			Kernel::getCore()->getBundle("DebugBar")->addCollector(new UrlMatcherCollector($this->matcher));
		}

		return parent::getMatcher();
	}
}