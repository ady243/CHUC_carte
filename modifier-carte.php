<?php
session_start();require_once __DIR__.'/config/database.php';require_once __DIR__.'/includes/fonctions.php';ensureUploadDirs();
$id=(int)($_GET['id']??0);$c=cardById($pdo,$id);if(!$c){http_response_code(404);die('Carte introuvable.');}$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $nouvelle=null;
 try{
  checkCsrf();$d=readCardForm();
  $s=$pdo->prepare('SELECT id FROM cartes WHERE matricule=? AND id<>?');$s->execute([$d['matricule'],$id]);if($s->fetch())throw new RuntimeException('Ce matricule existe déjà.');
  $photo=$c['photo'];
  if(isset($_FILES['photo'])&&$_FILES['photo']['error']===UPLOAD_ERR_OK){$nouvelle=savePhoto($_FILES['photo']);$photo=$nouvelle;}
  $s=$pdo->prepare('UPDATE cartes SET matricule=?,nom=?,postnom=?,prenom=?,fonction=?,date_delivrance=?,photo=?,photo_zoom=?,photo_x=?,photo_y=?,qr_url=? WHERE id=?');
  $s->execute([$d['matricule'],$d['nom'],$d['postnom'],$d['prenom'],$d['fonction'],$d['date_delivrance']?:null,$photo,$d['photo_zoom'],$d['photo_x'],$d['photo_y'],qrUrl($d['matricule']),$id]);
  if($nouvelle)deletePhoto($c['photo']); // l'ancienne photo n'est plus utilisée
  flash('success','Carte modifiée.');redirect('voir-carte.php?id='.$id);
 }catch(Throwable $e){deletePhoto($nouvelle);$errors[]=$e->getMessage();$c=array_merge($c,array_map('strval',$_POST));}
}
$title='Modifier '.$c['matricule'];require __DIR__.'/includes/header.php';?>
<section class="hero"><div><p>MODIFICATION</p><h1>Modifier la carte <?=e($c['matricule'])?></h1><span><?=e(fullName($c))?></span></div></section>
<?php if($errors):?><div class="alert error"><ul><?php foreach($errors as $x):?><li><?=e($x)?></li><?php endforeach;?></ul></div><?php endif;?>
<?php $photoObligatoire=false;$libelleBouton='Enregistrer les modifications';$urlAnnuler='voir-carte.php?id='.$id;require __DIR__.'/includes/formulaire-carte.php';?>
<?php require __DIR__.'/includes/footer.php';?>
