<?php
namespace Core\Kernel;

use Symfony\Yaml\Yaml;
use Symfony\HttpFoundation\Request;
use Symfony\HttpFoundation\Response;
use Symfony\HttpFoundation\Session\Session;
use Symfony\HttpFoundation\Session\Storage;
use Symfony\Routing\Exception\MethodNotAllowedException;
use Symfony\Routing\Exception\ResourceNotFoundException;

abstract class Kernel {
	protected $environment;
	protected $devEnvironment;
	protected $debug;
	protected $booted;
	protected $config;
	protected $configPDO;
	protected $bundles;
	protected $request;
	protected $response;

	protected $resolver;

	protected static $kernel = null;

	const VERSION		 = "1.0.0";
	const VERSION_ID	  = "10000";
	const MAJOR_VERSION   = "1";
	const MINOR_VERSION   = "0";
	const RELEASE_VERSION = "0";
	const EXTRA_VERSION   = "";

	const CACHE_CONFIG_DIR = "../app/cache/config/";

	public function __construct($environment, $debug) {
		$this->environment = $environment;
		$this->devEnvironment = (boolean) in_array($this->environment, array("dev", "test"));
		$this->debug = (Boolean) $debug;
		$this->booted = false;
		$this->config = array();
		$this->bundles = array();
		$this->request = null;

		$this->init();

		self::$kernel = $this;
	}

	public function init() {
		if (!is_dir(kernel::CACHE_CONFIG_DIR)) {
			mkdir(kernel::CACHE_CONFIG_DIR);
		}

		$this->initializeConfig();

		set_exception_handler(array($this, "exceptionHandler"));
		set_error_handler(array($this, "errorHandler"));
	}

	public function loadYamlFile($file) {
		$hash = hash("sha256", realpath($file));
		$fullpath = kernel::CACHE_CONFIG_DIR."/".substr($hash, 0, 2)."/".substr($hash, 2, 2)."/".substr($hash, 4).".php";
		$dir = dirname($fullpath);

		if ($this->devEnvironment || (!(file_exists($fullpath) && ($input = require $fullpath) && isset($input) && is_array($input)))) {
			$Yaml = new Yaml();


			$input = $Yaml->parse($file);
			if (!is_dir($dir)) { mkdir($dir, 0777, true); }
			file_put_contents($fullpath, "<?php\r\nreturn ".var_export($input, true).";\r\n");
		}

		return $input;
	}

	public function loadYamlConfigFile($file) {
		return $this->loadYamlFile("../app/config/$file");
	}

	protected function initializeConfig() {
		$main_yml = $this->loadYamlConfigFile("config.yml");
		$env_yml = $this->loadYamlConfigFile("config_{$this->environment}.yml");

		$keys = array_merge($main_yml, $env_yml);

		foreach ($keys as $key => $value) {
			$config[$key] = array_merge(isset($main_yml[$key]) ? $main_yml[$key] : array(), isset($env_yml[$key]) ? $env_yml[$key] : array());
		}

		foreach ($config["ini_set"] as $varname => $newvalue) {
			ini_set($varname, $newvalue);
		}

		$this->config = array(
			"ini_set" => $config["ini_set"],
			"config" => $config["config"],
			"res" => $config["res"]
		);

		$this->configPDO = $config["pdo"];
	}

	public function getResolver() {
		if (null === $this->resolver) {
			$this->resolver = new Controller\ControllerResolver();
		}
		return $this->resolver;
	}

	public function getConfig() {
		return $this->config;
	}

	public function getConfigPDO() {
		return $this->configPDO;
	}

	public function listConfig() {
		return call_user_func_array("array_merge", $this->config);
	}

	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		$this->exceptionHandler(new \ErrorException($errstr, $errno, 1, $errfile, $errline));
	}

	public function exceptionHandler($e) {
		$m = get_class($e).": {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}";
		if ($e->getTraceAsString() != "") {
			$m .= "\nStack trace:";
			preg_match_all("#\#[0-9] (.*)\n?#", $e->getTraceAsString(), $match);

			foreach ($match[1] as $i => $l) {
				$i++; $m .= "\n  $i. $l";
			}
		}
		if (ini_get("log_errors")) {
			foreach (explode("\n", $m) as $line) {
				error_log("PHP ".$line);
			}
		}

		if (ini_get("display_errors")) {
			echo nl2br($m);
		}

		if ($this->devEnvironment) {
			if (!$e instanceof \PDOException) {
				$this->getBundle("DebugBar")["exceptions"]->addException($e);
			}
			$this->useBundle("DebugBar")->disrupt();
		}
	}

	public function boot() {
		if (true === $this->booted) {
			return;
		}

		$this->initializeBundles();

		$dev = $this->devEnvironment;
		foreach ($this->getBundles() as $bundle) {
			$bundle->boot($dev);
		}

		$this->booted = true;

		foreach ($this->getBundles() as $bundle) {
			$bundle->build();
		}
	}

	private function handleException(\Exception $e, $request) {
		$response = ($this->response instanceof Response) ? $this->response : new Response();

		if ($response->headers->has('X-Status-Code')) {
			$response->setStatusCode($response->headers->get('X-Status-Code'));

			$response->headers->remove('X-Status-Code');
		} elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
			if ($e instanceof Exception\HttpExceptionInterface) {
				$response->setStatusCode($e->getStatusCode());
				$response->headers->add($e->getHeaders());
			} else {
				$response->setStatusCode(500);
			}
		}

		if (!$this->hasResponse()) {
			$statusCode = $response->getStatusCode(); $file = "erreur/".$statusCode.".html.twig";
			$file = file_exists("../app/ressource/$file") ? $file : "erreur/xxx.html.twig";

			$twig_array = array("erreur" => $statusCode, "message" => Response::$statusTexts[$statusCode]);
			$response->setContent($this->getBundle("Twig")->render($file, $twig_array));
		} else if (!$this->response instanceof Response) {
			$response->setContent($this->response);
		}

		return $this->filterResponse($response, $request);
	}

	public function handle(Request $request) {
		if (false === $this->booted) {
			$this->boot();
		}

		try {
			return $this->handleRaw($request);
		} catch (\Exception $e) {
			if ($this->devEnvironment && !$e instanceof \PDOException) {
				$this->getBundle("DebugBar")["exceptions"]->addException($e);
			}
			return $this->handleException($e, $request);
		}
	}

	private function handleRaw(Request $request) {
		$this->request = $request;

		// request
		$this->onKernelRequest();

		// load controller
		if (false === $controller = $this->getResolver()->getController($request)) {
			throw new Exception\NotFoundHttpException(sprintf("Unable to find the controller for path '%s'. Maybe you forgot to add the matching route in your routing configuration?", $request->getPathInfo()));
		}

		// controller arguments
		$arguments = $this->getResolver()->getArguments($request, $controller);

		// call controller
		$response = call_user_func_array($controller, $arguments);

		// view
		if (!$response instanceof Response) {
			if (!$response instanceof Response) {
				$msg = sprintf("The controller must return a response (%s given).", $this->varToString($response));

				if (null === $response) {
					$msg .= " Did you forget to add a return statement somewhere in your controller?";
				}
				throw new \LogicException($msg);
			}
		}

		return $this->filterResponse($response, $request);
	}

	private function filterResponse(Response $response, Request $request) {
		$this->response = $response;

		$this->onKernelFinishRequest();
		$response->prepare($request);
		return $response;
	}

	public function onKernelRequest() {
		if (!defined("IS_RES_SERVER")) {
			$PdoSessionHandler = new Storage\Handler\PdoSessionHandler($this->getBundle("BdD")->getPDO(), array("db_table" => "session"));
			$this->request->setSession(new Session(new Storage\NativeSessionStorage(array(), $PdoSessionHandler)));
			$this->request->getSession()->start();
		}

		$this->getRequest($this->request);

		foreach ($this->getBundles() as $bundle) {
			$bundle->onKernelRequest();
		}
	}

	public function onKernelFinishRequest() {
		foreach ($this->getBundles() as $bundle) {
			$bundle->onKernelFinishRequest();
		}
	}

	public function terminate(Request $request, Response $response) {
		if (false === $this->booted) {
			return;
		}

	}

	public function setResponse($response) {
		return $this->response = $response;
	}

	public function getResponse() {
		return $this->response;
	}

	public function hasResponse() {
		return (null !== $this->response);
	}

	public function getRequest(Request $request = null) {
		if (null === $request || $request->attributes->has('_controller')) {
			return $this->request;
		}

		try {
			$router = $this->getBundle("Router");
			$router->load($request, $this->config["config"]["routing_yml"]);

			$parameters = $router->matchRequest($request);
			if (is_array($parameters)) {
				$request->attributes->add($parameters);
			}
			unset($parameters["_route"]);
			unset($parameters["_controller"]);
			$request->attributes->set("_route_params", $parameters);
		} catch (ResourceNotFoundException $e) {
			$message = sprintf("No route found for '%s %s'", $request->getMethod(), $request->getPathInfo());
			if ($referer = $request->headers->get("referer")) { $message .= sprintf(" (from '%s')", $referer); }

			throw new Exception\NotFoundHttpException($message, $e);
		} catch (MethodNotAllowedException $e) {
			$message = sprintf("No route found for '%s %s': Method Not Allowed (Allow: %s)", $request->getMethod(), $request->getPathInfo(), implode(", ", $e->getAllowedMethods()));

			throw new Exception\MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
		}
	}

	abstract public function registerBundles();

	protected function initializeBundles() {
		foreach ($this->registerBundles() as $bundle) {
			$name = $bundle->getName();
			if (isset($this->bundles[$name])) {
				throw new \LogicException(sprintf("Trying to register two bundles with the same name '%s'", $name));
			}
			$this->bundles[$name] = $bundle;
		}
	}

	public function useBundle($name) {
		if (!isset($this->bundles[$name])) {
			throw new \LogicException(sprintf("Bundle '%s' does not exist or it is not enabled. Maybe you forgot to add it in the registerBundles() method of your %s.php file?", $name, get_class($this)));
		}

		return $this->bundles[$name];
	}

	public function hasBundle($name) {
		return isset($this->bundles[$name]);
	}

	public function getBundle($name) {
		return $this->useBundle($name)->getClass();
	}

	public function getBundles() {
		return $this->bundles;
	}

	public static function getCore() {
		return self::$kernel;
	}
	
	public function getEnvironment() {
		return $this->environment;
	}

	public function isDevEnvironment() {
		return $this->devEnvironment;
	}

	public function getMemoryUsage() {
		$size = memory_get_usage(true);
		$unit = array("B","KB","MB","GB","TB","PB");
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2)." ".$unit[$i];
	}

	public function isDebug() {
		return $this->debug;
	}

	private function varToString($var) {
		if (is_object($var)) {
			return sprintf('Object(%s)', get_class($var));
		}

		if (is_array($var)) {
			$a = array();
			foreach ($var as $k => $v) {
				$a[] = sprintf('%s => %s', $k, $this->varToString($v));
			}

			return sprintf("Array(%s)", implode(', ', $a));
		}

		if (is_resource($var)) {
			return sprintf('Resource(%s)', get_resource_type($var));
		}

		if (null === $var) {
			return 'null';
		}

		if (false === $var) {
			return 'false';
		}

		if (true === $var) {
			return 'true';
		}

		return (string) $var;
	}
}
