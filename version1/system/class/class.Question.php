
<?php
class Question {
	private $idquestion;
	private $question;
	private $vue;
	//private $categorie;
	/*---*/
	private $msg_tri;
	private $message;
	private $suivi;
	private $tag;

	public function __construct($id, $tri = "ancien", $tab = array()) {
		$this->idquestion = $id;
		$this->msg_tri = $tri;
		$this->question = (isset($tab["question"])) ? $tab["question"] : null;
		$this->vue = (isset($tab["vue"])) ? $tab["vue"] : null;
		//$this->categorie = ($tab["categorie"]) ? $tab["categorie"] : null;
	}

	private function load() {
		global $BasedeDonnee;

		$tab = $BasedeDonnee->lire("SELECT * FROM question WHERE idquestion = ?", array($this->idquestion), BdD::FETCH);
		$this->idquestion = $tab["idquestion"];
		$this->question = $tab["question"];
		$this->vue = $tab["vue"];
		//$this->categorie = $tab["categorie"];
	}

	public function getidquestion() {
		if ($this->idquestion === null) {
			$this->load();
		}
		return $this->idquestion;
	}
	public function getquestion() {
		if ($this->question === null) {
			$this->load();
		}
		return $this->question;
	}
	public function getvue() {
		if ($this->vue === null) {
			$this->load();
		}
		return $this->vue;
	}

	/*---*/

	public function getmessage($i = 0) {
		global $BasedeDonnee;
		if (!is_array($this->message)) {
			if ($this->msg_tri == "vote") {
				$requete1 =
					"SELECT message.idmessage
					FROM message, msgvote
					WHERE idquestion = :id AND message.idmessage = msgvote.idmessage
					GROUP BY message.idmessage"
				;
				$requete2 =
					"SELECT message.idmessage, type, SUM(valeur) as vote
					FROM message, msgvote
					WHERE idquestion = :id AND message.idmessage = msgvote.idmessage
					GROUP BY message.idmessage"
				;

				$tab = $BasedeDonnee->lire(
					"SELECT idmessage, type, 0 as vote
					FROM message
					WHERE idquestion = :id AND idmessage NOT IN($requete1)
					UNION ($requete2)
					ORDER BY CASE type WHEN 'question' THEN 1 ELSE 2 END, vote DESC, idmessage"
				, array("id" => $this->idquestion));
			} else { //ancien
				$tab = $BasedeDonnee->lire(
					"SELECT idmessage
					FROM message
					WHERE idquestion = ?
					ORDER BY CASE type WHEN 'question' THEN 1 ELSE 2 END, idmessage"
				, array($this->idquestion));
			}
			foreach ($tab as $stab) {
				$this->message[] = $stab["idmessage"];
			}
		}
		if ($i !== false && !is_object($this->message[$i])) {
			$this->message[$i] = new Message($this->message[$i]);
		}
		return $this->message[$i];
	}
	public function getsuivi() {
		global $BasedeDonnee;
		if ($this->suivi === null) {
			$this->suivi = $BasedeDonnee->lire("SELECT COUNT(*) AS suivi FROM questsuivi WHERE idquestion = ?", array($this->idquestion), "suivi");
			if ($this->suivi == null) { $this->suivi = 0; }
		}
		return $this->suivi;
	}
	public function gettag() {
		global $BasedeDonnee;
		if (!is_array($this->tag)) {
			$tab = $BasedeDonnee->lire("SELECT tag FROM tag JOIN questtag USING (idtag) WHERE idquestion = ?", array($this->idquestion));
			foreach ($tab as $stab) {
				$this->tag[] = $stab["tag"];
			}
		}
		return $this->tag;
	}

	public function getnombrerep() {
		$this->getmessage(false);
		return count($this->message)-1;
	}

	public function getidmessage() { return $this->getmessage()->getidmessage(); }
	public function gettexte() { return $this->getmessage()->gettexte(); }
	public function getidmembre() { return $this->getmessage()->getidmembre(); }
	public function getdateenvois() {return $this->getmessage()->getdateenvois(); }
	public function getdateedition() { return $this->getmessage()->getdateedition(); }
	public function getvote() { return $this->getmessage()->getvote(); }

	public function geturl($question = null) {
		global $System;
		if (is_null($question)) {
			$question = $this->getquestion();
		}
		return "./lecture-{$this->idquestion}-{$System->parseUrl($question)}.html";
	}

	public function incrementervue() {
		global $BasedeDonnee;

		$BasedeDonnee->ecrire("UPDATE question SET vue = vue + 1 WHERE idquestion = ?", array($this->idquestion));
		return $this->getvue(); 
	}
}
?>