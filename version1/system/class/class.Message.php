<?php
class Message {
	private $idmessage;
	private $idquestion;
	private $texte;
	private $type;
	private $idmembre;
	private $dateenvois;
	private $dateedition;
	/*---*/
	private $vote;
	private $commentaire;

	public function __construct($id) {
		$this->idmessage = $id;
	}

	private function load() {
		global $BasedeDonnee;

		$tab = $BasedeDonnee->lire("SELECT * FROM message WHERE idmessage = ?", array($this->idmessage), BdD::FETCH);
		$this->idmessage = $tab["idmessage"];
		$this->idquestion = $tab["idquestion"];
		$this->texte = $tab["texte"];
		$this->type = $tab["type"];
		$this->idmembre = $tab["idmembre"];
		$this->dateenvois = $tab["dateenvois"];
		$this->dateedition = $tab["dateedition"];
	}

	public function getidmessage() {
		if ($this->idmessage === null) {
			$this->load();
		}
		return $this->idmessage;
	}
	public function getidquestion() {
		if ($this->idquestion === null) {
			$this->load();
		}
		return $this->idquestion;
	}
	public function gettexte() {
		if ($this->texte === null) {
			$this->load();
		}
		return $this->texte;
	}
	public function gettype() {
		if ($this->type === null) {
			$this->load();
		}
		return $this->type;
	}
	public function getidmembre() {
		if ($this->idmembre === null) {
			$this->load();
		}
		return $this->idmembre;
	}
	public function getdateenvois() {
		if ($this->dateenvois === null) {
			$this->load();
		}
		return $this->dateenvois;
	}
	public function getdateedition() {
		if ($this->dateedition === null) {
			$this->load();
		}
		return $this->dateedition;
	}
	/*---*/
	public function getvote() {
		global $BasedeDonnee;
		if ($this->vote === null) {
			$this->vote = $BasedeDonnee->lire("SELECT SUM(valeur) AS vote FROM msgvote WHERE idmessage = ?", array($this->idmessage), "vote");
			if ($this->vote == null) { $this->vote = 0; }
		}
		return $this->vote;
	}

	public function getcommentaire($i = 0) {
		global $BasedeDonnee;
		if (!is_array($this->commentaire)) {
			$this->commentaire = array();
			$tab = $BasedeDonnee->lire("SELECT idcommentaire FROM msgcomment WHERE idmessage = ? ORDER BY idcommentaire", array($this->idmessage));
			foreach ($tab as $stab) {
				$this->commentaire[] = $stab["idcommentaire"];
			}
		}

		if (count($this->commentaire) == 0) { return null; }

		if (!is_object($this->commentaire[$i])) {
			$this->commentaire[$i] = new Commentaire($this->commentaire[$i]);
		}
		return $this->commentaire[$i];
	}
	public function getnombrecommentaire() {
		if (!is_array($this->commentaire)) {
			$this->getcommentaire();
		}
		return count($this->commentaire);
	}
}
?>