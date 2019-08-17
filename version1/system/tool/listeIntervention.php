<?php
$list = (new Vue())->listeIntervention();

if (0 < count($list)): ?>
	<div class="sidebloc">
		<div class="headerContent">
			<span>Mes interventions</span>
		</div>
		<div class="list">
			<ul>
			<?php
				$list = (new Vue())->listeIntervention();

				foreach ($list as $c12l) {
					if (isset($c12l["entete"])) {
						echo "<li class=\"entete\">{$c12l["entete"]}</li>";
					} else {
						echo "<li class=\"{$c12l["msuivi"]}\"><a href=\"{$c12l["url"]}\" title=\"{$c12l["ilya"]}.\">{$c12l["question"]}</a></li>";
					}
				}
			?>
			</ul>
			<hr>
			<center><p><a href="./<?=$_PAGENAME?>.html&amp;tri=mesinterventions" style="text-align:center;">Afficher plus d'intervention</a></p></center>
		</div>
	</div>
<?php
endif;
?>