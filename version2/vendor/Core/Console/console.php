<?php
ini_set("error_log", "./logs/php-error.log");
ini_set("date.timezone", "Europe/Monaco");

if (!(2 <= count($argv) && is_string($argv[1]))) {
	die("Aucun parametre.");
}

function rmdir_recursive($dir) {
	foreach (scandir($dir) as $file) {
		if ('.' === $file || '..' === $file) continue;
		if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
		else unlink("$dir/$file");
	}
	rmdir($dir);
}
echo "\n";
switch ($argv[1]):
	case "cache:clear":
		$COM = new COM("scripting.filesystemobject");
		$dir = array_map("realpath", glob("./cache/*"));
		$total = 0;
		if (is_dir($m = realpath("./../res/cache/"))) {
			$dir[] = $m;
		}

		if (count($dir) == 0) { die("Aucun fichier cache present."); }

		$mask = " %25s | %-40s \n";
		printf($mask, " [ Taille ]  ", "  [ Nom ] ");
		foreach ($dir as $element) {
			if(is_file($element)) {
				$size = filesize($element);
				unlink($element);
			} else {
				$size = (is_object ($COM)) ? $COM->getfolder($element)->size : 0;
				rmdir_recursive($element);
			}
			$total += $size;
			$size = number_format($size, 0, ".", " ")." octects";
			printf($mask, "- ".$size, $element);
		}
		$unit = array("O","KO","MO","GO","TO","PO");
		$size = @round($total/pow(1024,($i=floor(log($total,1024)))),2)." ".$unit[$i];
		$total = number_format($total, 0, ".", " ")." octects";
		printf($mask, "", "");
		printf($mask, "= ".$total, "Le cache a ete nettoye ($size).");
		break;
	case "ftp":
		require_once __DIR__."/ftp.php";

		break;
	default:
		print "Commande inconnu.";
endswitch;