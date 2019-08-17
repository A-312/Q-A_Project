<?php
namespace BdD;

use PDO;
use DebugBar\DebugBar;
use DebugBar\DataCollector\PDO\TraceablePDO;

class BdDWithDebugBar extends BdD {
	protected static $debugbar;

	public function connect($host, $dbname, $user, $password) {
		parent::connect($host, $dbname, $user, $password);

		$this->initializeTraceablePDO();
	}

	private function initializeTraceablePDO() {
		if (parent::$bdd instanceof PDO) {
			parent::$bdd = new TraceablePDO(parent::$bdd);
		}
	}

	public function sqlitememory() {
		parent::sqlitememory();

		$this->initializeTraceablePDO();
	}

	public function setDebugBar(DebugBar $debugbar) {
		self::$debugbar = $debugbar;
	}

	protected function exception($e) {
		$this->error_log($e);
		try {
			self::$debugbar['exceptions']->addException($e);
		} catch (\Exception $l) {}

		throw $e;
	}
}
