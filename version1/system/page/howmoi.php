<div id="subContent">
<?php

echo (@fsockopen("127.0.0.1", 3306, $errno, $errstr, 2)) ? "lol" : "non";

/*class Requete {
	const DELETE = "DELETE";
	const INSERT = "INSERT INTO";
	const SELECT = "SELECT";
	const UPDATE = "UPDATE";

	private $command = Requete::SELECT;
	private $column = array();
	private $table = array();

	public function __construct() {
		$num = func_num_args(); $args = func_get_args();

		if ($num == 1 && $args[0] == Requete::UPDATE) {
			$this->$command = Requete::UPDATE;
		}
	}

	private function r_get($num, $args, $type) {
		for ($i = 0; $i < $num; $i++) {
			$this->{$type}[] = $args[$i];
		}

		return $this;
	}

	public function column() {
		return $this->r_get(func_num_args(), func_get_args(), "column");
	}

	public function table() {
		return $this->r_get(func_num_args(), func_get_args(), "table");
	}

	public function show() {
		$query = $this->command." ";

		if (count($this->column) == 0) {
			$query .= "*";
		} else {
			for ($i = 0; $i < count($this->column); $i++) {
				$query .= (($i != 0) ? ", " : "").((is_array($t = $this->column[$i])) ? $t[0]." AS ".$t[1] : $t);
			}
		}

		$query .= " FROM ";

		for ($i = 0; $i < count($this->table); $i++) {
			$query .= (($i != 0) ? ", " : "").$this->table[$i];
		}

		return $query;
	}
}

$query = (new Requete(Requete::SELECT))
	->column(array("moi", "HorsSujet"), "toi")
	->table("salut");

echo $query->show();*/
?>
</div>