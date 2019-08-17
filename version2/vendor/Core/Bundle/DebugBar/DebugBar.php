<?php
namespace Core\Bundle\DebugBar;

use Core\Kernel\Kernel;
use Core\Kernel\Bundle\Bundle;
use Core\Bundle\DebugBar\DataCollector;
use DebugBar\DataCollector as StandardDebugBar;

class DebugBar extends Bundle {
	public function bootdev() {
		$this->class = new \DebugBar\DebugBar();

		Kernel::getCore()->getBundle("BdD")->setDebugBar($this->class);
	}

	public function build() {
		$this->class->addCollector(new StandardDebugBar\PhpInfoCollector());
		$this->class->addCollector(new StandardDebugBar\MessagesCollector());
		$this->class->addCollector(new DataCollector\GlobalsDataCollector());
		$this->class->addCollector(new DataCollector\RequestDataCollector());
		$this->class->addCollector(new StandardDebugBar\TimeDataCollector());
		$this->class->addCollector(new StandardDebugBar\MemoryCollector());
		$this->class->addCollector(new StandardDebugBar\ExceptionsCollector());
		$this->class->addCollector(new DataCollector\HTTPStatusCodeCollector());
		$this->class->addCollector(new DataCollector\KernelVersionCollector());
		$this->class->addCollector(new StandardDebugBar\ConfigCollector(Kernel::getCore()->listConfig()));
		$this->class->addCollector(new \DebugBar\Bridge\Twig\TwigCollector(Kernel::getCore()->getBundle("Twig")));
		$this->class->addCollector(new StandardDebugBar\PDO\PDOCollector(Kernel::getCore()->getBundle("BdD")->getPDO()));
	}

	public function onKernelFinishRequest() {
		$kernel = Kernel::getCore();
		$request = $kernel->getRequest();

		if (explode(":", $request->attributes->get("_controller"))[0] == "ResRouting") {
			return;
		}

		if ($request->isXmlHttpRequest()) {
			return;
		}

		$debugbarRenderer = $this->class->getJavascriptRenderer($this->getJavascriptPath());
		$kernel->getBundle("Twig")->addGlobal("debugbar", array(
				"body" => $debugbarRenderer->renderOnShutdownWithHead()
			));
	}

	public function getJavascriptPath() {
		$res = Kernel::getCore()->getConfig()["res"];
		return $res["scheme"]."://".$res["host"]."/res_dev.php/DebugBar/";
	}

	public function disrupt() {
		$this->class["messages"]->addMessage("-- Disrupt !!! --");

		$debugbarRenderer = $this->class->getJavascriptRenderer($this->getJavascriptPath());
		echo $debugbarRenderer->renderHead().$debugbarRenderer->render();
	}
}
