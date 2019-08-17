<?php
class Parser {
	public function parseHTML($message) {
		$message = preg_replace("#&(?!\#[0-9]+;)#si", "&amp;", $message); // fix & but allow unicode
		$message = str_replace("<","&lt;", $message);
		$message = str_replace(">","&gt;", $message);

		return $message;
	}

	private function fix_javascript(&$message) {
		$js_array = array(
			"#(&\#(0*)106;?|&\#(0*)74;?|&\#x(0*)4a;?|&\#x(0*)6a;?|j)((&\#(0*)97;?|&\#(0*)65;?|a)(&\#(0*)118;?|&\#(0*)86;?|v)(&\#(0*)97;?|&\#(0*)65;?|a)(\s)?(&\#(0*)115;?|&\#(0*)83;?|s)(&\#(0*)99;?|&\#(0*)67;?|c)(&\#(0*)114;?|&\#(0*)82;?|r)(&\#(0*)105;?|&\#(0*)73;?|i)(&\#112;?|&\#(0*)80;?|p)(&\#(0*)116;?|&\#(0*)84;?|t)(&\#(0*)58;?|\:))#i",
			"#(o)(nmouseover\s?=)#i",
			"#(o)(nmouseout\s?=)#i",
			"#(o)(nmousedown\s?=)#i",
			"#(o)(nmousemove\s?=)#i",
			"#(o)(nmouseup\s?=)#i",
			"#(o)(nclick\s?=)#i",
			"#(o)(ndblclick\s?=)#i",
			"#(o)(nload\s?=)#i",
			"#(o)(nsubmit\s?=)#i",
			"#(o)(nblur\s?=)#i",
			"#(o)(nchange\s?=)#i",
			"#(o)(nfocus\s?=)#i",
			"#(o)(nselect\s?=)#i",
			"#(o)(nunload\s?=)#i",
			"#(o)(nkeypress\s?=)#i"
		);
		
		$message = preg_replace($js_array, "$1<b></b>$2$4", $message);
	}

	private function voyageWdHTML_args($tab_args, $objname) {
		$html = "";
		foreach ($tab_args as $attr => $valeur) {
			if ($valeur !== null && $this->voyageWdHTML_allowattr($attr)) {
				$html .= " $attr=\"".htmlentities($valeur)."\"";
			}
		}
		return $html;
	}

	private function voyageWdHTML_allowattr($attr) {
		return in_array($attr, array("align", "face", "size", "href", "title", "target", "src", "color", "style",
									"data-class", "data-format"));
	}

	private function voyageWdHTML_allowtag($name) {
		return in_array($name, array("br", "b", "i", "u", "strike", "sub", "sup", "div", "ol", "ul", "li", "font", "span", "code",
									"hr", "blockquote", "cite", "a", "img", "p", "pre", "h6", "h5", "h4", "h3", "h2", "h1"));
	}

	private function voyageWdHTML_special(&$obj) {
		if ($obj["name"] == "a") { $obj["args"]["target"] = "_blank"; }
		if ($obj["name"] == "pre" && isset($obj["args"]["data-class"])) {
			array_filter($obj["children"], function (&$var) {
				if (is_string($var)) { return true; }
				if ($var["name"] == "br") { $var = "\n"; return true; }
				return false;
			});
					$obj["args"]["data-format"] = (isset($obj["args"]["data-format"]) && $obj["args"]["data-format"] == "ligne") ? "ligne" : null;

			if (isset($obj["children"][0]) && is_string($obj["children"][0])) {
				if (preg_match("/^\#\{script:[ ]?([A-z0-9-+#().]+)(,[ ]?mime:[ ]?(text|application|message)\/([A-z0-9-+]+))?\}/", $obj["children"][0], $match)) {
					$obj["args"]["data-class"] = (isset($match[2])) ? strtolower($match[3]."/".$match[4]) : strtolower($match[1]);
				}
			}
		}
	}

	private function voyageWdHTML($tableau, $lvl = 0) {
		$html = "";
		foreach ($tableau as $obj) {
			if (is_array($obj)) {
				if (!$this->voyageWdHTML_allowtag($obj["name"])) {
					$obj["name"] = "pre";
					if (!isset($obj["children"])) {
						$obj["children"] = array();
					}
				}
				if (isset($obj["children"])) {
					$this->voyageWdHTML_special($obj);
					$html .= "<{$obj["name"]}{$this->voyageWdHTML_args($obj["args"], $obj["name"])}>{$this->voyageWdHTML($obj["children"], $lvl+1)}</{$obj["name"]}>";
				} else {
					$html .= "<{$obj["name"]}>";
				}
			} else {
				$html .= $obj;
			}
		}
		return $html;
	}

	public function parseBadHTML($message) {
		$WdHTMLParser = new WdHTMLParser();
		$message = str_replace(array("<br>", "<hr>"), array("<br/>", "<hr/>"), $message);
		$tableau = $WdHTMLParser->parse($message);

		if ($WdHTMLParser->malformed) {
			$retour = $WdHTMLParser->error;
		} else {
			$retour = $this->voyageWdHTML($tableau);

			$this->fix_javascript($retour);

			//Smiley ? //Balise code ?...
		}

		return array($WdHTMLParser->malformed, $retour);
	}

	private function parseSmiley(&$message) {
		$smilies = array(
		':salut:' => 'a+.gif',
		':B' => 'b.png',
		':reverse:' => 'reverse.png',
		':magie:' => 'magicien.png',
		':pleure:' => 'pleure.png',
		'o_O' => 'blink.gif',
		':rire:' => 'rire.gif',
		':euh:' => 'unsure.gif',
		' ;)' => 'clin.png',
		':D ' => 'heureux.png',
		'^^' => 'hihi.png',
		':o' => 'huh.png',
		':P ' => 'langue.png',
		':col:' => 'mechant.png',
		':sif:' => 'siffle.png',
		':)' => 'smile.png',
		':(' => 'triste.png',
		':noel:' => 'noel.png',
		':hap:' => 'hap.png',
		':crash:' => 'boom.png',
		':vide:' => 'vide.png',
		':ange:' => 'ange.png',
		':demon:' => 'demon.png',
		':censure:' => 'censure.png',
		':enerve:' => 'enerve.gif',
		':ninja:' => 'ninja.gif',
		'-_-' => 'pinch.png',
		':honte:' => 'rouge.png',
		':pirate:' => 'pirate.png',
		':soleil:' => 'soleil.png',
		':diable:' => 'diable.png',
		':waw:' => 'waw.png',
		':zorro:' => 'zorro.png',
		':pingouin:' => 'pingouin.png',
		':pingouindos:' => 'pingouindos.png',
		':cretin:' => 'cretin.png',
		':prison:' => 'banni.gif',
		':aide:' => 'aide.png',
		'Saluttoutlemonde' => 'Saluttoutlemonde.png');
		foreach ($smilies as $Code => $Fichier) {
			$message = str_ireplace(" $Code ", " <img src=\"Design/Smiley/$Fichier\" alt=\"$Code\" /> ", $message);
		}
	}
}
?>
