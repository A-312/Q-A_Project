<?php
include "../system/main.php";

if (is_null($MembreActuel)) { die("NaN"); }

$retour = array();

$id = (int) $System->getGET("id");

$valeur = $System->getGET("s");

if ($valeur == "suivre" && is_array($BasedeDonnee->lire("SELECT idquestion FROM question WHERE idquestion = ?", array($id), BdD::FETCH))) {
	$parametre = array($id, $MembreActuel->getidmembre());
	if ($BasedeDonnee->lire("SELECT * FROM questsuivi WHERE idquestion = ? AND idmembre = ?", $parametre)) {
		$BasedeDonnee->ecrire("DELETE FROM questsuivi WHERE idquestion = ? AND idmembre = ?", $parametre);
	} else {
		$BasedeDonnee->ecrire("INSERT INTO questsuivi(idquestion, idmembre) VALUES (?, ?)", $parametre);
	}

	echo (new Question($id))->getsuivi();
} elseif (($valeur != "up" && $valeur != "down") || !is_array($BasedeDonnee->lire("SELECT idmessage FROM message WHERE idmessage = ?", array($id), BdD::FETCH))) {
	echo "erreur";
} else {
	$parametre = array($id, $MembreActuel->getidmembre());
	$valeur = ($valeur == "up") ? 1 : -1;
	if ($valeur_actuel = $BasedeDonnee->lire("SELECT valeur FROM msgvote WHERE idmessage = ? AND idmembre = ?", $parametre, "valeur")) {
		$BasedeDonnee->ecrire("DELETE FROM msgvote WHERE idmessage = ? AND idmembre = ?", $parametre);
	}

	if ($valeur_actuel != $valeur) {
		$parametre[] = $valeur;
		$BasedeDonnee->ecrire("INSERT INTO msgvote(idmessage, idmembre, valeur) VALUES (?, ?, ?)", $parametre);
	}
	

	echo (new Message($id))->getvote();
}
?>