<?php
function scandir_recursiveB(&$map, $path) {
	$map = array("type"=>"dir", "mtime"=>0, "child"=>array(), "path"=>$path);
	$dir = implode("/", $path); $dirmdtm = 0;
	foreach (scandir($dir) as $name) {
		if ($name == "." or $name == "..") {
			continue;
		}
		if (is_dir("$dir/$name")) {
			$fd = scandir_recursiveB($map["child"][$name], array_merge($path, [$name]));
		} else {
			$fd = filemtime("$dir/$name");
			$map["child"][$name] = array("type"=>"file", "mtime"=>$fd, "path"=>array_merge($path, [$name]));
		}
		$dirmdtm = max($dirmdtm, $fd);
	}
	return $map["mtime"] = $dirmdtm;
}

function ftp_isdir($conn_id, $path) {
	$origin = ftp_pwd($conn_id);
	$bool = @ftp_chdir($conn_id, $path);
	ftp_chdir($conn_id, $origin);
	return ($bool === true);
}

function scandir_recursive($dir, &$map) {
	$origin = getcwd();
	chdir($dir);
	scandir_recursiveB($map, array("."));
	chdir($origin);
}

function ftp_scandir_recursiveB($conn_id, &$map, $path) {
	$map = array("type"=>"dir", "mtime"=>0, "child"=>array(), "path"=>$path);
	$dir = implode("/", $path); $dirmdtm = 0;
	$origin = ftp_pwd($conn_id);
	ftp_chdir($conn_id, $dir);
	foreach (ftp_perfectrawlist($conn_id, ftp_pwd($conn_id)) as $file) {
		$name = $file["name"];
		if ($name == "." or $name == "..") {
			continue;
		}
		if ($file["isdir"]) {
			ftp_chdir($conn_id, $origin);
			$fd = ftp_scandir_recursiveB($conn_id, $map["child"][$name], array_merge($path, [$name]));
			ftp_chdir($conn_id, $dir);
		} else {
			$fd = ftp_mdtm($conn_id, $name);
			$map["child"][$name] = array("type"=>"file", "mtime"=>$fd, "path"=>array_merge($path, [$name]));
		}
		$dirmdtm = max($dirmdtm, $fd);
		echo ".";
	}
	ftp_chdir($conn_id, $origin);
	return $map["mtime"] = $dirmdtm;
}

function ftp_scandir_recursive($conn_id, $dir, &$map) {
	$origin = ftp_pwd($conn_id);
	ftp_chdir($conn_id, $dir);
	echo "Scan : [";
	ftp_scandir_recursiveB($conn_id, $map, array("."));
	echo "]\n";
	ftp_chdir($conn_id, $origin);
}

function scanarray_recursive($map) {
	foreach ($map["child"] as $name => $file) {
		echo implode("/", $file["path"])."\n";
		if ($file["type"] == "dir" && count($file["path"]) <= 1) {
			scanarray_recursive($file);
		}
	}
}

function ftp_connexion() {
	try {
		echo "p:";
		$stdin = fopen("php://stdin", "r");
		$password = trim(fgets($stdin));
		echo "\n";
		$conn_id = @ftp_connect("ftp.mywebsite.fr");
		if (!$conn_id || !@ftp_login($conn_id, "user_ftp", "$password")) { throw new Exception(); }
		return $conn_id;
	} catch (Exception $e) {
		die("Erreur de connexion");
		return false;
	}
}

function ftp_perfectrawlist($conn_id, $dir) {
	$output = array();
	foreach (ftp_rawlist($conn_id, $dir) as $current) {
		$split = preg_split("[ ]", $current, 9, PREG_SPLIT_NO_EMPTY);
		$year = (strrchr($split[7], ":") === false) ? $split[7] : (strtotime("{$split[5]} {$split[6]}")>time()) ? date("Y")-1 : date("Y");
		$time = (strrchr($split[7], ":") !== false) ? $split[7] : "00:00";
		$output[] = array(
			"name" => preg_filter("/^(.*)(( -> )(.*))?$/U", "$1", $split[8]),
			"isdir" => ($split[0] {0} === "d"),
			"mdtm" => strtotime($split[5]." ".$split[6]." ".$year." ".$time),
			"size" => $split[4]
			// read more : http://bit.ly/1cbscUu
		);
	}
	return !empty($output) ? $output : FALSE;
}

//								local   ftp
function comparearray_recursive($map1, $map2, $lastlocalMap, $lastftpMap, &$finalMap, $path = null) { // $l1["mtime"] != $l2["mtime"] => "_MAJ"
	if (!is_array($path)) { $path = ($finalMap["path"])?:array("."); }
	$b1 = is_array($map1); $b2 = is_array($map2);
	$keys = array_unique(array_merge(($b1) ? array_keys($map1["child"]) : array(), ($b2) ? array_keys($map2["child"]) : array()));

	foreach ($keys as $name) {
		$c1 = ($b1 && isset($map1["child"]) && isset($map1["child"][$name])); $c2 = ($b2 && isset($map2["child"]) && isset($map2["child"][$name]));
		$c3 = (is_array($lastlocalMap) && isset($lastlocalMap["child"]) && isset($lastlocalMap["child"][$name]));
		$c4 = (is_array($lastftpMap) && isset($lastftpMap["child"]) && isset($lastftpMap["child"][$name]));
		$l1 = ($c1) ? $map1["child"][$name] : null; $l2 = ($c2) ? $map2["child"][$name] : null;
		$l3 = ($c3) ? $lastlocalMap["child"][$name] : null; $l4 = ($c4) ? $lastftpMap["child"][$name] : null;
		
		$file = ($c1) ? $l1 : $l2;

		$btime = ($c1 && $c2) && ($c3 && $l1["mtime"] == $l3["mtime"]) && ($c4 && $l2["mtime"] == $l4["mtime"]);

		$element = array("type" => $file["type"], "mtime" => $file["mtime"], "path" => array_merge($path, [$name]),
						"localpath" => ($c1) ? $l1["path"] : null,
						"ftppath" 	=> ($c2) ? $l2["path"] : null,
						"etat" => ($c1) ? (($c2) ? (($btime) ? "IDEN" : "_MAJ") : "NOUV") : "INCO");

		$finalMap["child"][$name] = $element;

		if ($file["type"] == "dir") { //&& count($path) < 100) {
			$finalMap["child"][$name]["child"] = array(); 
			comparearray_recursive($l1, $l2, $l3, $l4, $finalMap["child"][$name], array_merge($path, [$name]));
		}
	}
}

function fastcomparearray_recursive($map1, $lastlocalMap, &$finalMap, $path = null) {
	if (!is_array($path)) { $path = ($finalMap["path"])?:array("."); }
	$b1 = is_array($map1); $b3 = is_array($lastlocalMap);
	$keys = array_unique(array_merge(($b1) ? array_keys($map1["child"]) : array(), ($b3) ? array_keys($lastlocalMap["child"]) : array()));

	foreach ($keys as $name) {
		$c1 = ($b1 && isset($map1["child"]) && isset($map1["child"][$name]));
		$c3 = ($b3 && isset($lastlocalMap["child"]) && isset($lastlocalMap["child"][$name]));
		$l1 = ($c1) ? $map1["child"][$name] : null; $l3 = ($c3) ? $lastlocalMap["child"][$name] : null;
		
		$file = ($c1) ? $l1 : $l3;

		$btime = $c1 && ($c3 && $l1["mtime"] == $l3["mtime"]);

		$element = array("type" => $file["type"], "mtime" => $file["mtime"], "path" => array_merge($path, [$name]),
						"localpath" => ($c1) ? $l1["path"] : null,
						"ftppath" 	=> array_merge($path, [$name]),
						"etat" => ($c1) ? (($c3) ? (($btime) ? "IDEN" : "_MAJ") : "NOUV") : "INCO");

		$finalMap["child"][$name] = $element;

		if ($file["type"] == "dir") { //&& count($path) < 100) {
			$finalMap["child"][$name]["child"] = array(); 
			fastcomparearray_recursive($l1, $l3, $finalMap["child"][$name], array_merge($path, [$name]));
		}
	}
}

function removeftp_recursiveB($conn_id, $map) {
	foreach ($map["child"] as $name => $file) {
		if ($file["type"] == "dir") {
			removeftp_recursiveB($conn_id, $file);
		} else {
			ftp_remove($conn_id, $file);
		}
	}
	ftp_remove($conn_id, $map);
}

function ftp_remove($conn_id, $file) {
	$path = implode("/", $file["path"]); $path = ($path == ".") ? ftp_pwd($conn_id) : $path;
	if (($file["type"] == "dir" && ftp_rmdir($conn_id, $path))
		|| ftp_delete($conn_id, $path)) {
		global $get_contents;
		echo $line = "RM $path\n";
		$get_contents .= $line;
	} else { echo "Impossible d'effacer le ".(($file["type"]=="dir")?"dossier":"fichier")." $path.\n"; }
}

function sizeformat($size) {
	$unit = array("O","KO","MO","GO","TO","PO");
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2)." ".$unit[$i];
}

function ftp_add($conn_id, $file) {
	$path = implode("/", $file["path"]); $s = 0;
	$localpath = implode("/", $file["localpath"]);
	if (($file["type"] == "dir" && ftp_mkdir($conn_id, $path))
		|| ftp_put($conn_id, $path, $localpath, FTP_BINARY)) {
		global $get_contents, $get_totalsize;
		echo $line = "MK $path ".(($file["type"]!="dir")?"[".sizeformat($s = filesize($localpath))."]":"")."\n";
		$get_contents .= $line; $get_totalsize += $s;
	} else { echo "Impossible de creer le ".(($file["type"]=="dir")?"dossier":"fichier")." $path.\n"; }
}

function ftp_update($conn_id, $file) {
	$path = implode("/", $file["path"]);
	$localpath = implode("/", $file["localpath"]);
	if ($file["type"] == "dir") {
		global $get_contents;
		echo $line = "OK $path\n";
		$get_contents .= $line;
	} elseif (ftp_put($conn_id, $path, $localpath, FTP_BINARY)) {
		global $get_contents, $get_totalsize;
		echo $line = "UP $path [".sizeformat($s = filesize($localpath))."]\n";
		$get_contents .= $line; $get_totalsize += $s;
	} else { echo "Impossible de mettre à jour le fichier $path.\n"; }
}

function ftp_askbefore_rm($conn_id, $file, $dir = null) {
	if ($dir !== null) { $origin = ftp_pwd($conn_id); ftp_chdir($conn_id, $dir); }
	$path = implode("/", $file["path"]);
	if ($path == ".") { $path = $dir; }
	if (basename($path) != "cache") {
		echo "Le fichier [$path] n'existe pas en local, voulez-vous le supprimer ? (O/N)\nReponse : ";
		$stdin = fopen("php://stdin", "r");
		$choix = trim(fgets($stdin));
		$C = $choix == "O" || $choix == "o";
	} else {
		echo "Fichier [$path] automatiquement supprime.";
		$C = true;
	}
	echo "\n";
	if ($C) {
		if ($file["type"] == "dir") {
			removeftp_recursiveB($conn_id, $file);
		} else {
			ftp_remove($conn_id, $file);
		}
	}
	if ($dir !== null) { ftp_chdir($conn_id, $origin); }

	return $C;
}

function updateftp_recursiveB($conn_id, $map, $newMap, $fast_mode) {
	$newMap = array("type"=>"dir", "mtime"=>0, "child"=>array(), "path"=>$map["path"]);
	$tempMap = array();
	global $get_contents;
	foreach ($map["child"] as $name => $file) {
		$path = implode("/", $file["path"]);
		echo $line = "> {$file["etat"]} $path\n";
		$get_contents .= $line;
		switch ($file["etat"]) {
			case "INCO":
				ftp_askbefore_rm($conn_id, $file);
				unset($map["child"][$name]);
				$file = null;
				break;
			case "NOUV":
				ftp_add($conn_id, $file);
				break;
			case "_MAJ":
				ftp_update($conn_id, $file);
				break;
			case "IDEN":
				echo $line = "OK $path\n";
				$get_contents .= $line;
				break;
			default:
				echo $line = "/!\ ETRANGE ! [$path]\n";
				$get_contents .= $line;
				break;
		}
		if ($file["type"] == "dir") {
			$tempMap[$name] = updateftp_recursiveB($conn_id, $file, $newMap, $fast_mode);
		}
	}

	if (!$fast_mode && $newMap["child"]) {
		$path = $map["path"]; $dirmdtm = 0;
		$origin = ftp_pwd($conn_id);
		ftp_chdir($conn_id, implode("/", $path));
		foreach (ftp_perfectrawlist($conn_id, ftp_pwd($conn_id)) as $file) {
			$name = $file["name"];
			if ($name == "." or $name == "..") {
				continue;
			}
			$newMap["child"][$name] = ($file["isdir"]) ? $tempMap[$name] : array("type"=>"file", "mtime"=>ftp_mdtm($conn_id, $name), "path"=>array_merge($path, [$name]));
			$dirmdtm = max($dirmdtm, $newMap["child"][$name]["mtime"]);
		}
		ftp_chdir($conn_id, $origin);
		$newMap["mtime"] = $dirmdtm;
	}
	return $newMap;
}

function updateftp_recursive($conn_id, $dir, $map, &$newMap, $fast_mode = false) {
	$origin = ftp_pwd($conn_id);
	ftp_chdir($conn_id, $dir);
	$newMap = updateftp_recursiveB($conn_id, $map, $newMap, $fast_mode);
	ftp_chdir($conn_id, $origin);
}

function make_tempfile($content, $hash = null) {
	$hash = hash("sha256", $hash ?: uniqid());
	$fullpath = "./app/cache/temp/".substr($hash, 0, 2)."/".substr($hash, 2, 2)."/".substr($hash, 4).".php";
	$dir = dirname($fullpath);

	if (!is_dir($dir)) { mkdir($dir, 0777, true); }
	file_put_contents($fullpath, $content);

	return $fullpath;
}

/* ----------------------------------------- */

try {
	$start_time = microtime(true);
	$get_totalsize = 0;
	$get_contents = "";
	$fast_mode = false;
	if(isset($argv[2]) && is_string($argv[2])) {
		if ($argv[2] == "fast") {
			$fast_mode = true;
		} else {
			die("Option innconnu.");
		}
	}
	chdir("..");

	$conn_id = ftp_connexion();

	$localMap = array();

	scandir_recursive(".", $localMap);

	/* Ignore `./res/cache`, `./app/cache` and `./app/logs` folders */
	unset($localMap["child"]["res"]["child"]["cache"]);
	unset($localMap["child"]["app"]["child"]["cache"]);
	$localMap["child"]["app"]["child"]["logs"]["child"] = array();

	/* Make `./core` folder */
	$vitualMap["child"]["core"] = $vitualMap = array("type"=>"dir", "mtime"=>0, "child"=>array(), "path"=>$localMap["path"]);
	$vitualMap["child"]["core"]["path"] = array_merge($vitualMap["path"], ["core"]);

	/* Move `./app`, `./src`, `./vendor` folders */
	$replace = ["app", "src", "vendor"]; $dirmdtm = 0;
	for ($i=0;$i<count($replace);$i++) {
		$vitualMap["child"]["core"]["child"][$replace[$i]] = $localMap["child"][$replace[$i]];
		$dirmdtm = max($dirmdtm, $localMap["child"][$replace[$i]]["mtime"]);
	}
	$vitualMap["child"]["core"]["mtime"] = $dirmdtm;

	/* Make `./core/.htaccess` files */
	$name = ".htaccess";
	$vitualMap["child"]["core"]["child"][$name] = array("type"=>"file", "mtime"=>$dirmdtm,
				"path"=>explode("/", make_tempfile("Deny From All", "ftp ./core/.htaccess")));

	/* Put `./www`, `./res` folders */
	$add = ["www", "res"];
	for ($i=0;$i<count($add);$i++) {
		$vitualMap["child"][$add[$i]] = $localMap["child"][$add[$i]];
		$vitualMap["child"][$add[$i]]["path"] = array_merge($vitualMap["path"], [$add[$i]]);
	}

	/* Ignore `./www/app_dev.php` and `./res/res_dev.php` files */
	unset($vitualMap["child"]["www"]["child"]["app_dev.php"]);
	unset($vitualMap["child"]["res"]["child"]["res_dev.php"]);

	/* Modify `./www/app.php` and `./res/res.php` file */
	$modify = [["www", "app.php"], ["res", "res.php"]];
	for ($i=0;$i<count($modify);$i++) {
		$path = "./".implode("/", $modify[$i]);
		$content = str_replace("/../app/AppKernel.php", "/../core/app/AppKernel.php", file_get_contents($path));
		$vitualMap["child"][$modify[$i][0]]["child"][$modify[$i][1]]["path"] = explode("/", make_tempfile($content, realpath($path)));
	}

	/* Modify `./app/config/config.yml` by `./app/config/config_prodserver.yml` file */
	$vitualMap["child"]["core"]["child"]["app"]["child"]["config"]["child"]["config.yml"]
				= $vitualMap["child"]["core"]["child"]["app"]["child"]["config"]["child"]["config_prodserver.yml"];
	unset($vitualMap["child"]["core"]["child"]["app"]["child"]["config"]["child"]["config_prodserver.yml"]);
	/* ---- */

	/* Set maintenance and modify `./www/.htaccess` and `./res/.htaccess` files */
	$ar_mnt = ["www", "res"];
	for ($i=0;$i<count($ar_mnt);$i++) {
		$path = "./{$ar_mnt[$i]}/.htaccess";
		$content = str_replace("#mnt ", "", file_get_contents($path)); $temppath = explode("/", make_tempfile($content, realpath($path)));
		$vitualMap["child"][$ar_mnt[$i]]["child"][".htaccess"]["path"] = $temppath;
		ftp_update($conn_id, ["type" => "file", "path" => [$ar_mnt[$i], ".htaccess"], "localpath" => $temppath]);
	}
	/* ---- */

	$path = "app/logs/ftp/lastvirtualmap.php"; $lastvirtualmap = (is_file($path)) ? include $path : null;
	$path = "app/logs/ftp/lastftpmap.php"; $lastftpmap = (is_file($path)) ? include $path : null;

	$ftpMap = array(); $newftpMap = array();
	$watch = ["www", "res", "core"];
	for ($i=0;$i<count($watch);$i++) {
		$finalMap = array("type"=>"dir", "mtime"=>0, "child"=>array(), "path"=>array("."));
		if ($fast_mode) {
			fastcomparearray_recursive($vitualMap["child"][$watch[$i]], $lastvirtualmap["child"][$watch[$i]], $finalMap);
		} else {
			ftp_scandir_recursive($conn_id, "../".$watch[$i], $ftpMap[$watch[$i]]);
			comparearray_recursive($vitualMap["child"][$watch[$i]], $ftpMap[$watch[$i]],
							(is_array($lastvirtualmap)) ? $lastvirtualmap["child"][$watch[$i]] : null,
							(is_array($lastftpmap)) ? $lastftpmap[$watch[$i]] : null, $finalMap);
		}
		echo "\n\nSynchronisation :\n";
		updateftp_recursive($conn_id, "../".$watch[$i], $finalMap, $newftpMap[$watch[$i]], $fast_mode);
	}
	if ($fast_mode) {
		$watch = ["../core/app/cache", "../res/cache"];
		for ($i=0;$i<count($watch);$i++) {
			if (ftp_isdir($conn_id, $watch[$i])) {
				$ftpMap2 = array();
				ftp_scandir_recursive($conn_id, $watch[$i], $ftpMap2);
				ftp_askbefore_rm($conn_id, $ftpMap2, $watch[$i]);
			}
		}
	}
	$ar_mnt = ["www", "res"];
	for ($i=0;$i<count($ar_mnt);$i++) {
		ftp_update($conn_id, ["type" => "file", "path" => [$ar_mnt[$i], ".htaccess"], "localpath" => explode("/", realpath("./{$ar_mnt[$i]}/.htaccess"))]);
	}

	$dir = "app/logs/ftp/";
	if (!is_dir($dir)) { mkdir($dir, 0777, true); }
	file_put_contents("$dir/ftp.log", $get_contents);
	file_put_contents("$dir/lastvirtualmap.php", "<?php\r\nreturn ".var_export($vitualMap, true).";\r\n");
	if (!$fast_mode) { file_put_contents("$dir/lastftpmap.php", "<?php\r\nreturn ".var_export($newftpMap, true).";\r\n"); }

	echo "\nTotal size = ".sizeformat($get_totalsize)." (".round(microtime(true)-$start_time, 3)."s).\n";
} catch (Exception $e) {
	echo "Erreur.";
}
