<?php require_once __DIR__.'/fonctions.php'; $flash=getFlash(); ?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title??APP_NAME)?></title><link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/carte.css"></head><body>
<header class="top"><div><b>CHU50</b><small>Gestionnaire de cartes</small></div><nav><a href="index.php">Cartes</a><a href="nouvelle-carte.php">Nouvelle carte</a></nav></header><main class="container">
<?php if($flash): ?><div class="alert <?=e($flash['type'])?>"><?=e($flash['message'])?></div><?php endif; ?>
