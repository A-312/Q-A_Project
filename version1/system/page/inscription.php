<?php
if (!is_null($MembreActuel)):
	$infomsg = "Vous êtes déjà inscrit.";
	header("refresh:1;url=./");
elseif ($System->getPOST("pseudo") != ""):
	$infomsg = "";
	$pseudo = substr(htmlspecialchars(ucfirst($System->getPOST("pseudo"))),0,60);
	$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);
	if (!$System->verifierFormulaire("inscription", $session)) {
		$infomsg = "Le formulaire a expiré.";
	} elseif (!preg_match("#^(.{4,20})$#", $pseudo) || !preg_match("#^([A-z0-9-]+)$#i", $pseudo)) {
		$infomsg = "Erreur pseudo invalide.";
	} else {
		$BasedeDonnee->ecrire("INSERT INTO membre(pseudo) VALUES (?)", array($pseudo));
		$infomsg = "Inscription realisée.";
	}
endif;
?>
<div id="subContent">
	<div id="formContent">
		<div class="headerContent">
			<span>Inscription</span>
		</div>
	<?php if (!empty($infomsg)): ?>
		<div class="subContentBorder">
			<span><?=$infomsg?></span>
		</div>
	<?php else: ?>
		<form class="subContentBorder" method="POST">
			Pseudo :<br>
			<input type="text" name="pseudo" />
			<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("inscription")?>"/>
			<br><br>
			<input type="submit" value="Inscription"/>
		</form>
	<?php endif; ?>
	</div>
</div>