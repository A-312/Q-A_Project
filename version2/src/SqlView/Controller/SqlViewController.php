<?php
namespace src\SqlView\Controller;

use Core\Kernel\Kernel;
use Core\Kernel\Controller\Controller;
use Symfony\HttpFoundation\Response;

class SqlViewController extends Controller {
	public function indexAction() {
		$kernel = Kernel::getCore();
		if (!$kernel->isDevEnvironment()) {
			return;
		}
		$response = new Response();

		$bdd = $kernel->getBundle("BdD");

		$tables = $bdd->lire("SHOW TABLES");

		$bdd->ecrire("USE INFORMATION_SCHEMA");
		$relations = $bdd->lire("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME IS NOT NULL AND CONSTRAINT_SCHEMA = ?",
			array("sdd"));
		$bdd->ecrire("USE sdd");

		foreach ($relations as $tab) {
			if (!isset($relations[$tab["TABLE_NAME"]])) {
				$relations[$tab["TABLE_NAME"]] = array();
			}

			$relations[$tab["TABLE_NAME"]][$tab["COLUMN_NAME"]] = array($tab["REFERENCED_TABLE_NAME"], $tab["REFERENCED_COLUMN_NAME"]);
		}

		$r_tables = array();
		foreach ($tables as $key => $table) {
			$table["count"] = $bdd->lire("SELECT COUNT(*) as count FROM {$table["Tables_in_sdd"]}", array(), "count");

			$array_column[$table["Tables_in_sdd"]] = $bdd->lire("DESC {$table["Tables_in_sdd"]}");

			$r_tables[$key] = array(
				"title" => "{$table["Tables_in_sdd"]} ({$table["count"]})",
				"column" => array()
			);

			foreach ($array_column[$table["Tables_in_sdd"]] as $key2 => $column) {
				$r_tables[$key]["column"][$key2] = array(
					"id" => $table["Tables_in_sdd"].".".$column["Field"],
					"option" => $column["Key"]." ".$column["Extra"],
					"name" => $column["Field"],
					"type" => $column["Type"],
					"d" => $this->getPathRelation($table["Tables_in_sdd"], $column["Field"], $relations)
				);
			}
		}

		$twig_array = array("tables" => $r_tables);
		$response->setContent($this->render("sqlview.html.twig", $twig_array));

		return $response;
	}

	private function getPathRelation($t, $c, $relations) {
		if (isset($relations[$t]) && isset($relations[$t][$c])) {
			$d = $relations[$t][$c];
		} else {
			return null;
		}
		return "<a href=\"#$d[0].$d[1]\">=> [ ]</a>";
	}
}
