<?php
namespace src\Forum\Model;

use Core\Kernel\Controller\Controller;


class TagModel extends Controller {
	public function listeTags($tag = "", $limit = 24, &$opt_page = false) {
		$_AFF_CATEGORIE = "question";
		$BdD = $this->get("BdD");

		if (!isset($_AFF_CATEGORIE)) { $_AFF_CATEGORIE = "question"; }
		$LIMIT = "";

		if ($opt_page !== false) {
			if (empty($tag)) {
				$nbrtag = $BdD->lire(
					"SELECT COUNT(*) as nombre
					FROM tag"
				, array(), "nombre");
			} else {
				$nbrtag = $BdD->lire(
					"SELECT COUNT(*) as nombre
					FROM tag
					WHERE tag LIKE ?"
				, array("%".$tag."%"), "nombre");
			}

			$nbrpage = ceil($nbrtag/$limit);
			if (1 < $nbrpage) {
				$pageactuel = (int) $this->getRequest()->attributes->get("page");
				$pageactuel = ($pageactuel <= 1) ? 1 : $pageactuel;
				$pageactuel = ($nbrpage <= $pageactuel) ? $nbrpage : $pageactuel;

				$opt_page = array("page" => $pageactuel, "nbr" => $nbrpage);

				$n = ($pageactuel-1) * $limit;

				$LIMIT = " LIMIT $n, $limit";
			} else {
				$opt_page = false;
			}
		}
		if (empty($tag)) {
			return $BdD->lire(
				"SELECT tag AS nom, description, COUNT(*) AS nombre
				FROM questtag
					RIGHT JOIN tag USING (idtag)
				GROUP BY tag
				ORDER BY nombre DESC, tag".$LIMIT
			);
		} else {
			return $BdD->lire(
				"SELECT tag AS nom, description, COUNT(*) AS nombre
				FROM questtag
					RIGHT JOIN tag USING (idtag)
				WHERE tag LIKE ?
				GROUP BY tag.idtag
				ORDER BY nombre DESC, tag".$LIMIT
			, array("%".$tag."%"));
		}
	}
}