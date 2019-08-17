<?php
namespace src\Forum\Controller;

use Core\Kernel\Controller\Controller;
use src\Forum\Controller\PageController;
use src\Forum\Model\QuestionModel;

class QuestionController extends Controller {
	public function indexAction() {
		$page = new PageController();
		$page->render("index.html.twig");
		return $page->end();
	}

	public function lectureQuestionAction() {
		return (new \Symfony\HttpFoundation\Response())->setContent("Oh");
	}

	public function listeQuestionsAction($tag = "") {
		$page = new PageController();
		$questionModel = new QuestionModel();

		list($tagmap, $tag) = $page->getTag($tag);

		$request = $this->getRequest();
		if ($request->query->has("tri")) {
			$g = $request->query->get("tri");
			if ($g == "mesinterventions" && !is_null($this->get("Forum")->getMembreActuel())) {
				$headertitle = "Mes interventions";
				$tri = "mesinterventions";
			} else {
				$tri = (array_search($g, array("recent", "vote")) !== false) ? $g : "recent";
				$request->getSession()->set("index-tri", $tri);
			}
		} elseif (!$request->getSession()->has("index-tri")) {
			$tri = "recent";
		} else {
			$tri = $request->getSession()->get("index-tri");
		}

		$optpage = true;
		$questions = $questionModel->listeQuestions($tagmap, $tri, $optpage);
		$pagination = (is_array($optpage)) ? $page->pagination($optpage["nbr"], $optpage["page"]) : null;

		$tags = $questionModel->listeTags($tagmap);
		$selectiontags = (count($tagmap) != 0) ? $questionModel->listeTags($tagmap, 2) : null;

		$twig_array = array("questions" => $questions,
							"tags" => $tags,
							"selectiontags" => $selectiontags,
							"nbrquestion" => (is_array($optpage)) ? $optpage["nbrquestion"] : count($questions),
							"pagination" => $pagination,
							"tri" => $tri
						);
		$page->render("listeQuestions.html.twig", $twig_array);

		return $page->end();
	}
}