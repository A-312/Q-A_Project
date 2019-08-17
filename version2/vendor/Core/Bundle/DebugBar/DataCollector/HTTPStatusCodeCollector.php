<?php
namespace Core\Bundle\DebugBar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class HTTPStatusCodeCollector extends DataCollector implements Renderable {
	public function collect() {
		return array("httpstatuscode" => http_response_code());
	}

	public function getName() {
		return "HTTPStatusCodeCollector";
	}

	public function getWidgets() {
		return array(
			"HTTPStatusCodeCollector" => array(
				"icon" => "sign-out",
				"tooltip" => "HTTP Status Code",
				"map" => "HTTPStatusCodeCollector.httpstatuscode",
				"default" => 0
			)
		);
	}
}
