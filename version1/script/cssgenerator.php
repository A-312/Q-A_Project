<?php
include_once "../system/lessc.inc.php";

header("Content-Type: text/css; charset=utf-8");

$chemin = (isset($_GET["c"]) && is_string($_GET["c"])) ? $_GET["c"] : "";

$chemin_less = "../$chemin.less";

echo "/* $chemin.less */\n";

if (array_search($chemin, array("design/style")) !== false && file_exists($chemin_less)) {
	$lessc = new lessc($chemin_less);
	echo $lessc->parse();
} else {
	header("HTTP/1.0 404 Not Found");
}

?>
