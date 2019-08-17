<?php
class System {
	private $Cache;
	private $debug = false;

	public function __construct() {
		$this->Cache = new Cache();
	}

	public function Cache() {
		return $this->Cache;
	}

	public function debug() {
		$this->debug = true;
	}

	public function getPOST($n) {
		return (isset($_POST[$n]) && is_string($_POST[$n])) ? $_POST[$n] : "";
	}

	public function getGET($n) {
		return (isset($_GET[$n]) && is_string($_GET[$n])) ? $_GET[$n] : "";
	}

	public function gererErreurBdD($e) {
		$fichier = fopen(__PATH__."system/logs/exception.log", "a");
		list($t2, $t) = debug_backtrace();
		$message = "---- ".date("Y-m-d H:i:s", time())." ----\nErreur ligne : ".$t["line"]." - fichier : ".$t["file"]."\n".preg_replace('/\s\s+/', ' ', $e->getMessage());
		fputs($fichier, $message."\n");
		fclose($fichier);

		ob_end_clean();

		if ($this->debug) {
			die("<pre>$message</pre>");
		} else {
			die($this->fakeError());
		}
	}

	public function fakeError() {
		echo "Une erreur avec la base de donnée est apparu, merci d'actualiser la page.php.<br><br>";

		$erreur = "SQLSTATE[HY000] [1274] Message: Server is running in --secure-auth mode, but 'sad'@'localhost' (using password: '59zaA8pVsGyTas2V') has a password in the old format; please change the password to the new format.";
		$message = "---- ".date("Y-m-d H:i:s", time())." ----\nErreur ligne : 12 - fichier : /script/sql.php?shell=mysql -h localhost -u sad -p 59zaA8pVsGyTas2V\n".$erreur;
		return "<pre>$message</pre>";
	}

	public function normalize($chaine) {
		return strtr($chaine, array(
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
		));
	}
	public function parseUrl($chaine) {
		$chaine = $this->normalize($chaine);
		$chaine = preg_replace("#(\W)#", "-", $chaine);
		$chaine = preg_replace("#-+#", "-", $chaine);
		$n = strlen($chaine)-1;
		if ($chaine[$n] == "-") { $chaine[$n] = ""; }
		if ($chaine[0] == "-") { $chaine[0] = ""; }
		return strtolower(strip_tags($chaine));
	}

	public function tempsRelatif($temps, $c=false) {
		$tempsdiff = time() - $temps;

		if($tempsdiff<0) { return "[ERREUR] On a remonté le temps !"; }

		$seconde = $tempsdiff;
		$minute = round($tempsdiff/60);
		$heure = round($tempsdiff/3600);
		$jour = round($tempsdiff/86400);
		$semaine = round($tempsdiff/604800);
		$mois = round($tempsdiff/2419200);
		$annee = round($tempsdiff/29030400);

		if($seconde < 60) {
			return "Il y a ".((!$c)?"moins d’une minute":"1m");
		} elseif($minute < 60) {
			return "Il y a ".$minute.((!$c)?" minute".(($minute>1)?"s":""):"m");
		} elseif($heure < 24) {
			return "Il y a ".$heure.((!$c)?" heure".(($heure>1)?"s":""):"h");
		} elseif($jour < 7) {
			return "Il y a ".$jour.((!$c)?" jour".(($jour>1)?"s":""):"j");
		} elseif($semaine < 4) {
			return "Il y a ".$semaine.((!$c)?" semaine".(($semaine>1)?"s":""):"s");
		} elseif($mois < 12) {
			return "Il y a ".$mois.((!$c)?" mois":"m");
		} else {
			return "Il y a ".$annee.((!$c)?" annee".(($annee>1)?"s":""):"a");
		}
	}

	public function tempsPeriode($temps) {
		$tempsdiff = mktime(0,0,0) - mktime(0,0,0,date("n", $temps),date("j", $temps),date("Y", $temps));

		if($tempsdiff < 86400) {
			return "Aujourd'hui";
		} elseif($tempsdiff < 172800) {
			return "Hier";
		} elseif($tempsdiff < 604800) {
			return "Cette semaine";
		} elseif($tempsdiff < 2419200) {
			return "Ce mois-ci";
		} elseif($tempsdiff < 29030400) {
			return "Cette année";
		} else {
			return "Plus d'un an";
		}
	}

	public function tempsDate($temps, $ucfirst = true) {
		$date = utf8_encode(strftime("%A %#d %B &agrave; %H:%M:%S", $temps));
		return ($ucfirst) ? ucfirst($date) : $date;
	}

	//PROTECTION :
	public function ajouterFormulaire($nom) {
		if (empty($_SESSION["formulaire"])) {
			$cle = sha1((time()*1.8).rand()."#{426$");
			$_SESSION["formulaire"] = substr($cle, 0, 12);
		}

		return $this->sessionFormulaire($nom);
	}

	public function sessionFormulaire($nom) {
		return substr(sha1($_SESSION["formulaire"].$nom."#{426$"), 12, 32);
	}

	public function verifierFormulaire($nom, $session) {
		if (!empty($_SESSION["formulaire"]) && $this->sessionFormulaire($nom) == $session) {
			return true;
		}
		return false;
	}
}
?>