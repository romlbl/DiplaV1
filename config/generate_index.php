<?php
// fichier pour mettre les articles de la dabase dans tntsearch
// pour relancer ce fichier, il faut d'abord supprimer le fichier: articles.index

require('../vendor/autoload.php');
require "database.php";
global $db;

use TeamTNT\TNTSearch\TNTSearch;

$tnt = new TNTSearch;

$tnt->loadConfig([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'dipla',
    'username'  => 'root',
    'password'  => '',
    'storage'   => '.',
    'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
]);

$indexer = $tnt->createIndex('articles.index');
$indexer->setPrimaryKey('id_item');
$indexer->query('SELECT id_item, titre, contenu, keyWords FROM produits');
$indexer->setLanguage('french');
$indexer->run();

?>