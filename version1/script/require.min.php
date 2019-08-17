<?php
include("minify/php-closure.php");
ob_start("ob_gzhandler");

$list = [
	"javascript/question.js",
	"javascript/site.js"
];

$c = new PhpClosure();

foreach ($list as $file) {
	$c->add("../".$file);
}

echo "var js_is_minify = ('".join(" ", $list)."').split(' ');\n";

$c->hideDebugInfo();
$c->cacheDir("minify/tmp/");

$c->write();
?>