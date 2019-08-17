<?php
namespace Core\Bundle\DebugBar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class GlobalsDataCollector extends DataCollector implements Renderable {
	public function collect() {
		$vars = array("_GET", "_POST", "_SESSION", "_COOKIE", "_SERVER");
		$data = array();

		foreach ($vars as $var) {
			if (isset($GLOBALS[$var])) {
				$data["$" . $var] = $this->formatVar($GLOBALS[$var]);
			}
		}

		return $data;
	}

	public function getName() {
		return "Globals";
	}

	public function getWidgets() {
		return array(
			"Globals" => array(
				"icon" => "globe",
				"widget" => "PhpDebugBar.Widgets.VariableListWidget",
				"map" => "Globals",
				"default" => "{}"
			)
		);
	}
}
