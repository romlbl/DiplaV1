  <!DOCTYPE html>
<html>
<head>
	<title>Dipla</title>
 <meta charset="utf8_general_ci"> 
 <script src="javascript/jquery.min.js"></script>
 <meta name="viewport" content="width=device-width, initial-scale=1.0" >
 <link rel="icon" type="shortcut icon" href="imgLocal/shortcut.svg"/>
 <link rel="stylesheet" type="text/css" href="style.css">
<script type="text/javascript" src="https://js.api.here.com/v3/3.2/mapsjs-core.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.2/mapsjs-service.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.2/mapsjs-mapevents.js"></script>
<script type="text/javascript" charset="utf-8" src="https://js.api.here.com/v3/3.2/mapsjs-ui.js" ></script>
<link rel="stylesheet" type="text/css" href="https://js.api.here.com/v3/3.2/mapsjs-ui.css" />
</head>
<body>
<?php include "entête.php" ?>
<div class="contenu">
<div class="contenant">

<div id="carteSelect" style="display:none;">
  <a class="close_map" id="close_map" href="#">×</a>
  <div class="containVilleMap">
    <input type="text" id="villeMap"  placeholder="Rechercher">
    <section class="suggestMap">
      <button type="button" class="suggestMapBtn"></button>
      <button type="button" class="suggestMapBtn"></button>
      <button type="button" class="suggestMapBtn"></button>
      <button type="button" class="suggestMapBtn"></button>
      <button type="button" class="suggestMapBtn"></button>
    </section>
  </div>
  <div id="map"></div>
</div>

<?php include "config/database.php";
global $db;

if(!empty($_SESSION['latitudeFrom']) && !empty($_SESSION['longitudeFrom'])){
  $getLatCarte = htmlspecialchars($_SESSION['latitudeFrom']);
  $getLngCarte = htmlspecialchars($_SESSION['longitudeFrom']);
}else{
  $getLatCarte = "";
  $getLngCarte = "";
}

?>
<!-- ou il y tout -->
<div class="director">
<!-- les sliders -->

<div class="sliders">
  <label class="errorlabel" id="errorFiltre"></label>
  <div id="prix_value">Prix max : </div>
  <input type="range" min="5" max="1000" value="1000" class="slider_search" id="sliderPrix">

  <div id="dist_value">Distance max : </div>
  <input type="range" min="1" max="500" value="500" class="slider_search" id="sliderDistance">
  <div class="specLocFrame">
    <label for="specLocation" class="specLocTitle" >Chercher depuis:</label>
    <input id="specLocation" type="text" placeholder="numéro rue, code,ville">
    <div>
    <section class="suggestContain">
      <button type="button" class="suggest"></button>
      <button type="button" class="suggest"></button>
      <button type="button" class="suggest"></button>
      <button type="button" class="suggest"></button>
      <button type="button" class="suggest"></button>
    </section>
    </div>
    <a href="#carteSelect" class="btn_true" id="hrefMap">Choisir sur la carte</a> 
    <input type="hidden" id="getLatCarte" value="<?php if (!empty($getLatCarte)) {echo $getLatCarte;} ?>">
    <input type="hidden" id="getLngCarte" value="<?php if (!empty($getLngCarte)) {echo $getLngCarte;} ?>">
  </div>

  <button id="send" class="btn_send">Afficher les résultats</button>
</div>

<!-- contient btn type et artcl -->
<div class="type_artcl">
<!-- choix entre articles et produits -->
<section class="containe_type">
  <button id="artc"  class="btn_choix">Articles</button>
  <button id="srvc" class="btn_choix">Services</button>
</section>

<!-- contient les articles -->
<div id="cadre" class="cadre">
<?php

//les prérequis pour la librairie de recherche
require('vendor/autoload.php');
global $db;

use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\TNTGeoSearch;

// fonction pour la geo_rechrche
// on récupères les id des articles qui sont a moins de 200km grâce a tntsearch si on peut
function geo_search($distance){
  // le tab pour contenir les 2 returne
  $values = [];
  if (!empty($_SESSION['latitudeFrom']) && !empty($_SESSION['longitudeFrom'])) {
    $currentLocation = [
        'longitude' => $_SESSION['longitudeFrom'],
        'latitude'  => $_SESSION['latitudeFrom']
    ];

    $distance = $distance; //km

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

    $near_products_ids = $near_products->findNearest($currentLocation, $distance, 100);
    $near_products_ids = array_map('intval', $near_products_ids['ids']);
    $array_near_ids =  implode(',', $near_products_ids);
    if (!empty($array_near_ids)) {
      $geo_condition = " AND id_item IN (".$array_near_ids .") ";
    }else{
      // on met une condition impossible pour qu'il n'y ai pas se résultat
      $geo_condition = " AND id_item < 0";
    }
  }else{
    $geo_condition = ' AND id_item < 0 ';;
    $array_near_ids = [];
  }

  array_push($values, $geo_condition);
  array_push($values, $array_near_ids);

  return $values;
}
$call_func = geo_search(200);
$geo_condition = $call_func[0];
$array_near_ids = $call_func[1];
// pour la recherche normal
$tnt = new TNTSearch;
//si il y a un mot
if (!empty($_GET['query']) && $_GET['query'] != 'Hl3QXpFP' && $_GET['query'] != 'rf9d8D3H') {
  //on utilise la librairie TNTsearch pour faire la recherche
  //on sécurise la variable
  $txt_rsch = htmlspecialchars($_GET['query']);

// on appel tntsearch
$tnt->loadConfig([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'dipla',
    'username'  => 'root',
    'password'  => '',
    'storage'   => '.',
    'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
]);

$tnt->selectIndex("config/articles.index");
$tnt->fuzziness = true;

//recherche simple
$explode_search = explode(' ', $txt_rsch);
$all_ids = [];
for ($i=0; $i < count($explode_search) ; $i++) { 
  if (strlen($explode_search[$i]) > 2) {
    $searchResult = $tnt->search($explode_search[$i]);
    $all_ids = array_merge($all_ids,$searchResult['ids']);
  }
}
$all_ids = array_unique($all_ids);
$ids = implode(', ', $all_ids);
if (!empty($ids)) {
  $rsch = $ids;

  $prod = $db->query("SELECT * FROM produits WHERE id_item IN ($ids) ".$geo_condition." ORDER BY FIELD(id_item, $ids)");

  // si il y a des résultats
  if ($prod->rowCount() > 0) {
    while ($articles = $prod->fetch()) {
      include "config/articles.php";    
    }
  }else{
    ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php 
  }
}
//sinon on affiche qu'il y a rien
else{
  ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php 
}

}
// si cliquer sur "à proximité"
if (!empty($_GET['query']) && $_GET['query'] == 'Hl3QXpFP') {
  //on dis a js qu'ona cliquer sur "à proximité"
  $rsch = 'proxi';

  // on cherche les artciles a moins de 20km avec tntsearch

$call_func = geo_search(20);
$geo_condition = $call_func[0];
$array_near_ids = $call_func[1];

// on vérifie qu'on trouver des prdt a proxi
if (!empty($array_near_ids)) {
  $query_near = $db->query("SELECT * FROM produits WHERE id_item > 0 ".$geo_condition." ORDER BY FIELD(id_item, $array_near_ids) ");
  // si il y a des résulutats
  if ($query_near->rowCount() > 0) {
    while ($articles = $query_near->fetch()) {
      include "config/articles.php";
    }
  }else{
    ?><div class="fond"> Il n'y a aucun articles à proximité pour l'instant</div><?php
  }
}else{
    ?><div class="fond"> Il n'y a aucun articles à proximité pour l'instant</div><?php
  }

  //on enleve la variable session sinon ca bloque tous vu qu'elle exitera tt le temps
  // unset($_GET['query']);

}
// si cliquer sur "découvrir"
if (!empty($_GET['query']) && $_GET['query'] == 'rf9d8D3H') {
  //on dis a js qu'ona cliquer sur "découvrir"
  $rsch = 'decouvrir';
// on vérifie qu'on trouver des prdt a proxi
  if (!empty($array_near_ids)) {
    //on affiche 1000 articles au hasard, il faut un where pour mettre $geo_condition qui commence par AND
    $prod = $db->query("SELECT * FROM produits WHERE id_item > 0 ".$geo_condition."  ORDER BY FIELD(id_item, $array_near_ids)");
    if ($prod->rowCount() > 0) {
      while ($articles = $prod->fetch()) {
        include "config/articles.php";    
      }
    }else{
      ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php
    }
  }
  else{
        //on affiche 1000 articles au hasard
    $prod = $db->query("SELECT * FROM produits WHERE id_item > 0 ".$geo_condition." ORDER BY rand() LIMIT 1000 ");
    if ($prod->rowCount() > 0) {
      while ($articles = $prod->fetch()) {
        include "config/articles.php";    
      }
    }else{
      ?><div class="fond"> Aucun résultats à votre recherche pour l'instant</div><?php
    }
  }

}

 ?>
</div>
</div>

</div>
</div>
<?php include'footer.php' ?>
</div>
<!-- input pour faire passer la recherche qu'on a fait (barre de recherch, btn,...) -->
<input type="hidden" id="mot_rsch" value="<?php if (!empty($rsch)) {echo $rsch;} ?>">
</body>
<script src="javascript/recherche.js"></script>
</html>
