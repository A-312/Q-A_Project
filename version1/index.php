<?php
$start = microtime(true);
include "system/main.php";

if (!empty($System->getGET["theme"])) {
	$_SESSION["theme"] =  $System->getGET("theme");
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>La 12zième communauté !</title>
	<meta name="requirejs.dir-js" content="./javascript/" />
	<link rel="stylesheet/less" type="text/css" href="./design/style.less?d=<?=time();?>">
	<?php if (isset($_SESSION["theme"]) && $_SESSION["theme"] == 2): ?>
		<link id="theme_NJ" title="theme_NJ" rel="stylesheet/less" type="text/css" href="./design/style_large.less?d=<?=time();?>">
	<?php endif; ?>
	<noscript><link rel="stylesheet" type="text/css" href="./design/style.css3"></noscript>
	<script type="text/javascript" src="./javascript/lib/less.js"></script>
	<script type="text/javascript" src="./javascript/lib/jquery.js"></script>
	<script type="text/javascript" src="./javascript/lib/require.js"></script>
	<script type="text/javascript" src="./javascript/rqrloader.js"></script>
</head>
<body>
	<div id="header">
		<div id="logo"></div>
		<ul id="toolbar">
			<li><input type="text" value="Chercher..." style="color:#666;" onfocus="val_onfocus(this)" onblur="val_onblur(this)" /></li>
			<li><a href="./faq.html">Issue Tracker</a></li>
			<li class="theme2"><a href="./?theme=2">Thème 2 (????)</a></li>
			<li class="theme1"><a href="./?theme=1">Thème 1 (Defaut)</a></li>
			<li><a href="./">Accueil</a></li>
		<?php if (is_null($MembreActuel)): ?>
			<li class="left"><a href="./connexion.html">[ Connexion ]</a></li>
			<li class="left"><a href="./inscription.html">[ Inscription ]</a></li>
		<?php else: ?>
			<li class="left"><a href="./profil-<?=$MembreActuel->getidmembre()?>.html">[ <?=$MembreActuel->getpseudo()?> ]</a></li>
			<li class="left"><a href="#">[ i (0) ]</a></li>
			<li class="left">
				<form action="./deconnexion.html" method="POST">
					<input type="hidden" name="page" value="<?=$_SERVER["REQUEST_URI"]?>"/>
					<input type="hidden" name="session" value="<?=$System->ajouterFormulaire("deconnexion")?>"/>
					<a onclick="this.parentNode.submit();">[ Deconnexion ]</a>
				</form>
			</li>
		<?php endif; ?>
		</ul>
		<div id="douze">
			<a href="./question.html">
				<h1>La <span>12<sup><span>z</span>ième</sup></span> communauté !</h1>
				<p>Partagez, participez, et suivez l'actualité du Web.</p>
			</a>
		</div>
		<ul id="menu">
		<?php
			$list = array(
				"question" => array("Questions", "Poser une question"),
				"tag" => array("Tags"),
				"news" => array("News", "Proposer une news"),
				"projet" => array("Projets", "Présenter votre projet"),
				"refwiki" => array("Références &amp; Wiki", "Rédiger une référence"),
				//"forum" => array("Forum"),
				"communaute" => array("Communauté", "Créer un groupe"),
				"rediger" => array("Autres...")
			);

			$page = $System->getGET("view");
			$_PAGENAME = (!empty($page)) ? $page : "index";

			if ($_PAGENAME == "profil") { $_PAGENAME = "communaute"; }

			$b = false;
			foreach ($list as $name => $c) {
				$url = "./$name.html";

				if ($name == "rediger") {
					$url = "./autres.html";
					if (isset($list[$_PAGENAME][1])) {
						$c[0] = $list[$_PAGENAME][1];
						$url = "$name-$_PAGENAME.html";
					} elseif (!$b) {
						$_PAGENAME = $name;
					}
				}

				if ($_PAGENAME == $name && $b = true) {
					echo "<li id=\"cx_$name\"><a href=\"$url\" class=\"here\">$c[0]</a></li>";
				} else {
					echo "<li id=\"cx_$name\"><a href=\"$url\">$c[0]</a></li>";
				}
			}
		?>
		</ul>
		<div style="clear:both"></div>
	</div>
	<div id="content">
		<?php
			if (!empty($page)) {
				$page = __PATH__."system/page/".str_replace("/", "", ucwords(strtolower($page))).".php";
				if (file_exists($page)) {
					include $page;
				} else {
					$erreur = "page";
					include __PATH__."system/page/erreur.php";
				}
			} else {
				include __PATH__."system/page/index.php";
			}
		?>
	</div>
	<div id="footer">
		<div id="subfooter">Dev info :</div>
		<div align="center">
			<?=((int)((microtime(true)-$start)*100000)/100)." ms"?>
		</div>
		<div align="center">
			<?=(isset($cache_info))?$cache_info:""?>
		</div>
		<div align="center">
			<?=nl2br($BasedeDonnee->getInfo());?>
		</div>
	<?php if ($_SERVER["REMOTE_ADDR"]=="127.0.0.1"): ?>
		<div id="devtool">
			<?=nl2br($BasedeDonnee->getLog())?>
		</div>
	<?php else: ?>
		<div id="devtool">
			<?="FORBIDDEN FOR {$_SERVER["REMOTE_ADDR"]}."?>
		</div>
	<?php endif; ?>
	</div>
</body>
</html>