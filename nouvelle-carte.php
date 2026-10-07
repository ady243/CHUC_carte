<?php
session_start();require_once __DIR__.'/config/database.php';require_once __DIR__.'/includes/fonctions.php';ensureUploadDirs();
$title='Nouvelle carte';$errors=[];$c=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $photo=null;
 try{
  checkCsrf();$c=readCardForm();
  if(!isset($_FILES['photo'])||$_FILES['photo']['error']!==UPLOAD_ERR_OK)throw new RuntimeException('La photo de l’agent est obligatoire.');
  $s=$pdo->prepare('SELECT id FROM cartes WHERE matricule=?');$s->execute([$c['matricule']]);if($s->fetch())throw new RuntimeException('Ce matricule existe déjà.');
  $photo=savePhoto($_FILES['photo']);
  $s=$pdo->prepare('INSERT INTO cartes(matricule,nom,postnom,prenom,fonction,date_delivrance,photo,photo_zoom,photo_x,photo_y,qr_url) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
  $s->execute([$c['matricule'],$c['nom'],$c['postnom'],$c['prenom'],$c['fonction'],$c['date_delivrance']?:null,$photo,$c['photo_zoom'],$c['photo_x'],$c['photo_y'],qrUrl($c['matricule'])]);
  $id=(int)$pdo->lastInsertId();flash('success','Carte enregistrée avec succès.');redirect('voir-carte.php?id='.$id);
 }catch(Throwable $e){deletePhoto($photo);$errors[]=$e->getMessage();$c=array_merge(array_map('strval',$_POST),['photo'=>'']);}
}
require __DIR__.'/includes/header.php';?>
<section class="hero"><div><p>CRÉATION</p><h1>Nouvelle carte</h1><span>Le modèle est fixe : saisissez les informations de l’agent et importez sa photo, la carte se compose automatiquement.</span></div></section>
<?php if($errors):?><div class="alert error"><ul><?php foreach($errors as $x):?><li><?=e($x)?></li><?php endforeach;?></ul></div><?php endif;?>
<?php $photoObligatoire=true;$libelleBouton='Générer et enregistrer la carte';$urlAnnuler='index.php';require __DIR__.'/includes/formulaire-carte.php';?>
<?php require __DIR__.'/includes/footer.php';?>
