<?php
namespace src\Forum\Type;

use Core\Kernel\Controller\Controller;
use BdD\BdD;

class Membre extends Controller {
	private $idmembre;
	private $pseudo;
	private $reputation;

	public function __construct($id) {
		global $BasedeDonnee;

		$tab = $this->get("BdD")->lire("SELECT * FROM membre WHERE idmembre = ?", array($id), BdD::FETCH);
		$this->idmembre = $tab["idmembre"];
		$this->pseudo = $tab["pseudo"];
		$this->reputation = $tab["reputation"];
	}

	public function getidmembre() { return $this->idmembre; }
	public function getavatar() { return ($this->idmembre == 4)?"./design/Logo12.png":"./design/navatar.png"; }
	public function getpseudo() { return $this->pseudo; }
	public function getreputation() { return $this->reputation; }
	public function geturl() { return "./profil-{$this->idmembre}.html"; }

	public function getsuivi($idquestion) {
		global $BasedeDonnee;

		return $this->get("BdD")->lire("SELECT * FROM questsuivi WHERE idquestion = ? AND idmembre = ?", array($idquestion, $this->idmembre)) ? true : false;
	}
	public function getvote($idmessage) {
		global $BasedeDonnee;

		return $this->get("BdD")->lire("SELECT valeur FROM msgvote WHERE idmessage = ? AND idmembre = ?", array($idmessage, $this->idmembre), "valeur");
	}
}