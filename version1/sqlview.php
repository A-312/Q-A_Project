<?php
include "system/class/class.BdD.php";
$BasedeDonnee = new BdD("localhost", "sdd_old", "sdd", "CYyqsaxVP3dMJDRU");

$listtable = $BasedeDonnee->lire("SHOW TABLES");

$BasedeDonnee->ecrire("USE INFORMATION_SCHEMA");
$listrelation = $BasedeDonnee->lire("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME IS NOT NULL AND CONSTRAINT_SCHEMA = ?",
										array("sdd_old"));
$BasedeDonnee->ecrire("USE sdd_old");

foreach ($listtable as $cle => $tab) {
	$list[$tab["Tables_in_sdd_old"]] = $BasedeDonnee->lire("DESC {$tab["Tables_in_sdd_old"]}");
	$listtable[$cle]["count"] = $BasedeDonnee->lire("SELECT COUNT(*) as count FROM {$tab["Tables_in_sdd_old"]}", array(), "count");
}

$listrel = array();
foreach ($listrelation as $tab) {
	if (!isset($listrel[$tab["TABLE_NAME"]])) {
		$listrel[$tab["TABLE_NAME"]] = array();
	}

	$listrel[$tab["TABLE_NAME"]][$tab["COLUMN_NAME"]] = array($tab["REFERENCED_TABLE_NAME"], $tab["REFERENCED_COLUMN_NAME"]);
}

function getrelation($t, $c) {
	global $listrel;
	return (isset($listrel[$t]) && isset($listrel[$t][$c])) ? $listrel[$t][$c] : null;
}

function setpathrelation($t, $c) {
	global $BasedeDonnee;
	$d = getrelation($t, $c);
	if (is_null($d)) { return null; }
	return "<a href=\"#$d[0].$d[1]\">=> [ ]</a>";
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>SQLView</title>
	<link rel="stylesheet/less" type="text/css" href="./sqlview.less">
	<script src="./javascript/lib/less.js"></script>
</head>
<body>
<?php foreach ($listtable as $x): ?>
	<div class="table">
		<div><?=$x["Tables_in_sdd_old"]?> (<?=$x["count"]?>)</div>
		<ul>
		<?php
		foreach ($list[$x["Tables_in_sdd_old"]] as $t):
			$d = setpathrelation($x["Tables_in_sdd_old"], $t["Field"]);
		?>
			<li id="<?=$x["Tables_in_sdd_old"].".".$t["Field"]?>" title="<?=$t["Key"]?> <?=$t["Extra"]?>">
				<span class="<?=$t["Key"]?> <?=$t["Extra"]?>"><?=$t["Field"]?></span> <?=$d?>
				<br><font><?=$t["Type"]?></font>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
<?php endforeach; ?>
	<a id="phpMyAdmin" href="http://localhost/phpmyadmin/index.php?db=sdd">phpMyAdmin => [ ]</a>
</body>
</html>