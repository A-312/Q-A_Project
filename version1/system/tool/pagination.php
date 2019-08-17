<div class="pagination">
<?php
	$list = (new Vue())->pagination($opt_page["nbr"], $opt_page["page"]);
	$p_url = (!empty($opt_page["url"])) ? $opt_page["url"] : "./$_PAGENAME.html";
	$b12l = (isset($opt_page["mini"]) && $opt_page["mini"]) ? true : false;
	foreach ($list as $c12l) {
		if (is_int($c12l)) {
			echo (($c12l == $opt_page["page"]) ? "<a class=\"here\">" : "<a href=\"$p_url&page=$c12l\">").$c12l."</a>";
		} elseif (is_string($c12l)) {
			echo "<span>$c12l</span>";
		} elseif ($c12l[0] == "prec") {
			echo "<a href=\"$p_url&page={$c12l[1]}\">".((!$b12l)?"« Précédent":"&lt;")."</a>";
		} elseif ($c12l[0] == "suiv") {
			echo "<a href=\"$p_url&page={$c12l[1]}\">".((!$b12l)?"Suivant »":"&gt;")."</a>";
		}
		echo " ";
	}
?>
</div>