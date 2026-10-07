<?php
session_start();require_once __DIR__.'/config/database.php';require_once __DIR__.'/includes/fonctions.php';
$title='Cartes CHU50';$q=trim($_GET['q']??'');
if($q){$x='%'.$q.'%';$s=$pdo->prepare('SELECT * FROM cartes WHERE matricule LIKE ? OR nom LIKE ? OR postnom LIKE ? OR prenom LIKE ? OR fonction LIKE ? ORDER BY date_modification DESC');$s->execute([$x,$x,$x,$x,$x]);}else{$s=$pdo->query('SELECT * FROM cartes ORDER BY date_modification DESC');}
$cartes=$s->fetchAll();$total=(int)$pdo->query('SELECT COUNT(*) FROM cartes')->fetchColumn();
require __DIR__.'/includes/header.php';?>
<section class="hero"><div><p>SERVICE INFORMATIQUE</p><h1>Gestion des cartes de service</h1><span>Modèle fixe → saisie de l’agent + photo → QR automatique → enregistrement → recherche, modification, impression.</span></div><a class="btn primary" href="nouvelle-carte.php">+ Nouvelle carte</a></section>
<div class="stat"><span>Cartes enregistrées</span><b><?=$total?></b></div>
<section class="panel"><div class="head"><h2>Cartes enregistrées</h2><form><input name="q" value="<?=e($q)?>" placeholder="ID, nom, postnom, prénom..."><button class="btn">Rechercher</button></form></div>
<?php if(!$cartes):?><div class="empty">Aucune carte.</div><?php else:?><div class="table"><table><tr><th></th><th>ID</th><th>Nom complet</th><th>Fonction</th><th>Actions</th></tr>
<?php foreach($cartes as $c):?><tr><td><img class="vignette" src="<?=e($c['photo'])?>" alt=""></td><td><b><?=e($c['matricule'])?></b></td><td><?=e(fullName($c))?></td><td><?=e($c['fonction'])?></td><td><a href="voir-carte.php?id=<?=$c['id']?>">Voir</a> <a href="modifier-carte.php?id=<?=$c['id']?>">Modifier</a> <a target="_blank" href="imprimer.php?id=<?=$c['id']?>">Imprimer</a> <form class="inline" method="post" action="supprimer-carte.php" onsubmit="return confirm('Supprimer cette carte ?')"><?=csrfField()?><input type="hidden" name="id" value="<?=$c['id']?>"><button class="lien danger">Supprimer</button></form></td></tr><?php endforeach;?></table></div><?php endif;?></section>
<?php require __DIR__.'/includes/footer.php';?>