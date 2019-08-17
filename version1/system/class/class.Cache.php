<?php
class Cache {
	private $membre;

	function membre($opt1) {
		if (is_object($opt1)) {
			$this->membre[$opt1->getidmembre()] = $opt1;
		} else {
			if (!isset($this->membre[$opt1])) {
				$this->membre[$opt1] = new Membre($opt1);
			}
			return $this->membre[$opt1];
		}
	}
}
?>