<?php
session_start();
include "config/database.php";
global $db;

//les prérequis pour la librairie de recherche
require('vendor/autoload.php');

use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\TNTGeoSearch;


// fonction pour la geo_recherche
// on récupères les id des articles qui sont a moins de 200km grâce a tntsearch si on peut
function geo_search($distance){
  // le tab pour contenir les 2 returne
  $values = []; 
  //traitement si il y a une recherche depuis un autre adresse
  if (!empty($_POST['specLat']) || !empty($_POST['specLng'])) {
    $_SESSION['latitudeFrom'] = htmlspecialchars($_POST['specLat']);;
    $_SESSION['longitudeFrom'] = htmlspecialchars($_POST['specLng']);;
  }

  if (!empty($_SESSION['latitudeFrom']) && !empty($_SESSION['longitudeFrom'])) {
    $Location = [
        'longitude' => $_SESSION['longitudeFrom'],
        'latitude'  => $_SESSION['latitudeFrom']
    ];

    $near_products = new TNTGeoSearch();
    $near_products->loadConfig([
      'driver'    => 'mysql',
      'host'      => 'localhost',
      'database'  => 'dipla',
      'username'  => 'root',
      'password'  => '',
      'storage'   => '.',
      'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
  ]);
    $near_products->selectIndex('config/geo_search.index');

    $near_products_ids = $near_products->findNearest($Location, $distance, 100);
    $near_products_ids = array_map('intval', $near_products_ids['ids']);
    $array_near_ids =  implode(',', $near_products_ids);
    if (!empty($array_near_ids)) {
      $geo_condition = " AND id_item IN (".$array_near_ids .") ";
    }else{
      // on met une condition impossible pour qu'il n'y ai pas se résultat
      $geo_condition = " AND id_item < 0";
    }
  }else{
    $geo_condition = ' AND id_item < 0 ';
    $array_near_ids = [];
  }

  array_push($values, $geo_condition);
  array_push($values, $array_near_ids);

  return $values;
}
// on récupères les id des articles qui sont a moins de 200km grâce a tntsearch si on peut
$call_func = geo_search(200);
$geo_condition =  $call_func[0];
$array_near_ids = $call_func[1];
// on récupère les ids des resultat ou le mode de recherche
if (!empty($_POST['rslt_ids'])) {
  $received_ids = htmlspecialchars($_POST['rslt_ids']);
}

// on récupère toutes les variable si elles existent
if (!empty($_POST['type'])) {
  $type = htmlspecialchars($_POST['type']);
  $type = ' "'.$type.'" ';
  $STRcondition_T = "AND type = ".$type." ";
}else{
  $STRcondition_T = "";
}

if (!empty($_POST['conditP']) && $_POST['conditP'] != 1000 ) {
  $conditP = htmlspecialchars($_POST['conditP']);
  $STRcondition_P = "AND prix <= ".$conditP."";
}else{
  $STRcondition_P = "";
}

if (!empty($_POST['conditD']) && $_POST['conditD'] != 500) {
  $conditD = htmlspecialchars($_POST['conditD']);

$call_func = geo_search($conditD);
$geo_condition =  $call_func[0];
$array_near_ids = $call_func[1];
}

// on traite cas par cas
// recheches normales parmis les ids donnés
if (!empty($received_ids) && $received_ids != 'decouvrir' && $received_ids != 'proxi') {
  // on faite une requête avec les params choissit avant
  $prod = $db->query("SELECT * FROM produits WHERE id_item IN ($received_ids) ".$geo_condition." ".$STRcondition_P." ".$STRcondition_T." ORDER BY FIELD(id_item, $received_ids)");
  // si il y a des résultats
  if ($prod->rowCount() > 0) {
    while ($articles = $prod->fetch()) {
      include "config/articles.php";    
    }
  }else{
    ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php 
  }
}
// si cliqué sur proxi
if (!empty($received_ids) &&  $received_ids == 'proxi') {
  // on reclacule la recherche gps
$call_func = geo_search(20);
$geo_condition =  $call_func[0];
$array_near_ids = $call_func[1];

// si on trouver des obj a proxi
if (!empty($array_near_ids)) {
  // on faite une requête avec les params choissit avant
  $prod = $db->query("SELECT * FROM produits WHERE id_item > 0 ".$geo_condition." ".$STRcondition_P." ".$STRcondition_T." ORDER BY FIELD(id_item, $array_near_ids)");
  // si il y a des résultats
  if ($prod->rowCount() > 0) {
    while ($articles = $prod->fetch()) {
      include "config/articles.php";    
    }
  }else{
    ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php 
  }
}else{
    ?><div class="fond"> Problème de géolocalisation</div><?php 
  }

}

// si cliqué sur decouvrir
if (!empty($received_ids) && $received_ids == 'decouvrir') {

// on vérifie qu'on trouver des prdt a proxi
  if (!empty($array_near_ids)) {
    //on affiche 1000 articles au hasard, il faut un where pour mettre $geo_condition qui commence par AND
    $prod = $db->query("SELECT * FROM produits WHERE id_item > 0 ".$geo_condition." ".$STRcondition_P." ".$STRcondition_T." ORDER BY FIELD(id_item, $array_near_ids)");
    if ($prod->rowCount() > 0) {
      while ($articles = $prod->fetch()) {
        include "config/articles.php";    
      }
    }else{
      ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php
    }
  }
  else{
        //on affiche 1000 articles au hasard mais avec les conditions
    $prod = $db->query("SELECT * FROM produits WHERE id_item > 0 ".$geo_condition." ".$STRcondition_P." ".$STRcondition_T."");
    if ($prod->rowCount() > 0) {
      while ($articles = $prod->fetch()) {
        include "config/articles.php";    
      }
    }else{
      ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php
    }
  }

}

else if(empty($received_ids)){
  ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php
}


?>
