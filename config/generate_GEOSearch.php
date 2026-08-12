<?php

// fichier pour mettre les articles de la dabase dans tntsearch pour la recherche par geolocalisation
// pour relancer ce fichier, il faut d'abord supprimer le fichier: geo_search.index

require('../vendor/autoload.php');
require "database.php";
global $db;

use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\Indexer\TNTGeoIndexer;

$tnt_geo = new TNTGeoIndexer;

$tnt_geo->loadConfig([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'dipla',
    'username'  => 'root',
    'password'  => '',
    'storage'   => '.',
    'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
]);

$tnt_geo->createIndex('geo_search.index');
$tnt_geo->setPrimaryKey('id_item');
$tnt_geo->query('SELECT id_item, latitude, longitude FROM produits;');
$tnt_geo->run();

?>