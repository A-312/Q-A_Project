<div id="subContent">
	<div id="formContent">
		<div class="headerContent">
			<span>Erreur</span>
		</div>
		<div class="subContentBorder">
			<center>
			<?php
				$erreur = (isset($erreur)) ? $erreur : substr(htmlspecialchars(strtolower($System->getGET("e"))),0,60);

				if ($erreur == "page") {
					header("HTTP/1.0 404 Not Found");
					echo "La page que vous avez essayé de visiter n'existe pas.";
				} else {
					echo "...";
				}
			?>
			</center>
		</div>
	</div>
</div>