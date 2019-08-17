<?php
$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);
if ($System->verifierFormulaire("deconnexion", $session)) {
	if(!is_null($MembreActuel)):
		session_destroy();
	endif;
	$page = substr(htmlspecialchars($System->getPOST("page")),0,120);
	if (!empty($page)) {
		die(header("Location:{$page}"));
	}
	die(header('Location:./'));
} else {
	die(header('Location:erreur.html&e=formulaire'));
}
?>