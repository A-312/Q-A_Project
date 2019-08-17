<?php
namespace Core\Bundle\DebugBar\DataCollector;

use Core\Kernel\Kernel;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class KernelVersionCollector extends DataCollector implements Renderable {
	public function collect() {
		return array("kernelversion" => Kernel::VERSION."-".Kernel::getCore()->getEnvironment());
	}

	public function getName() {
		return "KernelVersionCollector";
	}

	public function getWidgets() {
		return array(
			"KernelVersionCollector" => array(
				"icon" => "rocket",
				"tooltip" => "Kernel Version and Environment",
				"map" => "KernelVersionCollector.kernelversion",
				"default" => 0
			)
		);
	}
}
