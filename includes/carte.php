<?php
declare(strict_types=1);

/*
 * Rendu d'une carte de service à partir du modèle fixe (paysage).
 * Le même HTML sert à l'aperçu, à la fiche et à l'impression :
 * les positions sont en % du modèle, donc identiques quelle que soit la taille.
 */

/* Champ de la carte : la valeur saisie, ou un libellé indicatif (classe « vide ») si elle est absente */
function champCarte(string $nom, string $valeur, string $defaut): string
{
    return $valeur !== ''
        ? '<span data-champ="' . $nom . '">' . e($valeur) . '</span>'
        : '<span data-champ="' . $nom . '" class="vide">' . $defaut . '</span>';
}

/* $c peut être vide (aperçu d'une nouvelle carte) : des libellés indicatifs s'affichent. */
function renderRecto(array $c = []): string
{
    $champ = fn(string $nom, string $defaut): string => champCarte($nom, (string)($c[$nom] ?? ''), $defaut);

    $matricule = (string)($c['matricule'] ?? '');
    $qr        = $matricule !== '' ? qrUrl($matricule) : '';

    $photo    = (string)($c['photo'] ?? '');
    $zoom     = (float)($c['photo_zoom'] ?? 1);
    $position = (int)($c['photo_x'] ?? 50) . '% ' . (int)($c['photo_y'] ?? 30) . '%';
    $cadrage  = 'object-position:' . $position . ';transform-origin:' . $position . ';transform:scale(' . $zoom . ')';

    $nomComplet  = $champ('nom', 'NOM') . $champ('postnom', 'POSTNOM') . $champ('prenom', 'PRÉNOM');
    $classePhoto = 'carte-photo' . ($photo === '' ? ' sans-photo' : '');

    ob_start();
    ?>
<div class="carte carte-recto" data-qr-base="<?= e(QR_BASE_URL) ?>" data-qr="<?= e($qr) ?>">
    <img class="carte-fond" src="<?= e(MODELE_RECTO) ?>" alt="">
    <div class="carte-texte">
        <div class="carte-nom"><?= $nomComplet ?></div>
        <div class="carte-fonction"><?= $champ('fonction', 'Fonction') ?></div>
    </div>
    <div class="carte-id"><span>ID:<?= $champ('matricule', '00000000') ?></span></div>
    <div class="carte-qr"></div>
    <div class="<?= $classePhoto ?>"><img src="<?= e($photo) ?>" style="<?= e($cadrage) ?>" alt=""><span>PHOTO</span></div>
</div>
<?php
    return (string)ob_get_clean();
}

function renderVerso(): string
{
    return '<div class="carte carte-verso"><img class="carte-fond" src="' . e(MODELE_VERSO) . '" alt=""></div>';
}
