<?php
namespace src\Forum\Controller;

use Core\Kernel\Controller\Controller;
use src\Forum\Model\TagModel;

class TagController extends Controller {
	public function listeTagsAction($tag = "") {
		$page = new PageController();
		$tagModel = new TagModel();

		$optpage = true;
		$tags = $tagModel->listeTags($tag, 24, $optpage);
		$pagination = (is_array($optpage)) ? $page->pagination($optpage["nbr"], $optpage["page"]) : null;

		$twig_array = array("tagterm" => $tag,
							"tags" => $tags,
							"pagination" => $pagination
						);
		$page->render("listeTags.html.twig", $twig_array);

		return $page->end();
	}
}