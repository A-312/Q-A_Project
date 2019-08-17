<?php
namespace Core\Bundle\DebugBar\DataCollector\UrlMatcher;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class UrlMatcherCollector extends DataCollector implements Renderable {
	public function __construct(TraceableUrlMatcher $UrlMatcher) {
		$this->UrlMatcher = $UrlMatcher;
	}

	public function collect() {
		$templates = array(); $accuRenderTime = 0; $match = false;

		$traces = $this->UrlMatcher->getTraces();
		if (is_array($traces)) {
			foreach ($traces as $trace) {
				$accuRenderTime += $trace["time"];
				$templates[] = array(
					"name" => "{$trace["name"]} [{$trace["path"]}] : {$trace["log"]}",
					"render_time" => $trace["time"],
					"render_time_str" => $this->formatDuration($trace["time"])
				);
				if ($trace["level"] == 2) { $match = $trace["path"]; }
			}
		}

		return array(
			"nb_templates" => count($templates),
			"templates" => $templates,
			"accumulated_render_time" => $accuRenderTime,
			"accumulated_render_time_str" => $this->formatDuration($accuRenderTime),
			"sentence" => "paths were checked".(($match !== false) ? ", one match [$match]." : ", no match.")
		);
	}

	public function getName() {
		return "UrlMatcher";
	}

	public function getWidgets() {
		return array(
			"UrlMatcher" => array(
				"icon" => "sort-amount-desc",
				"widget" => "PhpDebugBar.Widgets.TemplatesWidget",
				"map" => "UrlMatcher",
				"default" => "[]"
			),
            'UrlMatcher:badge' => array(
                'map' => 'UrlMatcher.nb_templates',
                'default' => 0
            )
		);
	}
}
