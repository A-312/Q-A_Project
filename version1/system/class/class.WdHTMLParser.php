<?php
class WdHTMLParser {
	private $encoding;
	private $matches;
	private $escaped;
	private $opened = array();

	public $error;
	public $malformed;

	public function parse($html, $namespace=NULL, $encoding='utf-8') {
		$this->malformed = false;
		$this->encoding = $encoding;
		
		$html = $this->escapeSpecials($html);
		$this->matches = preg_split('#<(/?)' . $namespace . '([^>]*)>#', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

		$tree = $this->buildTree();
		if ($this->escaped) {
			$tree = $this->unescapeSpecials($tree);
		}

		return $tree;
	}

	private function escapeSpecials($html) {
		$html = preg_replace_callback('#<\!--.+-->#sU', array($this, 'escapeSpecials_callback'), $html);
		$html = preg_replace_callback('#<\?.+\?>#sU', array($this, 'escapeSpecials_callback'), $html);

		return $html;
	}

	private function escapeSpecials_callback($m) {
		$this->escaped = true;
		
		$text = $m[0];
		$text = str_replace(array('<', '>'), array("\x01", "\x02"), $text);

		return $text;
	}

	private function unescapeSpecials($tree) {
		return is_array($tree) ? array_map(array($this, 'unescapeSpecials'), $tree) : str_replace(array("\x01", "\x02"), array('<', '>'), $tree);
	}

	private function buildTree() {
		$nodes = array();

		$i = 0;
		$text = NULL;
		while (($value = array_shift($this->matches)) !== NULL) {
			switch ($i++ % 3) {
				case 0:	
					if (trim($value)) {
						$nodes[] = $value;
					}
				break;
				case 1:
					$closing = ($value == '/');
				break;
				case 2:
					if (substr($value, -1, 1) == '/') {
						$nodes[] = $this->parseMarkup(substr($value, 0, -1));
					}
					else if ($closing) {
						$open = array_pop($this->opened);
					
						if ($value != $open) {
							$this->error($value, $open);
						}

						return $nodes;
					} else {
						$node = $this->parseMarkup($value);

						$this->opened[] = $node['name'];
						$node['children'] = $this->buildTree($this->matches);
						
						$nodes[] = $node;
					}
				break;
			}
		}

		return $nodes;
	}

	public function parseMarkup($markup) {
		preg_match('#^[^\s]+#', $markup, $matches);

		$name = $matches[0];
		preg_match_all('#\s+([^=]+)\s*=\s*"([^"]+)"#', $markup, $matches, PREG_SET_ORDER);
		$args = array();

		foreach ($matches as $m) {
			$args[$m[1]] = html_entity_decode($m[2], ENT_QUOTES, $this->encoding);
		}

		return array('name' => $name, 'args' => $args);
	}
	
	public function error($markup, $expected) {
		$this->error = "Fermeture balise \"$markup\" inattendu, \"$expected\" était attendu.";
		$this->malformed = true;
	}
}
?>