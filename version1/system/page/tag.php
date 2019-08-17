<?php
if ($System->getGET("tag") != "" && $tag = $BasedeDonnee->Lire("SELECT tag FROM tag WHERE tag = ?", array($System->getGET("tag")), "tag")):

if ($System->getPOST("description") != "" && $System->getPOST("texte") != "") {
	$session = substr(htmlspecialchars(strtolower($System->getPOST("session"))),0,60);
	if (is_null($MembreActuel)) {
		echo "Et si tu te connecté ?";
	} elseif (!$System->verifierFormulaire("tag$tag", $session)) {
		echo "Le formulaire a expiré.";
	} else {		
		$description = (new Parser())->parseHTML($System->getPOST("description"));
		list($erreur, $texte) = (new Parser())->parseBadHTML($System->getPOST("texte"));
		if ($erreur) {
			echo "Erreur : ".$texte;
		} elseif ($BasedeDonnee->lire("SELECT COUNT(*) AS nombre FROM tag WHERE tag = ? AND (description <> ? OR texte <> ?)", array($tag, $description, $texte), "nombre") == 0) {
			echo "Rien a été modifié.";
		} else {
			$BasedeDonnee->ecrire("UPDATE tag SET description = ?, texte = ? WHERE tag = ?", array($description, $texte, $tag));
		}
	}
}

$t_tag = $BasedeDonnee->lire("SELECT description, texte FROM tag WHERE tag = ?", array($tag), BdD::FETCH);

(($description = $t_tag["description"]) || ($description = "Description..."));
(($texte = $t_tag["texte"]) || ($texte = "Texte..."));
?>
<div id="subContent">
	<div>
		<div class="headerContent">
			<span>Informations sur le tag "<?=$tag?>"</span>
			<ul>
				<li><a href="./tag-<?=$tag?>.html" class="here">Info Tag</a></li>
			<?php
				$list = array(
					array("Questions", "question"),
					array("News", "news"),
					array("Projets", "projet"),
					array("Références &amp; Wiki", "refwiki")
				);
				$_PAGENAME = ($System->getGET("page") != "") ? $System->getGET("page") : "index";

				if ($_PAGENAME == "question") { $_PAGENAME = ""; }

				foreach ($list as $c) {
					$url = "./$c[1].html&tag=$tag";
					echo "<li><a href=\"$url\">$c[0]</a></li>";
				}
			?>
			</ul>
		</div>
		<div class="subContentBorder" style="padding:10px 10px 3px">
			<div id="description" style="min-height:15px;"><?=$description?></div>
			<hr>
			<div id="texte"><?=$texte?></div>

		<?php if(!is_null($MembreActuel)): ?>
			<div align="right"><a onclick="goModifier(this)">Modifier</a></div>
			<form id="formulaire" method="POST" style="display:none;" onsubmit="return submitModif();">
				<input type="hidden" name="tag" value="<?=$tag?>"/>
				<input type="hidden" name="description" />
				<input type="hidden" name="texte" />
				<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("tag$tag")?>"/>
				<input type="submit" value="Confirmer la modification" class="inputsubmit" />
			</form>
		<?php endif; ?>
		</div>
	</div>
</div>
<?php if(!is_null($MembreActuel)): ?>
<script src="./javascript/nicEdit/nicEdit.js" type="text/javascript"></script>
<script>
function goModifier(o) {
	document.getElementById("formulaire").style.display = "inherit";
	o.style.display = "none";
	document.getElementById("description").setAttribute('contenteditable', true);
	new nicEditor({fullPanel : true}).panelInstance("texte", {hasPanel : true});
	document.getElementById("description").focus();
}
function submitModif() {
	var divdesc = document.getElementById("description");
	var description = divdesc.textContent || divdesc.innerText || '';
	document.getElementsByName("description")[0].value = description;
	document.getElementsByName("texte")[0].value = nicEditors.findEditor("texte").elm.innerHTML;

	return true;
}
</script>
<?php endif; ?>
<?php
else:
$term = "";
$b = (($System->getPOST("term") != "" && $term = $System->getPOST("term")) || ($System->getGET("term") != "" && $term = $System->getGET("term")));
$headertitle = (!empty($term)) ? "Tous les tags avec \"$term\"" : "Tous les tags";
?>
<div id="subContent">
	<div id="listebloc">
		<div class="headerContent">
			<span><?=$headertitle?></span>
		</div>
		<div class="subContentBorder">
			<form id="search" method="POST">
				<p>Les tags sont des mots-clés permettant de catégoriser simplement les différents sujets abordés.</p>
				<div id="searchbloc" class="I-search-tag">
					<label for="term">Tag :</label>
					<input type="text" name="term" class="inputtext" id="term" value="<?=$term?>"/>
					<a onclick="this.parentNode.parentNode.submit();">Chercher &gt;&gt;</a>
				</div>
			</form>
			<div id="list">
			<?php
				$opt_page = true;
				$list = (new Vue())->listeTag((!empty($term)) ? $term : array(), 3, 36, $opt_page);

				foreach ($list as $c) {
					echo "<div class=\"picttag\"><p class=\"header\"><a class=\"tagbl\" href=\"./tag-{$c["tag"]}.html\">{$c["tag"]}</a> <span>x{$c["nombre"]}</span></p>";
					echo "<p class=\"content\">{$c["description"]}</p><a class=\"plusdinfo\" href=\"./tag-{$c["tag"]}.html\">Plus d'information</a></div>";
				}
			?>
			</div>
			<?php if ($opt_page !== false): ?>
				<?php
					$opt_page["url"] = (!empty($term)) ? "./$_PAGENAME.html&term=$term" : "";
					include "./system/tool/pagination.php";
				?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
endif;
?>