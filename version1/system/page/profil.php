<?php
if ($System->getGET("id") != "" && $idmembre = $BasedeDonnee->lire("SELECT idmembre FROM membre WHERE idmembre = ?", array((int) $System->getGET("id")), "idmembre")):

$membre = new Membre($idmembre);
?>
<div id="subContent">
	<div>
		<div class="headerContent">
			<span>Profil de <?=$membre->getpseudo();?></span>
		</div>
		<div class="subContentBorder">
			<h2><?=$membre->getpseudo();?></h2>
		</div>
	</div>
</div>
<?php
else:
?>
<div id="subContent">
	<div>
		<div class="headerContent">
			<span>Lieu de rendez-vous</span>
		</div>
		<div class="subContentBorder">
			<marquee onmousedown="this.stop()" onmouseup="this.start()" behavior="scroll" direction="left" scrollamount="6" class="padding">
				<p>/!\ Le numéro n'est pas attribué, tu viens de te prendre un rat d'eau. Les rats ça nage !</p>
			</marquee>
		</div>
	</div>
</div>
<?php
endif;
?>