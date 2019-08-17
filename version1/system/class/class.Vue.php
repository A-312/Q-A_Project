<?php
class Vue {
	public function listeQuestion($taglist = array(), $tri = "recent", &$opt_page = false) {
		global $BasedeDonnee, $System, $MembreActuel, $_AFF_CATEGORIE;

		$LIMIT = "";

		if (count($taglist) != 0) {}

		if ($opt_page !== false) {
			if ($tri == "mesinterventions") {
				$nbrquestion = $BasedeDonnee->lire(
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
				, array("categorie" => $_AFF_CATEGORIE, "idmembre" => $MembreActuel->getidmembre()), "nombre");
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
					$nbrquestion = $BasedeDonnee->lire(
						"SELECT COUNT(*) AS nombre
						FROM question
							JOIN ($sousrequete2) AS temp USING (idquestion)"
					, $tab, "nombre");
				} else {
					$nbrquestion = $BasedeDonnee->lire(
						"SELECT COUNT(*) AS nombre
						FROM question
						WHERE categorie = ?"
					, array($_AFF_CATEGORIE), "nombre");
				}
			}
			$nbrpage = ceil($nbrquestion/20);

			if (1 < $nbrpage) {
				$pageactuel = (int) $System->getGET("page");
				$pageactuel = ($pageactuel <= 1) ? 1 : $pageactuel;
				$pageactuel = ($nbrpage <= $pageactuel) ? $nbrpage : $pageactuel;

				$opt_page = array("page" => $pageactuel, "nbr" => $nbrpage);

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
				$list = $BasedeDonnee->lire(
					"SELECT question.*, COALESCE(vote, 0) AS vote
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

				$list = $BasedeDonnee->lire(
					"SELECT question.*, MAX(message.dateenvois) AS plusRecent
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
				$list = $BasedeDonnee->lire(
					"SELECT question.*, COALESCE(vote, 0) AS vote
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
				$list = $BasedeDonnee->lire(
					"SELECT question.*, MAX(message.dateenvois) AS plusRecent
					FROM question
						JOIN message USING (idquestion)
						LEFT JOIN questsuivi USING(idquestion)
						LEFT JOIN msgcomment USING(idmessage)
					WHERE categorie = :categorie
						AND (questsuivi.idmembre = :idmembre OR message.idmembre = :idmembre OR msgcomment.idmembre = :idmembre)
					GROUP BY question.idquestion
					ORDER BY plusRecent DESC, question.idquestion DESC".$LIMIT
				, array("categorie" => $_AFF_CATEGORIE, "idmembre" => $MembreActuel->getidmembre()));
			} else { //recent
				$list = $BasedeDonnee->lire(
					"SELECT question.*, MAX(message.dateenvois) AS plusRecent
					FROM question
						JOIN message USING (idquestion)
					WHERE categorie = ?
					GROUP BY question.idquestion
					ORDER BY plusRecent DESC, question.idquestion DESC".$LIMIT
				, array($_AFF_CATEGORIE));
			}
		}
		if (isset($list[0])) {
			global $cache_info;
			sort($taglist);
			$ordquest = array(); foreach ($list as $t) { $ordquest[] = $t["idquestion"]; } $ordquest = implode(",", $ordquest);
			$cache_info = "{ ".$_AFF_CATEGORIE." | ".((isset($pageactuel))?$pageactuel:1)." | ".implode(",", $taglist)." | ".$list[0]["idquestion"]." | ".hexdec(hash("crc32", $ordquest))." | ".(time()+60)." }";
		}

		$i = 0;
		$retour = array();
		foreach ($list as $tab) {
			$question = new Question($tab["idquestion"], "ancien", array(
				"question" => (isset($tab["question"])) ? $tab["question"] : null,
				"vue" => (isset($tab["vue"])) ? $tab["vue"] : null,
				"categorie" => (isset($tab["categorie"])) ? $tab["categorie"] : null
			));

			$vote = (isset($tab["vote"])) ? $tab["vote"] : $question->getvote();
			$rep = $question->getnombrerep();
			$vue = $question->getvue();

			$retour[$i]["compteur"] = array(
					array($vote, "vote".(($vote>1)?"s": "")),
					array($rep, "réponse".(($rep>1)?"s": "")),
					array($vue, "vue".(($vue>1)?"s": ""))
				);

			$derniermessage = $question->getmessage($rep);
			$membre = $System->Cache()->membre($derniermessage->getidmembre());

			$retour[$i]["membre"] = array(
				"id" => $membre->getidmembre(),
				"pseudo" => $membre->getpseudo(),
				"reputation" => $membre->getreputation(),
				"url" => $membre->geturl()
			);

			$retour[$i]["id"] = $question->getidquestion();
			$retour[$i]["question"] = $question->getquestion();
			$retour[$i]["url"] = $question->geturl();

			$retour[$i]["tag"] = $question->gettag();

			$retour[$i]["ilya"] = $System->tempsRelatif($derniermessage->getdateenvois());
			$retour[$i]["date"] = $System->tempsDate($derniermessage->getdateenvois());

			$i++;
		}

		return $retour;
	}

	public function pagination($nbrpage, $pageactuel) {
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
				$retour[] = "...";
			} elseif ($nbrpage < $p) {
				break;
			} if ($i == 7 && $nbrpage != $p) {
				$p = $nbrpage;
				$retour[] = "...";
			}
			$retour[] = (int) $p;
			$p++;
		}

		if ($suiv) { $retour[] = array("suiv", $pageactuel+1); }

		return $retour;
	}

	public function listeIntervention() {
		global $BasedeDonnee, $System, $MembreActuel, $_AFF_CATEGORIE;

		if (!isset($_AFF_CATEGORIE)) { $_AFF_CATEGORIE = "question"; }
		$list = $BasedeDonnee->lire(
			"SELECT question.idquestion, question, MAX(message.dateenvois) AS plusRecent, suivi
			FROM question
				JOIN message USING (idquestion)
				LEFT JOIN (
					SELECT idquestion, idmembre, 1 AS suivi
					FROM questsuivi
					WHERE idmembre = :idmembre
				) AS questsuivi USING(idquestion)
				LEFT JOIN msgcomment USING(idmessage)
			WHERE categorie = :categorie
				AND (questsuivi.idmembre = :idmembre OR message.idmembre = :idmembre OR msgcomment.idmembre = :idmembre)
			GROUP BY question.idquestion
			ORDER BY plusRecent DESC
			LIMIT 0,12"
		, array("categorie" => $_AFF_CATEGORIE, "idmembre" => $MembreActuel->getidmembre()));

		$jour = "";
		$jourp = "";

		$i = 0;
		$retour = array();
		foreach ($list as $tab) {
			$question = new Question($tab["idquestion"]);
			$dernierenvois = $tab["plusRecent"];
			if ($jour != date("Y-m-d", $dernierenvois) && $jourp != $System->tempsPeriode($dernierenvois)) {
				$jour = date("Y-m-d", $dernierenvois);
				$jourp = $System->tempsPeriode($dernierenvois);

				$retour[$i]["entete"] = $System->tempsPeriode($dernierenvois);
				$i++;
			}

			$retour[$i]["question"] = $tab["question"];
			$retour[$i]["url"] = $question->geturl($tab["question"]);
			$retour[$i]["ilya"] = $System->tempsRelatif($dernierenvois);

			$retour[$i]["msuivi"] = ($tab["suivi"]) ? "suivi" : "";

			$i++;
		}

		return $retour;
	}

	public function listeTag($taglist = array(), $mode = 1, $limit = 24, &$opt_page = false) {
		global $BasedeDonnee, $System, $_AFF_CATEGORIE;

		if (!isset($_AFF_CATEGORIE)) { $_AFF_CATEGORIE = "question"; }
		$LIMIT = "";

		if (is_array($taglist) && count($taglist) != 0) {
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
			$retour = $BasedeDonnee->lire(
				"SELECT tag.idtag, tag, COUNT(*) AS nombre
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
		} else if ($mode == 3) { //Pour la liste "Tous les tags" de ./tag.html
			if ($opt_page !== false) {
				$nbrtag = $BasedeDonnee->lire(
					"SELECT COUNT(*) as nombre
					FROM tag"
				, array($_AFF_CATEGORIE), "nombre");

				$nbrpage = ceil($nbrtag/$limit);
				if (1 < $nbrpage) {
					$pageactuel = (int) $System->getGET("page");
					$pageactuel = ($pageactuel <= 1) ? 1 : $pageactuel;
					$pageactuel = ($nbrpage <= $pageactuel) ? $nbrpage : $pageactuel;

					$opt_page = array("page" => $pageactuel, "nbr" => $nbrpage);

					$n = ($pageactuel-1) * $limit;

					$LIMIT = " LIMIT $n, $limit";
				} else {
					$opt_page = false;
				}
			}
			if (is_array($taglist)) {
				return $BasedeDonnee->lire(
					"SELECT tag, description, COUNT(*) AS nombre
					FROM questtag
						RIGHT JOIN tag USING (idtag)
					GROUP BY tag
					ORDER BY nombre DESC, tag".$LIMIT
				);
			} else {
				return $BasedeDonnee->lire(
					"SELECT tag, description, COUNT(*) AS nombre
					FROM questtag
						RIGHT JOIN tag USING (idtag)
					WHERE tag LIKE ?
					GROUP BY tag.idtag
					ORDER BY nombre DESC, tag".$LIMIT
				, array("%".$taglist."%"));
			}
		} else {
			return $BasedeDonnee->lire(
				"SELECT tag, COUNT(*) AS nombre
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

	public function listeMembre($term, &$opt_page = false) {
		global $BasedeDonnee, $System;

		$LIMIT = "";

		if ($opt_page !== false) {
			$nbrquestion = $BasedeDonnee->lire(
				"SELECT COUNT(*) AS nombre
					FROM membre
					WHERE pseudo LIKE ?"
			, array("%".$term."%"), "nombre");
			$nbrpage = ceil($nbrquestion/20);

			if (1 < $nbrpage) {
				$pageactuel = (int) $System->getGET("page");
				$pageactuel = ($pageactuel <= 1) ? 1 : $pageactuel;
				$pageactuel = ($nbrpage <= $pageactuel) ? $nbrpage : $pageactuel;

				$opt_page = array("page" => $pageactuel, "nbr" => $nbrpage);

				$n = ($pageactuel-1) * 20;
				$LIMIT = " LIMIT $n, 20";
			} else {
				$opt_page = false;
			}
		}

		$list = $BasedeDonnee->lire(
			"SELECT idmembre
			FROM membre
			WHERE pseudo LIKE ?".$LIMIT
		, array("%".$term."%"));

		$i = 0;
		$retour = array();
		foreach ($list as $tab) {
			$membre = new Membre($tab["idmembre"]);

			$retour[$i]["id"] = $membre->getidmembre();
			$retour[$i]["pseudo"] = $membre->getpseudo();
			$retour[$i]["reputation"] = $membre->getreputation();
			$retour[$i]["url"] = $membre->geturl();

			$i++;
		}

		return $retour;
	}

	public function sujetQuestion($id, $tri = "ancien", &$opt_page = false) {
		global $BasedeDonnee, $System, $MembreActuel;

		$tab = $BasedeDonnee->lire(
			"SELECT idquestion, COUNT(message.idquestion) as nombre
			FROM question
				JOIN message USING (idquestion)
			WHERE idquestion = ?"
		, array($id), BdD::FETCH);

		if (!is_array($tab)) {
			return false;
		}

		$n = 0; $n_last = $tab["nombre"]-1;
		if ($opt_page !== false) {
			$nbrmsg = $n_last;
			$limit = 15;

			$nbrpage = ceil($nbrmsg/$limit);
			if (1 < $nbrpage) {
				$pageactuel = (int) $System->getGET("page");
				$pageactuel = ($pageactuel <= 1) ? 1 : $pageactuel;
				$pageactuel = ($nbrpage <= $pageactuel) ? $nbrpage : $pageactuel;

				$opt_page = array("page" => $pageactuel, "nbr" => $nbrpage);

				$n = ($pageactuel-1) * $limit;
				if ($n > 0) $n++;

				$n_last = min($pageactuel * $limit, $n_last);
			} else {
				$opt_page = false;
			}
		}

		$question = new Question($id, $tri);

		$question->incrementervue(); /* <== Seulement les connectés ? */

		$membre = $System->Cache()->membre($question->getidmembre());
		$retour["membre"] = array(
				"id" => $membre->getidmembre(),
				"pseudo" => $membre->getpseudo(),
				"reputation" => $membre->getreputation(),
				"url" => $membre->geturl()
			);

		$retour["id"] = $question->getidquestion();
		$retour["question"] = $question->getquestion();
		$retour["url"] = $question->geturl();
		$retour["idmessage"] = $question->getidmessage();
		$retour["texte"] = $question->gettexte();

		$retour["tag"] = $question->gettag();

		$retour["suivi"] = $question->getsuivi();
		$retour["msuivi"] = (!is_null($MembreActuel) && $MembreActuel->getsuivi($question->getidquestion())) ? "on" : "";

		$rep = $question->getnombrerep();
		$retour["rep"] = $rep." réponse".(($rep>1)?"s": "");

		$date = $System->tempsDate($question->getdateenvois(), false);
		$retour["asking"] = "Demandé $date par {$membre->getpseudo()}.";

		$msg = array();
		for ($i = $n; $i <= $n_last; $i++) {
			$message = $question->getmessage($i);

			$membre = $System->Cache()->membre($message->getidmembre());
			$msg[$i] = array();
			$msg[$i]["membre"] = array(
				"id" => $membre->getidmembre(),
				"avatar" => $membre->getavatar(),
				"pseudo" => $membre->getpseudo(),
				"reputation" => $membre->getreputation(),
				"url" => $membre->geturl()
			);
			$msg[$i]["idmessage"] = $message->getidmessage();
			$msg[$i]["texte"] = $message->gettexte();
			$msg[$i]["type"] = $message->gettype();

			$msg[$i]["vote"] = $message->getvote();

			$mvote = is_null($MembreActuel) ? 0 : $MembreActuel->getvote($message->getidmessage());
			$msg[$i]["mvote"] = array(($mvote==1)?"on":"$mvote",($mvote==-1)?"on":"$mvote");

			$msg[$i]["date"] = $System->tempsDate($message->getdateenvois());

			$ilya = strtolower($System->tempsRelatif($message->getdateenvois()));
			$plus = ($message->getdateenvois() == $message->getdateedition()) ? "" : " et édité ".strtolower($System->tempsRelatif($message->getdateedition()));
			$msg[$i]["asking"] = "Posté $ilya$plus.";

			$com = $message->getnombrecommentaire();
			$msg[$i]["nbrcom"] = $com;

			$comment = array();
			for ($j = 0; $j < $com; $j++) {
				$commentaire = $message->getcommentaire($j);

				$membre = $System->Cache()->membre($commentaire->getidmembre());
				$comment[$j] = array();
				$comment[$j]["membre"] = array(
					"id" => $membre->getidmembre(),
					"pseudo" => $membre->getpseudo(),
					"reputation" => $membre->getreputation(),
					"url" => $membre->geturl()
				);
				$comment[$j]["idcommentaire"] = $commentaire->getidcommentaire();
				$comment[$j]["texte"] = $commentaire->gettexte();

				$comment[$j]["date"] = $System->tempsDate($commentaire->getdateenvois());

				$comment[$j]["ilya"] = $System->tempsRelatif($commentaire->getdateenvois(), true);
			}
			$msg[$i]["commentaire"] = $comment;
		}
		$retour["message"] = $msg;

		return $retour;
	}
}
?>