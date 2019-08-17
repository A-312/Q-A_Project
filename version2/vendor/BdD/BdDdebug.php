<?php
namespace BdD;

use PDO;

class BdDdebug extends BdD {
	protected static $log = "";
	protected static $av = 0;
	protected static $limit = 3;
	protected static $count = 0;
	protected static $time = 0;

	public function setLimit($limit = 3) {
		self::$limit = $limit;
	}

	public function lire($query, $input_parameters = array(), $selected = BdD::FETCH_ALL) {
		$this->begin();

		$r = parent::lire($query, $input_parameters, $selected);

		$this->end($query, $input_parameters);

		return $r;
	}

	public function ecrire($query, $input_parameters = array()) {
		$this->begin();

		$r = parent::ecrire($query, $input_parameters);

		$this->end($query, $input_parameters);

		return $r;
	}

	public function getInfo() {
		return self::$count." request in ".self::$time." ms, ".round((self::$time/self::$count), 4)." req/ms.\n\n";
	}

	public function getLog() {
		return "---- Slow query (> ".self::$limit." ms) : ----\n".self::$log;
	}

	public function getCount() {
		return self::$count;
	}

	protected function begin() {
		self::$av = microtime(true);
	}

	protected function end($query, $input_parameters) {
		$time = round((microtime(true)-self::$av)*1000, 4);
		self::$log .= (self::$limit <= $time) ? "'".preg_replace('/\s\s+/',' ',$query)."'\n".str_replace("\n","",print_r($input_parameters, true))."\nExec : ".$time." ms\n" : "";
		self::$count++;
		self::$time += $time;
	}

	protected function exception($e) {
		$this->error_log($e);
		echo "<pre>$e</pre>";

		throw $e;
	}
}
