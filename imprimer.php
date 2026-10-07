<?php
require_once __DIR__.'/config/database.php';require_once __DIR__.'/includes/fonctions.php';require_once __DIR__.'/includes/carte.php';
$c=cardById($pdo,(int)($_GET['id']??0));if(!$c){http_response_code(404);die('Carte introuvable.');}?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Impression <?=e($c['matricule'])?></title>
<link rel="stylesheet" href="assets/css/carte.css"><style>
/* Format carte CR80 : 85,6 x 54 mm, une page par face (recto puis verso) */
@page{size:85.6mm 53.98mm;margin:0}
*{box-sizing:border-box}body{margin:0;background:#d9dee5;font-family:Arial,sans-serif}
.bar{text-align:center;background:#fff;padding:12px;font-size:14px}.bar button{padding:8px 16px;font-size:14px;cursor:pointer}.bar small{display:block;color:#667;margin-top:6px}
.sheet{display:flex;flex-direction:column;align-items:center;gap:8mm;padding:8mm 0}
/* Le modèle (2411x1560) est un peu plus haut que le format CR80 : il est centré et
   rogné de 0,7 mm en haut et en bas (zones blanches / frise), sans déformation. */
.page{position:relative;width:85.6mm;height:53.98mm;overflow:hidden;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.2)}
.page .carte{position:absolute;left:0;top:calc((53.98mm - 85.6mm * 1560 / 2411) / 2);width:85.6mm}
@media print{html,body{background:#fff;margin:0;padding:0}.bar{display:none}.sheet{display:block;padding:0}.page{box-shadow:none;break-after:page}.page:last-child{break-after:auto}}
</style></head><body>
<div class="bar"><button id="btn-imprimer" disabled>Imprimer</button><small>Dans la fenêtre d’impression : marges « Aucune », échelle 100 %, cocher « Graphiques d’arrière-plan ».</small></div>
<div class="sheet"><div class="page"><?=renderRecto($c)?></div><div class="page"><?=renderVerso()?></div></div>
<script src="assets/js/qrcode.js"></script><script src="assets/js/carte.js"></script>
<script>document.addEventListener('cartes-pretes',function(){var b=document.getElementById('btn-imprimer');b.disabled=false;b.onclick=function(){print();};});</script>
</body></html>
