<?php
namespace BdD;

use PDO;
use Exception;

class BdD {
	const FETCH_ALL = -1;
	const FETCH = 0;

	protected static $bdd;
	protected $errorfunction;

	public function __construct($host = null, $dbname = null, $user = null, $password = null) {
		if ($host !== null && $dbname !== null && $user !== null && $password !== null) {
			$this->connect($host, $dbname, $user, $password);
		}
	}

	public function connect($host, $dbname, $user, $password) {
		try {
			self::$bdd = new PDO("mysql:host=".$host.";dbname=".$dbname, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			self::$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (Exception $e) {
			$this->exception($e);
		}
	}

	public function sqlitememory() {
		self::$bdd = new PDO("sqlite::memory:");
	}

	public function getPDO() {
		return self::$bdd;
	}

	public function lire($query, $input_parameters = array(), $selected = BdD::FETCH_ALL) {
		try {
			$rep = self::$bdd->prepare($query);
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

			return $return;
		}
		catch (Exception $e) {
			$this->exception($e);
		}
	}

	public function ecrire($query, $input_parameters = array()) {
		try {
			$send = self::$bdd->prepare($query);
			$send->execute($input_parameters);
			if ($send->errorCode() != 0) {
				throw new Exception($query."\n".$send->errorInfo()[2]);
			}
			return $send->rowCount();
		}
		catch (Exception $e) {
			$this->exception($e);
		}
	}

	public function read($query, $input_parameters = array(), $selected = BdD::FETCH_ALL) {
		return $this->lire($query, $input_parameters, $selected);
	}
	public function write($query, $input_parameters = array()) {
		return $this->ecrire($query, $input_parameters);
	}

	protected function error_log($e) {
		$m = get_class($e).": {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}\nStack trace:";
		preg_match_all("#\#[0-9] (.*)\n?#", $e->getTraceAsString(), $match);

		foreach ($match[1] as $i => $l) {
			$i++; $m .= "\n  $i. $l";
		}

		foreach (explode("\n", $m) as $line) {
			error_log("PDO ".$line);
		}
	}

	protected function exception($e) {
		$this->error_log($e);

		throw $e;
	}
}
