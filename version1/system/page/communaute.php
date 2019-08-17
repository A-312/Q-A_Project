<?php
$tri = (array_search($System->getGET("tri"), array("membre", "groupe")) !== false) ? $System->getGET("tri") : "membre";
?>
<div id="subContent">
	<div id="listebloc">
		<div class="headerContent">
			<span>Liste des <?=$tri."s"?></span>
			<ul>
				<li><a href="./communaute.html&amp;tri=membre" <?=(($tri == "membre") ? "class=\"here\"" : "")?>>Membres</a></li>
				<li><a href="./communaute.html&amp;tri=groupe" <?=(($tri == "groupe") ? "class=\"here\"" : "")?>>Groupes</a></li>
			</ul>
		</div>
		<div class="subContentBorder">
			<form id="search" method="POST">
				<p>Les <?=$tri."s"?> les plus actifs les 7 derniers jours.</p>
				<div id="searchbloc">
					<label for="term">Rechercher :</label>
					<input type="text" name="term" class="inputtext ui-for-pseudo-it-is-me" id="term" />
					<a onclick="this.parentNode.parentNode.submit();">Chercher &gt;&gt;</a>
				</div>
			</form>
			<div id="list">
			<?php
				$term = ($System->getPOST("term") != "") ? $System->getPOST("term") : (($System->getGET("term") != "") ? $System->getGET("term") : false);
				$opt_page = true;
				$list = (new Vue())->listeMembre(($term) ? $term : "", $opt_page);
				foreach ($list as $c) {
					$c["desc"] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi cursus purus nec est aliquam fermentum. Pellentesque sed.";
					echo "<div class=\"picttag\"><p class=\"header\"><a class=\"tagbl\" href=\"{$c["url"]}\">{$c["pseudo"]}</a> <span>{$c["reputation"]}</span></p>";
					echo "<p class=\"content\">{$c["desc"]}</p><a class=\"plusdinfo\" href=\"{$c["url"]}\">Plus d'information</a></div>";
				}
			?>
			</div>
			<?php
				if ($opt_page !== false) {
					$opt_page["url"] = "./communaute.html".(($term) ? "&term=".$term : "");
					include "./system/tool/pagination.php";
				}
			?>
		</div>
	</div>
</div>