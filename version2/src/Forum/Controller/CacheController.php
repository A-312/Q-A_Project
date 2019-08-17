<?php
namespace src\Forum\Controller;

use Core\Kernel\Controller\Controller;
use src\Forum\Type\Membre;

class CacheController extends Controller {
	private static $membre = array();

	public function membre($opt1) {
		if (is_object($opt1)) {
			self::$membre[$opt1->getidmembre()] = $opt1;
		} else {
			if (!isset(self::$membre[$opt1])) {
				self::$membre[$opt1] = new Membre($opt1);
			}
			return self::$membre[$opt1];
		}
	}
}