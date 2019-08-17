<?php
if (fsockopen("127.0.0.1", 3306, $errno, $errstr, 2)) {
	include "main.php";
	$BasedeDonnee->ecrire("UPDATE membre SET reputation = reputation + 1 WHERE idmembre = 8");
}
?>