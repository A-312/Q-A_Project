<?php
$type = $System->getGET("type");
if (is_null($MembreActuel) || empty($type) || array_search($type, array("question", "news", "projet", "refwiki")) === false):
?>
<div id="subContent">
	<div id="formContent">
		<div class="headerContent">
			<span>Rédigez votre <?=($type=="refwiki")?"Références":"refwiki"?></span>
		</div>
		<div class="subContentBorder">
			<span><?=is_null($MembreActuel) ? "Et si tu te connectais ?" : "Type incconu."?></span>
		</div>
	</div>
</div>
<?php
elseif ($System->getPOST("question") != "" && $System->getPOST("tag") != "" && $System->getPOST("message") != ""):
	$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);

	$question = (new Parser())->parseHTML($System->getPOST("question"));
	list($erreur, $message) = (new Parser())->parseBadHTML($System->getPOST("message"));
	$type = $System->getGET("type");

	if (is_null($MembreActuel)) {
		$infomsg = "Et si tu te connectais ?";
	} elseif (array_search($type, array("question", "news", "projet", "refwiki")) === false) {
		$infomsg = "Type inconnu.";
	} elseif (!$System->verifierFormulaire("poserunequestion", $session)) {
		$infomsg = "Le formulaire a expiré.";
	} elseif ($System->getPOST("tag") == "") {
		$infomsg = "Aucun tag !";
	} elseif ($erreur) {
		$infomsg = "Erreur : ".$message;
	} elseif ($BasedeDonnee->lire("SELECT COUNT(*) AS nombre FROM question, message WHERE question.idquestion = message.idquestion AND question LIKE ? AND texte LIKE ?", array($question, $message), "nombre") != 0) {
		$infomsg = "Oh ! Cette question existe déjà.";
	} else {
		$taglist_tmp = preg_split("#(\s*),(\s*)#", strtolower($System->normalize($System->getPOST("tag"))));
		$taglist_tmp = array_unique($taglist_tmp);

		$taglist = array();

		foreach ($taglist_tmp as $tag) {
			if (preg_match("#^(\w+)$#", $tag)) {
				$tab = $BasedeDonnee->lire("SELECT idtag FROM tag WHERE tag = ?", array($tag), BdD::FETCH);
				if (empty($tab["idtag"])) {
					$BasedeDonnee->ecrire("INSERT INTO tag(tag) VALUES (?)", array($tag));
					$tab["idtag"] = $BasedeDonnee->lire("SELECT LAST_INSERT_ID() AS lastInsertID", array(), BdD::FETCH)["lastInsertID"];
				}
				$taglist[] = $tab["idtag"];
			}
		}

		if (count($taglist) != 0 && count($taglist) <= 6) {
			$BasedeDonnee->ecrire("INSERT INTO question(question, categorie) VALUES (?, ?)", array($question, $type));
			$idquestion = $BasedeDonnee->lire("SELECT LAST_INSERT_ID() AS lastInsertID", array(), BdD::FETCH)["lastInsertID"];
			$BasedeDonnee->ecrire("INSERT INTO message(idquestion, texte, type, idmembre, dateenvois, dateedition) VALUES (?, ?, ?, ?, ?, ?)", array($idquestion, $message, "question", $MembreActuel->getidmembre(), time(), time()));

			foreach ($taglist as $idtag) {
				$BasedeDonnee->ecrire("INSERT INTO questtag(idquestion, idtag) VALUES (?, ?)", array($idquestion, $idtag));
			}
		} else {
			$infomsg = "Aucun tag ou trop de tag !";
		}
	}
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
	endif;
else:
?>
<div id="subContent">
	<div id="redactionquestion" class="I-write-my-answer">
		<div class="headerContent">
			<span>Rédigez votre <?=($type=="refwiki")?"Références":"refwiki"?></span>
		</div>
		<form id="sredaction" method="POST">
			<span>Ce formulaire est temporaire, l'affichage des erreurs et des options est encore très brut.</span>
			<h2>Question :</h2>
			<input type="text" name="question" class="inputtext"/>
			<h2>Tag :</h2>
			<input type="text" name="tag" class="inputtext" id="tagquestion"/>
			<h2>Message :</h2>

			<div style="margin:auto;width:90%;">
				<textarea name="message" style="width:100%;min-height:250px;"><?=htmlentities(($System->getPOST("message") != "")?$System->getPOST("message"):"")?></textarea>
			</div>

			<script src="./javascript/nicEdit/nicEdit.js" type="text/javascript"></script>

			<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("poserunequestion")?>"/>
			<input type="submit" value="Publier la question" class="inputsubmit" />
		</form>
	</div>
</div>
<?php
endif;
?>