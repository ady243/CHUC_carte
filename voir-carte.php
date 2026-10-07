<?php
session_start();require_once __DIR__.'/config/database.php';require_once __DIR__.'/includes/fonctions.php';require_once __DIR__.'/includes/carte.php';
$c=cardById($pdo,(int)($_GET['id']??0));if(!$c){http_response_code(404);die('Carte introuvable.');}$title=$c['matricule'];require __DIR__.'/includes/header.php';?>
<section class="hero"><div><p>CARTE ENREGISTRÉE</p><h1><?=e($c['matricule'])?></h1><span><?=e(fullName($c))?> · <?=e($c['fonction'])?></span></div><div><a class="btn" href="modifier-carte.php?id=<?=$c['id']?>">Modifier</a> <a class="btn primary" target="_blank" href="imprimer.php?id=<?=$c['id']?>">Imprimer</a></div></section>
<div class="faces"><div class="panel"><h2>RECTO</h2><?=renderRecto($c)?></div><div class="panel"><h2>VERSO</h2><?=renderVerso()?></div></div>
<section class="panel"><div class="details"><div>ID / Matricule<b><?=e($c['matricule'])?></b></div><div>Nom<b><?=e($c['nom'])?></b></div><div>Postnom<b><?=e($c['postnom'])?></b></div><div>Prénom<b><?=e($c['prenom'])?></b></div><div>Fonction<b><?=e($c['fonction'])?></b></div><div>URL du QR code<b><?=e($c['qr_url'])?></b></div></div></section>
<?php require __DIR__.'/includes/footer.php';?>
