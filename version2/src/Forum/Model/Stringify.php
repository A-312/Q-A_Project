<?php
namespace src\Forum\Model;

class Stringify {
	public function normalize($chaine) {
		return strtr($chaine, array(
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'à'=>'a', 'á'=>'a',
			'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
			'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
			'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',	'ÿ'=>'y',
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
}