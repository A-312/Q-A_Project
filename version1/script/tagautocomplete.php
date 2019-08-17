<?php
include "../system/main.php";

$retour = array();

$term = $System->getGET("term");

if (!empty($term) && 2 <= strlen($term) && preg_match("#^(\w+)$#", $term)) {
	if ($System->getGET("pseudo") != "") {
		$tableau = $BasedeDonnee->Lire("SELECT idmembre, 'membre' as type FROM membre WHERE pseudo LIKE ? LIMIT 0,12", array("%".$term."%"));

		foreach ($tableau as $tab) {
			$membre = new Membre($tab["idmembre"]);
			$retour[] = array("value" => $membre->getpseudo(), "avatar" => $membre->getavatar(), "label" => $membre->getpseudo(), "desc" => $membre->getreputation(), "type" => $tab["type"]);
		}
	} else {
		$tableau = $BasedeDonnee->Lire("SELECT tag FROM tag WHERE tag LIKE ? LIMIT 0,12", array("%".$term."%"));

		foreach ($tableau as $tab) {
			$n = $BasedeDonnee->Lire(
				"SELECT COUNT(*) AS n
				FROM tag
					JOIN questtag USING (idtag)
				WHERE tag LIKE ?", array($tab["tag"]), "n");
			$retour[] = array("value" => $tab["tag"], "label" => $tab["tag"], "desc" => $n);
		}
	}
}

echo json_encode($retour);
?>