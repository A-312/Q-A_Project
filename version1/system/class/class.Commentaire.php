<?php
class Commentaire {
	private $idcommentaire;
	private $idmessage;
	private $texte;
	private $idmembre;
	private $dateenvois;
	private $dateedition;

	public function __construct($id) {
		global $BasedeDonnee;

		$tab = $BasedeDonnee->lire("SELECT * FROM msgcomment WHERE idcommentaire = ?", array($id), BdD::FETCH);
		$this->idcommentaire = $tab["idcommentaire"];
		$this->idmessage = $tab["idmessage"];
		$this->texte = $tab["texte"];
		$this->idmembre = $tab["idmembre"];
		$this->dateenvois = $tab["dateenvois"];
		$this->dateedition = $tab["dateedition"];
	}

	public function getidcommentaire() { return $this->idcommentaire; }
	public function getidmessage() { return $this->idmessage; }
	public function gettexte() { return $this->texte; }
	public function getidmembre() { return $this->idmembre; }
	public function getdateenvois() { return $this->dateenvois; }
	public function getdateedition() { return $this->dateedition; }
}
?>