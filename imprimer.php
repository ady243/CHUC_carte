<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/carte.php';

$c = cardById($pdo, (int)($_GET['id'] ?? 0));
if (!$c) {
    http_response_code(404);
    die('Carte introuvable.');
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Impression <?= e($c['matricule']) ?></title>
<link rel="stylesheet" href="assets/css/carte.css">
<style>
/* Format carte CR80 : 85,6 x 54 mm, une page par face (recto puis verso) */
@page { size: 85.6mm 53.98mm; margin: 0 }
* { box-sizing: border-box }
body { margin: 0; background: #d9dee5; font-family: Arial, sans-serif }

.bar { text-align: center; background: #fff; padding: 12px; font-size: 14px }
.bar button { padding: 8px 16px; font-size: 14px; cursor: pointer }
.bar small { display: block; color: #667; margin-top: 6px }
.bar .groupe { display: inline-block; margin: 4px 10px }

.sheet { display: flex; flex-direction: column; align-items: center; gap: 8mm; padding: 8mm 0 }

/* Le modèle (2411x1560) est un peu plus haut que le format CR80 : il est centré et
   rogné de 0,7 mm en haut et en bas (zones blanches / frise), sans déformation. */
.page { position: relative; width: 85.6mm; height: 53.98mm; overflow: hidden; background: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, .2) }
.page .carte { position: absolute; left: 0; top: calc((53.98mm - 85.6mm * 1560 / 2411) / 2); width: 85.6mm }

@media print {
    html, body { background: #fff; margin: 0; padding: 0 }
    .bar { display: none }
    .sheet { display: block; padding: 0 }
    .page { box-shadow: none; break-after: page }
    .page:last-child { break-after: auto }
}
</style>
</head>
<body>
<div class="bar" data-matricule="<?= e($c['matricule']) ?>">
    <span class="groupe">
        <b>Recto</b>
        <button data-action="telecharger" data-face="recto" disabled>Télécharger l’image</button>
        <button data-action="copier" data-face="recto" disabled>Copier l’image</button>
    </span>
    <span class="groupe">
        <b>Verso</b>
        <button data-action="telecharger" data-face="verso" disabled>Télécharger l’image</button>
        <button data-action="copier" data-face="verso" disabled>Copier l’image</button>
    </span>
    <span class="groupe">
        <button id="btn-imprimer" disabled>Imprimer</button>
    </span>
    <small>Images : PNG en pleine résolution du modèle, à coller ou importer dans le logiciel de l’imprimante.</small>
    <small>Impression : marges « Aucune », échelle 100 %, cocher « Graphiques d’arrière-plan ».</small>
</div>

<div class="sheet">
    <div class="page"><?= renderRecto($c) ?></div>
    <div class="page"><?= renderVerso() ?></div>
</div>

<script src="assets/js/qrcode.js"></script>
<script src="assets/js/carte.js"></script>
<script src="assets/js/image.js"></script>
<script>
// Les boutons ne s'activent qu'une fois les cartes rendues (polices chargées, texte ajusté)
document.addEventListener('cartes-pretes', () => {
  const barre = document.querySelector('.bar');
  const face = (nom) => document.querySelector(`.carte-${nom}`);

  // Remplace brièvement le libellé du bouton pour confirmer l'action
  function signaler(bouton, message) {
    const libelle = bouton.textContent;
    bouton.textContent = message;
    setTimeout(() => { bouton.textContent = libelle; }, 1500);
  }

  const boutonImprimer = document.getElementById('btn-imprimer');
  boutonImprimer.disabled = false;
  boutonImprimer.addEventListener('click', () => print());

  barre.querySelectorAll('[data-action="telecharger"]').forEach((bouton) => {
    bouton.disabled = false;
    bouton.addEventListener('click', () => {
      const nom = bouton.dataset.face;
      CarteImage.telecharger(face(nom), `carte-${barre.dataset.matricule}-${nom}.png`)
        .catch(() => signaler(bouton, 'Échec'));
    });
  });

  barre.querySelectorAll('[data-action="copier"]').forEach((bouton) => {
    if (!CarteImage.copiePossible()) {
      bouton.title = 'Copie indisponible : ouvrir la page via http://localhost ou en https';
      return;
    }
    bouton.disabled = false;
    bouton.addEventListener('click', () => {
      CarteImage.copier(face(bouton.dataset.face))
        .then(() => signaler(bouton, 'Copié ✓'))
        .catch(() => signaler(bouton, 'Échec de la copie'));
    });
  });
});
</script>
</body>
</html>
