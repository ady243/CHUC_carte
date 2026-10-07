<?php
// Suppression uniquement en POST avec jeton CSRF (avant : simple lien GET)
session_start();require_once __DIR__.'/config/database.php';require_once __DIR__.'/includes/fonctions.php';
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('index.php');
try{checkCsrf();}catch(Throwable $e){flash('error',$e->getMessage());redirect('index.php');}
$id=(int)($_POST['id']??0);$c=cardById($pdo,$id);
if($c){$s=$pdo->prepare('DELETE FROM cartes WHERE id=?');$s->execute([$id]);deletePhoto($c['photo']);flash('success','Carte supprimée.');}
redirect('index.php');
