CHU50 – GESTIONNAIRE DE CARTES PHP/XAMPP (version 2, modèle paysage)

Fonctionnement :
1. Le modèle de carte (recto + verso paysage) est fixe : assets/modele/.
2. L'opérateur saisit ID, nom, postnom, prénom, fonction et importe la PHOTO de l'agent.
3. La carte se compose automatiquement (aperçu en direct, recto / verso).
4. Le QR code est généré automatiquement à partir de l'ID, sans Internet.
5. Enregistrer : informations + photo + URL du QR stockées en MySQL.
6. Rechercher, modifier et réimprimer plus tard (format CR80, 85,6 x 54 mm).

QR : https://chu-cinquantenaire.cd/employes/{ID}  (modifiable : config/config.php)
Changer le design : remplacer assets/modele/recto.png et verso.png (2411 x 1560 px).

Installation : voir INSTALLATION.txt
Détail des modifications : voir CHANGEMENTS.txt
