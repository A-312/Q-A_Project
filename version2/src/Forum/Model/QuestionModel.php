<?php
namespace src\Forum\Model;

use Core\Kernel\Controller\Controller;
use src\Forum\Controller\CacheController;
use src\Forum\Model\Stringify;
use src\Forum\Type\Question;


class QuestionModel extends Controller {
	public function listeQuestions($taglist = array(), $tri = "recent", &$opt_page = false) {
		$Stringify = new Stringify();
		$Cache = new CacheController();
		$BdD = $this->get("BdD");
		$_AFF_CATEGORIE = "question";

		$LIMIT = " LIMIT 0, 100";

		if ($opt_page !== false) {
			if ($tri == "mesinterventions") {
				$nbrquestion = $BdD->lire(
					"SELECT COUNT(*) AS nombre
					FROM (
						SELECT 1
						FROM question
							JOIN message USING (idquestion)
							LEFT JOIN questsuivi USING(idquestion)
							LEFT JOIN msgcomment USING(idmessage)
						WHERE categorie = :categorie
							AND (questsuivi.idmembre = :idmembre OR message.idmembre = :idmembre OR msgcomment.idmembre = :idmembre)
						GROUP BY question.idquestion
					) AS intervention"
				, array("categorie" => $_AFF_CATEGORIE, "idmembre" => $this->get("Forum")->getMembreActuel()->getidmembre()), "nombre");
			} else {
				if (count($taglist) != 0) {
					$qmark = str_repeat("?,",count($taglist)-1)."?";
					$tab = $taglist;
					$tab[] = $_AFF_CATEGORIE;
					$tab[] = count($tab)-1;

					$sousrequete =
						"SELECT question.idquestion, count(*) AS tags
						FROM question
							JOIN questtag USING (idquestion)
						WHERE idtag IN ($qmark)
						GROUP BY question.idquestion"
					;
					$sousrequete2 =
						"SELECT idquestion
						FROM question
							JOIN ($sousrequete) AS temp USING (idquestion)
							JOIN message USING (idquestion)
						WHERE categorie = ? AND tags = ?
						GROUP BY question.idquestion"
					;
					$nbrquestion = $BdD->lire(
						"SELECT COUNT(*) AS nombre
						FROM question
							JOIN ($sousrequete2) AS temp USING (idquestion)"
					, $tab, "nombre");
				} else {
					$nbrquestion = $BdD->lire(
						"SELECT COUNT(*) AS nombre
						FROM question
						WHERE categorie = ?"
					, array($_AFF_CATEGORIE), "nombre");
				}
			}
			$nbrpage = ceil($nbrquestion/20);

			if (1 < $nbrpage) {
				$pageactuel = (int) $this->getRequest()->attributes->get("page");
				$pageactuel = ($pageactuel <= 1) ? 1 : $pageactuel;
				$pageactuel = ($nbrpage <= $pageactuel) ? $nbrpage : $pageactuel;

				$opt_page = array("page" => $pageactuel, "nbr" => $nbrpage, "nbrquestion" => $nbrquestion);

				$n = ($pageactuel-1) * 20;
				$LIMIT = " LIMIT $n, 20";
			} else {
				$opt_page = false;
			}
		}

		if (count($taglist) != 0) {
			$qmark = str_repeat("?,",count($taglist)-1)."?";
			$tab = $taglist;
			$tab[] = $_AFF_CATEGORIE;
			$tab[] = count($tab)-1;

			if ($tri == "vote") {
				$requete1 =
					"SELECT idquestion, idmessage, count(*) AS tags
					FROM message
						JOIN questtag USING (idquestion)
					WHERE type = 'question' AND idtag IN ($qmark)
					GROUP BY idquestion"
				;
				$requete2 =
					"SELECT message.idmessage, SUM(valeur) AS vote
					FROM message JOIN msgvote USING (idmessage)
					GROUP BY message.idmessage"
				;
				$list = $BdD->lire(
					"SELECT question.*, message.texte, COALESCE(vote, 0) AS vote
					FROM question
						JOIN ($requete1) AS temp USING (idquestion)
						LEFT JOIN msgvote USING (idmessage)
						LEFT JOIN ($requete2) AS temp2 USING (idmessage)
					WHERE categorie = ? AND tags = ?
					ORDER BY vote DESC, question.idquestion DESC".$LIMIT
				, $tab);
			} else { //recent
				$sousrequete =
					"SELECT question.idquestion, count(*) AS tags
					FROM question
						JOIN questtag USING (idquestion)
					WHERE idtag IN ($qmark)
					GROUP BY question.idquestion"
				;

				$list = $BdD->lire(
					"SELECT question.*, message.texte, MAX(message.dateenvois) AS plusRecent
					FROM question
						JOIN ($sousrequete) AS temp USING (idquestion)
						JOIN message USING (idquestion)
					WHERE categorie = ? AND tags = ?
					GROUP BY question.idquestion
					ORDER BY plusRecent DESC, question.idquestion DESC".$LIMIT
				, $tab);
			}
		} else { //normal
			if ($tri == "vote") {
				$sousrequete =
					"SELECT message.idmessage, SUM(valeur) AS vote
					FROM message JOIN msgvote USING (idmessage)
					GROUP BY message.idmessage"
				;
				$list = $BdD->lire(
					"SELECT question.*, message.texte, COALESCE(vote, 0) AS vote
					FROM question
						JOIN message USING (idquestion)
						LEFT JOIN msgvote USING (idmessage)
						LEFT JOIN ($sousrequete) AS temp USING (idmessage)
					WHERE categorie = ?
						AND type = 'question'
					GROUP BY question.idquestion
					ORDER BY vote DESC, question.idquestion DESC".$LIMIT
				, array($_AFF_CATEGORIE));
			} elseif ($tri == "mesinterventions") {
				$list = $BdD->lire(
					"SELECT question.*, message.texte, MAX(message.dateenvois) AS plusRecent
					FROM question
						JOIN message USING (idquestion)
						LEFT JOIN questsuivi USING(idquestion)
						LEFT JOIN msgcomment USING(idmessage)
					WHERE categorie = :categorie
						AND (questsuivi.idmembre = :idmembre OR message.idmembre = :idmembre OR msgcomment.idmembre = :idmembre)
					GROUP BY question.idquestion
					ORDER BY plusRecent DESC, question.idquestion DESC".$LIMIT
				, array("categorie" => $_AFF_CATEGORIE, "idmembre" => $this->get("Forum")->getMembreActuel()->getidmembre()));
			} else { //recent
				$list = $BdD->lire(
					"SELECT question.*, message.texte, MAX(message.dateenvois) AS plusRecent
					FROM question
						JOIN message USING (idquestion)
					WHERE categorie = ?
					GROUP BY question.idquestion
					ORDER BY plusRecent DESC, question.idquestion DESC".$LIMIT
				, array($_AFF_CATEGORIE));
			}
		}
		if (isset($list[0])) {
			$taglist = (is_null($taglist)) ? array() : $taglist;
			sort($taglist);
			$ordquest = array(); foreach ($list as $t) { $ordquest[] = $t["idquestion"]; } $ordquest = implode(",", $ordquest);
			//$cache_info = "{ ".$_AFF_CATEGORIE." | ".((isset($pageactuel))?$pageactuel:1)." | ".implode(",", $taglist)." | ".$list[0]["idquestion"]." | ".hexdec(hash("crc32", $ordquest))." | ".(time()+60)." }";
		}

		$i = 0;
		$questions = array();
		foreach ($list as $tab) {
			$question = new Question($tab["idquestion"], "ancien", array(
				"question" => (isset($tab["question"])) ? $tab["question"] : null,
				"vue" => (isset($tab["vue"])) ? $tab["vue"] : null,
				"categorie" => (isset($tab["categorie"])) ? $tab["categorie"] : null
			));

			$vote = (isset($tab["vote"])) ? $tab["vote"] : $question->getvote();
			$rep = $question->getnombrerep();
			$vue = $question->getvue();

			$questions[$i]["compteurs"] = array(
					array($rep, "réponse".(($rep>1)?"s": "")),
					//array($vue, "vue".(($vue>1)?"s": "")),
					array($vote, "vote".(($vote>1)?"s": ""))
				);

			$questions[$i]["vue"] = array($vue, "vue".(($vue>1)?"s": ""));

			$derniermessage = $question->getmessage($rep);
			$membre = $Cache->membre($derniermessage->getidmembre());

			$questions[$i]["membre"] = array(
				"id" => $membre->getidmembre(),
				"pseudo" => $membre->getpseudo(),
				"reputation" => $membre->getreputation(),
				"url" => $membre->geturl()
			);

			$questions[$i]["id"] = $question->getidquestion();
			$questions[$i]["titre"] = $titre = $question->getquestion();
			$questions[$i]["titreurl"] = $Stringify->parseUrl($titre);

			$questions[$i]["texte"] = $tab["texte"];

			$questions[$i]["tags"] = $question->gettag();

			$questions[$i]["ilya"] = $Stringify->tempsRelatif($derniermessage->getdateenvois());
			$questions[$i]["date"] = $Stringify->tempsDate($derniermessage->getdateenvois());

			$i++;
		}

		return $questions;
	}

	public function listeTags($taglist = array(), $mode = 1, $limit = 24, &$opt_page = false) {
		$_AFF_CATEGORIE = "question";
		$BdD = $this->get("BdD");

		if (!isset($_AFF_CATEGORIE)) { $_AFF_CATEGORIE = "question"; }
		$LIMIT = "";

		if (is_array($taglist) && count($taglist) != 0 && $mode <= 2) {
			$qmark = str_repeat("?,",count($taglist)-1)."?";
			$tab = $taglist;
			$tab[] = $_AFF_CATEGORIE;
			$tab[] = count($tab)-1;

			$sousrequete =
				"SELECT categorie, question.idquestion, count(*) AS tags
				FROM question
					JOIN questtag USING (idquestion)
				WHERE idtag IN ($qmark)
				GROUP BY question.idquestion"
			;
			$retour = $BdD->lire(
				"SELECT tag.idtag, tag AS nom, COUNT(*) AS nombre
				FROM tag
					JOIN questtag USING (idtag)
					LEFT JOIN ($sousrequete) AS temp USING (idquestion)
				WHERE categorie = ? AND tags = ?
				GROUP BY tag.idtag
				ORDER BY nombre DESC, tag
				LIMIT 0, 24"
			, $tab);

			foreach ($retour as $cle => $tab) {
				if ($mode == 1 && in_array($tab["idtag"], $taglist)) { //Affichage des éléments NON-selectionné
					unset($retour[$cle]); //On supprime ceux qui sont selectionné.
				} elseif ($mode == 2 && !in_array($tab["idtag"], $taglist)) { //Affichage des éléments selectionné
					unset($retour[$cle]);
				}
			}

			return $retour;
		} else {
			return $BdD->lire(
				"SELECT tag AS nom, COUNT(*) AS nombre
				FROM question
					JOIN questtag USING (idquestion)
					JOIN tag USING (idtag)
				WHERE categorie = ?
				GROUP BY tag.idtag
				ORDER BY nombre DESC, tag
				LIMIT 0, $limit"
			, array($_AFF_CATEGORIE));
		}
	}
}