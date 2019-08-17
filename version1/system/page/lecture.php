<?php
$idquestion = (int) $System->getGET("id");

if ($System->getGET("tri") != "") {
	$_SESSION["question-tri"] = (array_search($System->getGET("tri"), array("ancien", "vote")) !== false) ? $System->getGET("tri") : "ancien";
} elseif (!isset($_SESSION["question-tri"])) {
	$_SESSION["question-tri"] = "ancien";
}
$tri = $_SESSION["question-tri"];

if ($BasedeDonnee->lire("SELECT COUNT(*) AS nombre FROM question WHERE idquestion = ?", array($idquestion), "nombre") == 0):
	$infomsg = "Oh ! Cette question n'existe pas encore.";
elseif ($System->getPOST("idquestion") != "" && $System->getPOST("message") != ""):
	$idquestion = (int) $System->getPOST("idquestion");
	$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);
	if (is_null($MembreActuel)) {
		$infomsg = "Et si tu te connectais ?";
	} elseif (!$System->verifierFormulaire("question$idquestion", $session)) {
		$infomsg = "Le formulaire a expiré.";
	} else {
		list($erreur, $message) = (new Parser())->parseBadHTML($System->getPOST("message"));
		if ($erreur) {
			$infomsg = "Erreur : ".$message;
		} else {
			$BasedeDonnee->ecrire("INSERT INTO message(idquestion, texte, type, idmembre, dateenvois, dateedition) VALUES (?, ?, ?, ?, ?, ?)", array($idquestion, $message, "reponse", $MembreActuel->getidmembre(), time(), time()));
		}
	}
elseif ($System->getPOST("idmessage") != "" && $System->getPOST("message") != ""):
	$idmessage = (int) $System->getPOST("idmessage");
	$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);
	if (is_null($MembreActuel)) {
		$infomsg = "Et si tu te connectais ?";
	} elseif (!$System->verifierFormulaire("message$idmessage", $session)) {
		$infomsg = "Le formulaire a expiré.";
	} elseif ($BasedeDonnee->lire("SELECT COUNT(*) AS nombre FROM message WHERE idquestion = ? AND idmessage = ?", array($idquestion, $idmessage), "nombre") == 0) {
		$infomsg = "Ce message n'existe pas.";
	} elseif ($BasedeDonnee->lire("SELECT idmembre FROM message WHERE idmessage = ?", array($idmessage), "idmembre") != $MembreActuel->getidmembre()) {
		$infomsg = "Tu ne peux pas éditer ce message, il n'est pas de toi.";
	} else {
		list($erreur, $message) = (new Parser())->parseBadHTML($System->getPOST("message"));
		if ($erreur) {
			$infomsg = "Erreur : ".$message;
		} elseif ($BasedeDonnee->lire("SELECT COUNT(*) AS nombre FROM message WHERE idmessage = ? AND texte <> ?", array($idmessage, $message), "nombre") == 0) {
			$infomsg = "Rien a été modifié.";
		} else {
			$BasedeDonnee->ecrire("UPDATE message SET texte = ?, dateedition = ? WHERE idmessage = ?", array($message, time(), $idmessage));
		}
	}
elseif ($System->getPOST("idmessage") != "" && $System->getPOST("commentaire") != ""):
	$idmessage = (int) $System->getPOST("idmessage");
	$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);
	if (is_null($MembreActuel)) {
		$infomsg = "Et si tu te connectais ?";
	} elseif (!$System->verifierFormulaire("commentaire$idmessage", $session)) {
		$infomsg = "Le formulaire a expiré.";
	} elseif ($BasedeDonnee->lire("SELECT COUNT(*) AS nombre FROM message WHERE idquestion = ? AND idmessage = ?", array($idquestion, $idmessage), "nombre") == 0) {
		$infomsg = "Ce message n'existe pas.";
	} elseif ($BasedeDonnee->lire("SELECT idmembre FROM msgcomment WHERE idmessage = ? ORDER BY idcommentaire DESC", array($idmessage), "idmembre") == $MembreActuel->getidmembre()) {
		$infomsg = "Repose toi ;), le dernier commentaire est de toi.";
	} else {
		$commentaire = (new Parser())->parseHTML($System->getPOST("commentaire"));
		$BasedeDonnee->ecrire("INSERT INTO msgcomment(idmessage, idmembre, texte, dateenvois, dateedition) VALUES (?, ?, ?, ?, ?)", array($idmessage, $MembreActuel->getidmembre(), $commentaire, time(), time()));
	}
endif;

if (!empty($infomsg)):
?>

<div id="subContent">
	<div id="formContent">
		<div class="headerContent">
			<span>Erreur</span>
		</div>
		<div class="subContentBorder">
			<span><?=$infomsg?></span>
		</div>
	</div>
</div>
<?php
header("refresh:3;");
else:
$opt_page = true;
$c = (new vue())->sujetQuestion($idquestion, $tri, $opt_page);
?>
<div id="subContent" class="I-read-this-content">
	<div id="sujetquestion">
		<div class="headerContent">
			<span><?=$c["asking"]?></span>
		</div>
		<table id="listemessage">
			<tr class="entete hq">
				<td class="left"></td>
				<td colspan="2">
					<h1><a href="<?=$c["url"]?>"><?=$c["question"]?></a></h1>
					<div id="taglist">
						<span>Tags :</span>
					<?php foreach ($c["tag"] as $nom): ?>
						<a class="tagbl" href="./tag-<?=$nom;?>.html"><?=$nom;?></a>
					<?php endforeach; ?>
					</div>
				</td>
			</tr>
		<?php foreach ($c["message"] as $msg): ?>
			<tr class="entete msg">
				<td class="left"></td>
				<td class="pseudo"><a href="<?=$msg["membre"]["url"]?>"><?=$msg["membre"]["pseudo"]?></a></td>
				<td class="info">
					<a id="message-<?=$msg["idmessage"]?>" href="#message-<?=$msg["idmessage"]?>" title="<?=$msg["date"]?>"># <?=$msg["asking"]?></a>

				<?php if(!is_null($MembreActuel) && $msg["membre"]["id"] == $MembreActuel->getidmembre()): ?>
					<div class="toolmsg"><a onclick="toggleEditor(<?=$msg["idmessage"]?>)">Editer</a></div>
				<?php endif; ?>
				</td>
			</tr>
			<tr class="blocmsg <?=$msg["type"]?>">
				<td class="left votetool" id="d_vt<?=$msg["idmessage"]?>">
					<a onclick="vote('up', <?=$msg["idmessage"]?>, this)" class="up <?=$msg["mvote"][0]?>"></a>
					<span class="num" id="vote_num"><?=$msg["vote"]?></span>
					<a onclick="vote('down', <?=$msg["idmessage"]?>, this)" class="down <?=$msg["mvote"][1]?>"></a>
					
				<?php if($msg["type"] == "question"): ?>
					<a onclick="suivi(<?=$c["id"]?>, <?=$msg["idmessage"]?>, this)" class="star <?=$c["msuivi"]?>"></a>
					<span class="num" id="suivi_num"><?=$c["suivi"]?></span>
				<?php endif; ?>
				</td>
				<td class="profil">
					<a href="<?=$msg["membre"]["url"]?>"><img alt="avatar" src="<?=$msg["membre"]["avatar"]?>" /></a>
					<div class="center"><span class="arank" title="Réputation"><?=$msg["membre"]["reputation"]?></span></div>
				</td>
				<td class="texte">
					<div id="textmsg<?=$msg["idmessage"]?>"><?=$msg["texte"]?></div>
				
				<?php if(!is_null($MembreActuel) && $msg["membre"]["id"] == $MembreActuel->getidmembre()): ?>
					<form method="POST" style="display:none;" onsubmit="return submitEdit(this);">
						<input type="hidden" name="idmessage" value="<?=$msg["idmessage"]?>"/>
						<input type="hidden" name="message" />
						<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("message{$msg['idmessage']}")?>"/>
						<input type="submit" value="Confirmer l'édition" class="inputsubmit" />
					</form>
				<?php endif; ?>
				</td>
			</tr>

			<?php foreach ($msg["commentaire"] as $comment): ?>
			<tr id="comment-<?=$comment["idcommentaire"]?>" class="blocmsg commentaire">
				<td class="left center"></td>
				<td class="profil"></td>
				<td class="texte">
					<span><?=$comment["texte"]?></span>
					<span title="<?=$comment["date"]?>" class="autor"><a class="ilya" href="#comment-<?=$comment["idcommentaire"]?>"># <?=$comment["ilya"]?></a> <a href="<?=$comment["membre"]["url"]?>"><?=$comment["membre"]["pseudo"]?></a> <span class="arank"><?=$comment["membre"]["reputation"]?></span></span>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php if(!is_null($MembreActuel)): ?>
			<tr class="blocmsg commentaire tool">
				<td class="left"></td>
				<td class="profil"></td>
				<td class="texte">
					<form method="POST" action="#message-<?=$msg["idmessage"]?>" style="display:none;">
						<span contenteditable="true" onfocus="val_onfocus(this)" onblur="val_onblur(this)">Rédigez votre commentaire ici...</span>
						<input type="hidden" name="commentaire"/>
						<input type="hidden" name="idmessage" value="<?=$msg["idmessage"]?>"/>
						<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("commentaire{$msg["idmessage"]}")?>"/>
						<div class="center"><a>[ Ajouter le commentaire ]</a></div>
					</form>
					<div class="center"><a class="ajouter">Ajouter un commentaire</a></div>
				</td>
			</tr>
			<?php endif; ?>
			<?php if($msg["type"] == "question" && $c["rep"] != 0): ?>
			<tr class="entete">
				<td class="left"></td>
				<td colspan="2">
					<div class="headerContent">
						<span><?=$c["rep"]?></span>
						<ul>
							<li><a href="<?=$c["url"]?>&tri=ancien" <?=(($tri == "ancien") ? "class=\"here\"" : "")?>>Chronologique</a></li>
							<li><a href="<?=$c["url"]?>&tri=vote" <?=(($tri == "vote") ? "class=\"here\"" : "")?>>Notation</a></li>
						</ul>
					</div>
				</td>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ($opt_page !== false): ?>
			<tr class="foot">
				<td colspan="3">
					<?php
						$opt_page["url"] = $c["url"];
						include "./system/tool/pagination.php";
					?>
				</td>
			</tr>
		<?php else: ?>
			<tr class="foot">
				<td colspan="3">
					<br>
				</td>
			</tr>
		<?php endif; ?>
		</table>
		<div id="sommairemsg" class="sidebloc">
			<div class="headerContent">
				<span>Liste des réponses</span>
			</div>
			<div class="list">
				<center><a href="#header">-------- Haut de la page --------</a></center>
				<ul>
				<?php
					foreach ($c["message"] as $msg) {
						echo "<li><a id=\"message-{$msg["idmessage"]}\" href=\"#message-{$msg["idmessage"]}\" title=\"{$msg["date"]}\">[{$msg["vote"]}] Par {$msg["membre"]["pseudo"]}</a></li>";
					}
				?>
				</ul>
				<?php
					if ($opt_page !== false) {
						$opt_page["url"] = $c["url"];
						$opt_page["mini"] = 1;
						include "./system/tool/pagination.php";
					}
				?>
			</div>
		</div>
	</div>
	<br>
<?php if (!is_null($MembreActuel)): ?>
	<div id="partie-redaction" class="bloc-rediger">
		<div class="headerContent">
			<span>Rédigez votre réponse</span>
		</div>
		<form method="POST" class="subContentBorder">
			<input type="hidden" name="idquestion" value="<?=$idquestion?>"/>
			<textarea name="message"></textarea>
			<script src="./javascript/nicEdit/nicEdit.js" type="text/javascript"></script>
			<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("question$idquestion")?>"/>
			<input type="submit" value="Publier la réponse" class="inputsubmit" />
		</form>
	</div>
<?php endif; ?>
</div>

<?php
endif;
?>