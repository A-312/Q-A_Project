<?php
if (!is_null($MembreActuel)):
	$infomsg = "Vous êtes déjà connecté.";
	header("refresh:1;url=./");
elseif ($System->getPOST("pseudo") != ""):
	$infomsg = "";
	$pseudo = substr(htmlspecialchars(ucfirst($System->getPOST("pseudo"))),0,60);
	$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);
	if (!$System->verifierFormulaire("connexion", $session)) {
		$infomsg = "Le formulaire a expiré.";
	} elseif (!$id = $BasedeDonnee->lire("SELECT idMembre as id FROM membre WHERE pseudo = ?", array($pseudo), "id")) {
		$infomsg = "Compte inconnu.";
	} else {
		$infomsg = "Vous êtes à présent connecté.";
		header("refresh:1;url=./");

		$_SESSION["id"] = $id;
	}
endif;
?>
<div id="subContent">
	<div id="formContent">
		<div class="headerContent">
			<span>Connexion</span>
		</div>
	<?php if (!empty($infomsg)): ?>
		<div class="subContentBorder">
			<span><?=$infomsg?></span>
		</div>
	<?php else: ?>
		<form class="subContentBorder" method="POST">
			Pseudo :<br>
			<input id="term" class="ui-for-pseudo-it-is-me" type="text" name="pseudo" />
			<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("connexion")?>"/>
			<br><br>
			<input type="submit" value="Connexion"/>
		</form>
	<?php endif; ?>
	</div>
</div>