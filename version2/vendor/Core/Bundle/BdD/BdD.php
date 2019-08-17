<?php
namespace Core\Bundle\BdD;

use Core\Kernel\Bundle\Bundle;

class BdD extends Bundle {
	public function boot($dev = false) {
		if ($dev) { return $this->bootdev(); }

		$this->class = new \BdD\BdD();
	}

	public function bootdev() {
		$this->class = new \BdD\BdDWithDebugBar();
	}

	public function build() {
		$h = \Core\Kernel\Kernel::getCore()->getConfigPDO();
		$this->class->connect($h["host"], $h["dbname"], $h["user"], $h["password"]);
	}
}