<?php
class BdD {
	const FETCH_ALL = -1;
	const FETCH = 0;

	private $bdd;
	private $errorfunction;

	public function __construct($host, $dbname, $user, $password, $errorfunction = false) {
		$this->set_errorfunction($errorfunction);
		$this->connect($host, $dbname, $user, $password);
	}

	private function connect($host, $dbname, $user, $password) {
		try {
			$this->bdd = new PDO("mysql:host=".$host.";dbname=".$dbname, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		}
		catch (Exception $e) {
			$f = $this->errorfunction; $f($e);
		}
	}

	public function lire($query, $input_parameters = array(), $selected = BdD::FETCH_ALL) {
		try {
			$this->begin();

			$rep = $this->bdd->prepare($query);
			$rep->execute($input_parameters);

			if ($rep->errorCode() != 0) {
				throw new Exception($query."\n".$rep->errorInfo()[2]);
			}
			if ($selected === BdD::FETCH_ALL) {
				$return = $rep->fetchAll(PDO::FETCH_ASSOC);
			} elseif ($selected === BdD::FETCH) {
				$return = $rep->fetch(PDO::FETCH_ASSOC);
			} else {
				$return = $rep->fetch(PDO::FETCH_ASSOC);
				$return = (isset($return[$selected])) ? $return[$selected] : null;
			}

			$this->end($query, $input_parameters);

			return $return;
		}
		catch (Exception $e) {
			$f = $this->errorfunction; $f($e);
		}
	}

	public function ecrire($query, $input_parameters = array()) {
		try {
			$send = $this->bdd->prepare($query);
			$send->execute($input_parameters);
			if ($send->errorCode() != 0) {
				throw new Exception($query."\n".$send->errorInfo()[2]);
			}
			return $send->rowCount();
		}
		catch (Exception $e) {
			$f = $this->errorfunction; $f($e);
		}
	}

	private function set_errorfunction($errorfunction) {
		$this->errorfunction = ($errorfunction) ? $errorfunction : function($e) {
			list($t2, $t) = debug_backtrace();
			$message = "---- ".date("Y-m-d H:i:s", time())." ----\nline : ".$t["line"]." - file : ".$t["file"]."\n".preg_replace('/\s\s+/', ' ', $e->getMessage());

			ob_end_clean();

			if ($this->debug) {
				die("<pre>$message</pre>");
			} else {
				die("Sorry we have an error.");
			}
		};
	}

	private $debug = false;
	private $log = "";
	private $av = 0;
	private $limit = 3;
	private $count = 0;
	private $time = 0;

	public function debug($bool = true, $limit = 3) {
		$this->debug = $bool;
		$this->limit = $limit;
	}

	public function getInfo() {
		if ($this->debug) {
			return "{$this->count} request in {$this->time} ms, ".round(($this->time/$this->count), 4)." req/ms.\n\n";
		}
	}

	public function getLog() {
		if ($this->debug) {
			return "---- Slow query (> ".$this->limit." ms) : ----\n".$this->log;
		}
	}

	public function getCount() {
		return $this->count;
	}

	private function begin() {
		if ($this->debug) {
			$this->av = microtime(true);
		}
	}

	private function end($query, $input_parameters) {
		if ($this->debug) {
			$time = round((microtime(true)-$this->av)*1000, 4);
			$this->log .= ($this->limit <= $time) ? "'".preg_replace('/\s\s+/',' ',$query)."'\n".str_replace("\n","",print_r($input_parameters, true))."\nExec : ".$time." ms\n" : "";
			$this->count++;
			$this->time += $time;
		}
	}
}
?>
