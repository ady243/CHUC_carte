<?php
/* Formulaire commun à « Nouvelle carte » et « Modifier » : saisie à gauche, aperçu en direct à droite.
   Variables attendues : $c (données de la carte, éventuellement vides), $photoObligatoire (bool), $libelleBouton, $urlAnnuler */
require_once __DIR__.'/carte.php';
$z=(float)($c['photo_zoom']??1);$px=(int)($c['photo_x']??50);$py=(int)($c['photo_y']??30);
?>
<form method="post" enctype="multipart/form-data" class="editeur" id="editeur">
<?=csrfField()?>
<div class="editeur-saisie">
 <section class="panel"><h2>1. Informations de l’agent</h2><div class="fields">
  <label>ID / Matricule *<input id="matricule" name="matricule" value="<?=e($c['matricule']??'')?>" required maxlength="40" pattern="[A-Za-z0-9._\-]+"><small>Le QR code se génère automatiquement.</small></label>
  <label>Fonction *<input id="fonction" name="fonction" value="<?=e($c['fonction']??'')?>" required maxlength="180"></label>
  <label>Nom *<input id="nom" name="nom" value="<?=e($c['nom']??'')?>" required maxlength="100"></label>
  <label>Postnom *<input id="postnom" name="postnom" value="<?=e($c['postnom']??'')?>" required maxlength="100"></label>
  <label>Prénom *<input id="prenom" name="prenom" value="<?=e($c['prenom']??'')?>" required maxlength="100"></label>
  <label>Date de délivrance<input type="date" name="date_delivrance" value="<?=e($c['date_delivrance']??'')?>"><small>Enregistrée, non imprimée.</small></label>
 </div></section>
 <section class="panel"><h2>2. Photo de l’agent</h2>
  <label class="upload"><b><?=$photoObligatoire?'Importer la photo *':'Remplacer la photo (facultatif)'?></b><input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" <?=$photoObligatoire?'required':''?>><small>JPG, PNG ou WEBP, 10 MB max. Idéal : tête et épaules, fond clair.</small></label>
  <div class="cadrage">
   <label>Zoom <input type="range" name="photo_zoom" id="photo_zoom" min="1" max="3" step="0.05" value="<?=e((string)$z)?>"></label>
   <label>Gauche / droite <input type="range" name="photo_x" id="photo_x" min="0" max="100" value="<?=$px?>"></label>
   <label>Haut / bas <input type="range" name="photo_y" id="photo_y" min="0" max="100" value="<?=$py?>"></label>
  </div>
 </section>
</div>
<div class="editeur-apercu">
 <section class="panel"><div class="head"><h2>3. Aperçu</h2><div class="switch"><button type="button" class="btn active" data-face="recto">RECTO</button><button type="button" class="btn" data-face="verso">VERSO</button></div></div>
  <div class="apercu" data-face-courante="recto"><div class="face-recto"><?=renderRecto($c)?></div><div class="face-verso"><?=renderVerso()?></div></div>
  <p class="aide">L’aperçu est exactement ce qui sera imprimé.</p>
 </section>
 <div class="submit"><a class="btn" href="<?=e($urlAnnuler)?>">Annuler</a><button class="btn primary"><?=e($libelleBouton)?></button></div>
</div>
</form>
