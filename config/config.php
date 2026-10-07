<?php
declare(strict_types=1);
const APP_NAME='CHU50 – Gestionnaire de cartes de service';
const QR_BASE_URL='https://chu-cinquantenaire.cd/employes/';
const DB_HOST='localhost';
const DB_NAME='chu50_cartes';
const DB_USER='root';
const DB_PASS='';
const DB_SOCKET='/tmp/mysql.sock';
const MODELE_RECTO='assets/modele/recto.png';
const MODELE_VERSO='assets/modele/verso.png';
const UPLOAD_DIR=__DIR__.'/../uploads/';
const PHOTO_DIR=UPLOAD_DIR.'photos/';
const MAX_UPLOAD_SIZE=10485760;
const ALLOWED_MIME=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
date_default_timezone_set('Africa/Kinshasa');
