<?php
namespace src\Forum\Controller;

use Core\Kernel\Controller\Controller;
use Symfony\HttpFoundation\Response;
use Core\Kernel\Kernel;
use BdD\BdD;

class PageController extends Controller {
	private $response;
	private $context;

	public function __construct() {
		$this->response = new Response;
		$this->context = array(
			"app" => array(
				"route" => $this->getRequest()->attributes->get("_route"),
				"isDevEnvironment" => Kernel::getCore()->isDevEnvironment()
			),
			"navigbar" => array(
				"questions" => "Questions",
				"tags" => "Tags",
				"news" => "News",
				"projets" => "Projets",
				"refwiki" => "Références & Wiki",
				//"forum" => "Forum",
				"communaute" => "Communauté",
				"rediger" => "Autres..."
			)
		);
	}

	public function render($view, array $context = array()) {
		$this->response->setContent(parent::render($view, array_merge($this->context, $context)));
	}

	public function getTag($tag) {
		if (empty($tag)) { return; }
		$tagmap = array_unique(explode("-", $tag));
		$BdD = $this->get("BdD");

		$i = 0; $actag = ""; $taglist = array();
		foreach ($tagmap as $tag) {
			if (4 <= $i) {
				break;
			} else if (preg_match("#^(\w+)$#", $tag)) {
				$tab = $BdD->lire("SELECT idtag FROM tag WHERE tag = ?", array($tag), BdD::FETCH);
				$taglist[] = $tab["idtag"];
				$actag .= (($actag != "") ? "-" : "").$tag;
			}
			$i++;
		}

		$this->context["app"]["tag"] = $actag;
		return [$taglist, $actag];
	}

	public function pagination($nbrpage, $pageactuel, $mini = false) {
		$suiv = true;

		$prev = !($pageactuel == 1);
		$suiv = !($nbrpage == $pageactuel);

		$retour = array();

		if ($prev) { $retour[] = array("prec", $pageactuel-1); }

		$m = ($nbrpage <= $pageactuel+2) ? 3-($nbrpage-$pageactuel) : 0;

		$t = $pageactuel - 2 - $m;

		$p = 1;

		for ($i=1; $i<=7; $i++) {
			if ($i == 2 && 2 < $t) {
				$p = $t;
				$retour[] = array("str", "...");
			} elseif ($nbrpage < $p) {
				break;
			} if ($i == 7 && $nbrpage != $p) {
				$p = $nbrpage;
				$retour[] = array("str", "...");
			}
			$retour[] = array("int", (int) $p);
			$p++;
		}

		if ($suiv) { $retour[] = array("suiv", $pageactuel+1); }
		return array(
			"mini" => $mini,
			"page" => $this->getRequest()->attributes->get("page"),
			"elements" => $retour
		);
	}

	public function end() {
		return $this->response;
	}
}