<?php
namespace Core\Bundle\DebugBar\DataCollector;

use Core\Kernel\Kernel;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class RequestDataCollector extends DataCollector implements Renderable {
	public function collect() {
		$request = Kernel::getCore()->getRequest();
		if ($request === null) { return; }

		$data = array(
			"query" => $request->query,
			"request" => $request->request,
			"cookies" => $request->cookies,
			"session" => $request->getSession() ?: null,
			"server" => $request->server,
			"headers" => $request->headers,
			"attributes" => $request->attributes,
			"files" => $request->files
		);

		foreach ($data as $key=>$parameterBag) {
			if ($parameterBag == null) { continue; }
			$data[$key] = $this->formatVar($parameterBag->all());
		}

		return $data;
	}

	public function getName() {
		return "Request";
	}

	public function getWidgets() {
		return array(
			"Request" => array(
				"icon" => "tags",
				"widget" => "PhpDebugBar.Widgets.VariableListWidget",
				"map" => "Request",
				"default" => "{}"
			)
		);
	}
}
