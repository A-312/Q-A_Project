<?php
$taglist = array();
$actag = "";

if ($System->getGET("tag") != "") {
	$taglist_tmp = preg_split("#-#", $System->getGET("tag"));
	$taglist_tmp = array_unique($taglist_tmp);

	foreach ($taglist_tmp as $tag) {
		if (preg_match("#^(\w+)$#", $tag)) {
			$tab = $BasedeDonnee->lire("SELECT idtag FROM tag WHERE tag = ?", array($tag), BdD::FETCH);
			$taglist[] = $tab["idtag"];
			$actag .= (($actag != "") ? "-" : "").$tag;
		}
	}
}

if (!isset($_AFF_CATEGORIE)) { $_AFF_CATEGORIE = "question"; }

if ($_AFF_CATEGORIE == "refwiki") {
	$headertitle = (count($taglist) != 0) ? "Références & Wiki en fonction des tags selectionnés" : "Toutes les références";
} elseif ($_AFF_CATEGORIE == "projet") {
	$headertitle = (count($taglist) != 0) ? "Projets en fonction des tags selectionnés" : "Tous les projets";
} elseif ($_AFF_CATEGORIE == "news") {
	$headertitle = (count($taglist) != 0) ? "News en fonction des tags selectionnés" : "Toutes les news";
} else {
	$headertitle = (count($taglist) != 0) ? "Questions en fonction des tags selectionnés" : "Toutes les questions";
}

$urltri = "./$_PAGENAME.html&".(($actag != "") ? "tag=$actag&" : "" );
if ($System->getGET("tri") != "") {
	if ($System->getGET("tri") == "mesinterventions") {
		$headertitle = "Mes interventions";
		$tri = "mesinterventions";
	} else {
		$_SESSION["index-tri"] = (array_search($System->getGET("tri"), array("recent", "vote")) !== false) ? $System->getGET("tri") : "recent";
	}
} elseif (!isset($_SESSION["index-tri"])) {
	$_SESSION["index-tri"] = "recent";
}

$tri = (isset($tri)) ? $tri : $_SESSION["index-tri"];
?>
<table id="subContent">
	<tr>
		<td id="categoriequestion">
			<div class="headerContent">
				<span><?=$headertitle?></span>
				<ul>
					<li><a href="<?=$urltri?>tri=recent" <?=(($tri == "recent" || $tri == "mesinterventions") ? "class=\"here\"" : "")?>>Dernier</a></li>
					<li><a href="<?=$urltri?>tri=vote" <?=(($tri == "vote") ? "class=\"here\"" : "")?>>Top</a></li>
				</ul>
			</div>
			<table id="listquestion">
			<?php
				$opt_page = true;
				$list = (new Vue())->listeQuestion($taglist, $tri, $opt_page);
				if ($opt_page !== false && $opt_page["page"] != 1):
				$p = "./$_PAGENAME.html".((!empty($actag)) ? "&tag=$actag" : "").((!empty($tri)) ? "&tri=$tri" : "")."&page=".($opt_page["page"]-1);
			?>
				<tr class="top">
					<td colspan="2">
						<div class="pagination">
							<a href="<?=$p?>">Charger la page précédente</a>
						</div>
					</td>
				</tr>
			<?php
				endif;

				foreach ($list as $c):
			?>
				<tr class="question">
					<td class="stats">
						<?php foreach ($c["compteur"] as $x): ?>
							<div class="picstats"><span class="nbr"><?=$x[0];?></span><span><?=$x[1];?></span></div>
						<?php endforeach; ?>
						<p class="view"><span class="nbr"><?=$c["compteur"][2][0];?></span> <?=$c["compteur"][2][1];?></p>
					</td>
					<td class="qinfo">
						<h3><a href="<?=$c["url"]?>" title="<?=$c["ilya"];?>, par <?=$c["membre"]["pseudo"];?> <?=$c["membre"]["reputation"];?>."><?=$c["question"];?></a></h3>
						<div class="tags">
							<?php foreach ($c["tag"] as $nom): ?>
								<a class="tagbl" href="./<?=$_PAGENAME?>.html&amp;tag=<?=$nom;?>"><?=$nom;?></a>
							<?php endforeach; ?>
						</div>
						<?php if (!(isset($_AFF_CATEGORIE) && $_AFF_CATEGORIE == "refwiki")): ?>
							<div class="autor" title="<?=$c["date"];?>"><p><a href="<?=$c["membre"]["url"]?>"><span class="ilya"><?=$c["ilya"];?></span> <?=$c["membre"]["pseudo"];?> <span class="arank">[<?=$c["membre"]["reputation"];?>]</span></a></p></div>
						<?php else: ?>
							<div class="autor" title="<?=$c["date"];?>"><p><span class="ilya">Références &amp; Wiki</span></p></div>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ($opt_page !== false): ?>
				<tr class="foot">
					<td colspan="2">
						<?php
							$opt_page["url"] = "./$_PAGENAME.html".((!empty($actag)) ? "&tag=$actag" : "").((!empty($tri)) ? "&tri=$tri" : "");
							include "./system/tool/pagination.php";
						?>
					</td>
				</tr>
			<?php endif; ?>
			</table>
		</td>
		<td id="sidebar">
			<div class="sidenumber">
				<div>712</div>
				<p>questions<p>
			</div>
		<?php if (!is_null($MembreActuel) && count($taglist) == 0) { include "./system/tool/listeIntervention.php"; } ?>
		<?php if (count($taglist) != 0): ?>
			<div class="sidebloc">
				<div class="headerContent">
					<span>Tags selectionnés</span>
				</div>
				<div class="list">
				<?php
					$list = (new Vue())->listeTag($taglist, 2);

					foreach ($list as $c) {
						echo "<p><a class=\"tagbl\" href=\"./$_PAGENAME.html&tag={$c["tag"]}\">{$c["tag"]}</a> <a href=\"./tag-{$c["tag"]}.html\">info >></a></p>";
					}
				?>
					<hr>
					<center><p><a href="./<?=$_PAGENAME?>.html<?=((!empty($tri)) ? "&tri=$tri" : "")?>" style="text-align:center;">Supprimer la selection</a></p></center>
				</div>
			</div>
		<?php endif; ?>
			<div class="sidebloc">
				<div class="headerContent">
					<span>Tags les plus actifs</span>
				</div>
				<div class="list">
				<?php
					$list = (new Vue())->listeTag($taglist);
					$actag .= (($actag != "") ? "-" : "");

					foreach ($list as $c) {
						echo "<p><a class=\"tagbl\" href=\"./$_PAGENAME.html&tag=$actag{$c["tag"]}\">{$c["tag"]}</a> <span>x{$c["nombre"]}</span></p>";
					}
				?>
					<hr>
					<p>*Cliquez sur un tag pour affiner votre recherche.</p>
				</div>
			</div>
		<?php if (!is_null($MembreActuel) && count($taglist) != 0) { include "./system/tool/listeIntervention.php"; } ?>
		</td>
	</tr>
</table>