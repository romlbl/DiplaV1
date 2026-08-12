<!DOCTYPE html>
<html>
<head> 
	<title>Dipla</title>
<meta charset="utf8_general_ci"> 
 <meta name="viewport" content="width=device-width, initial-scale=1.0" >
<link rel="icon" type="shortcut icon" href="imgLocal/shortcut.svg"/>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-core.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-service.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-mapevents.js"></script>
<script type="text/javascript" charset="utf-8" src="https://js.api.here.com/v3/3.1/mapsjs-ui.js" ></script>
<link rel="stylesheet" type="text/css" href="https://js.api.here.com/v3/3.1/mapsjs-ui.css" />
<link rel="stylesheet" type="text/css" href="style.css" ver="1.1">
<script src="javascript/jquery.min.js"></script>
<link rel="stylesheet" href="line-awesome-1.3.0/1.3.0/css/line-awesome.min.css">
</head>
<body>
<?php	include "config/database.php";
global $db;

require('vendor/autoload.php');

use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\TNTGeoSearch;
?>

<?php include "entête.php" ?>

<div class="contenu">
<div class="contenant">

<?php
$id_produit = htmlspecialchars($_GET['id']);
// si l'atcl existe
$tryAtcl = $db->prepare('SELECT * FROM produits WHERE id_item = ? ');
$tryAtcl->execute(array($id_produit));

if ($tryAtcl->rowCount() > 0) {

// pour ajouter l'artcl a l'historique
if (!empty($_SESSION['id_user'])) {
  $id_user =  htmlspecialchars($_SESSION['id_user']);
  // tab pour stocker les ids
  $tabIds = [];
  // on vérifie que le dernier artcl histo n'est pas celui la
  $last = $db->prepare('SELECT id_item  FROM historique WHERE id_user = ? ORDER BY date ASC');
  $last->execute(array($id_user));
  // on check si il en a 
  if ($last->rowCount() > 0) {
    // on recup les ids de l'histo
    while ($getIds = $last->fetch()) {
      array_push($tabIds, $getIds[0]);
    }
    $oldestId = $tabIds[0];
    foreach ($tabIds as $element) {
      $tabIds[] = $element[0];
    }
    // si il n'est pas deja dedans
    if (!in_array($id_produit, $tabIds)) {
      // si il y en a moins de 7
      if ($last->rowCount() < 7) {
        // on ajoute
        $add_histo = $db->prepare("INSERT INTO historique (id_user,id_item) VALUES (?,?) ");
        $add_histo -> execute(array($id_user,$id_produit)); 
      }else{
        var_dump($last->rowCount());
        // on suppr le plus longtemps et on met lui
        $add_histo = $db->prepare("INSERT INTO historique (id_user,id_item) VALUES (?,?) ");
        $add_histo -> execute(array($id_user,$id_produit));
        $del_last = $db->prepare('DELETE FROM historique WHERE id_item = ?');
        $del_last->execute(array($oldestId));
      }
    }
  }else{
    // on ajoute
    $add_histo = $db->prepare("INSERT INTO historique (id_user,id_item) VALUES (?,?) ");
    $add_histo -> execute(array($id_user,$id_produit)); 
  }
}else{
  $id_user = null;
}
 
if (!empty($_SESSION['latitudeFrom']) && !empty($_SESSION['longitudeFrom'])) {
  $coords_lat_from = $_SESSION['latitudeFrom'];
  $coords_lng_from = $_SESSION['longitudeFrom'];
}else{
  $coords_lat_from ='';
  $coords_lng_from = ''; 
}

// on recupères les info de l'articles
$query = $db->prepare('SELECT * FROM produits WHERE id_item = ? ');
$query->execute(array($id_produit));
$mainArtcl = $query->fetch();

$date = date("d/m/y",strtotime($mainArtcl['date']));
// on récupère la note
$note = $mainArtcl['note'];
// on récupère les coodrs de l'articles
$latitudeTo = $mainArtcl['latitude'];
$longitudeTo = $mainArtcl['longitude'];

// pour les artcl en lien
$config = [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'dipla',
    'username'  => 'root',
    'password'  => '',
    'storage'   => '.',
    'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
];
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
    $geo_condition = " AND id_item IN (".$array_near_ids .") ";
  }else{
    $geo_condition = ' AND id_item < 0 ';
    $array_near_ids = [];
  }

  array_push($values, $geo_condition);
  array_push($values, $array_near_ids);

  return $values;
}
$call_func = geo_search(200);
$geo_condition = $call_func[0];
// pour la recherche normal
$tnt = new TNTSearch;
$tnt->loadConfig($config);
$tnt->selectIndex("config/articles.index");
$tnt->fuzziness = true;


?>
<!-- ces input sont invisible et sert juste a passer les coordonée en php vers le js -->
<input type="hidden" id="coords_lat" value=<?=$latitudeTo ?>>
<input type="hidden" id="coords_lng" value=<?=$longitudeTo ?>>
<input type="hidden" id="coords_lat_from" value=<?=$coords_lat_from ?>>
<input type="hidden" id="coords_lng_from" value=<?=$coords_lng_from ?>>
<input type="hidden" id="id_produit" value=<?=$id_produit ?>>

<div id="conteneurInfoImg">
  <section id="allImg">
    <section id="conteneurSub">
      <img class="subImg" id="sub_a" onclick="selectImg('a',<?=$id_produit?>)" src="img produits/img portrait/<?=$id_produit?>a.png">
      <?php 
      if (file_exists('img produits/img portrait/'.$id_produit.'b.png')) { ?>
        <img class="subImg" id="sub_b" onclick="selectImg('b',<?=$id_produit?>)" src="img produits/img portrait/<?=$id_produit?>b.png">
      <?php } ?>
      <?php 
      if (file_exists('img produits/img portrait/'.$id_produit.'c.png')) { ?>
        <img class="subImg" id="sub_c" onclick="selectImg('c',<?=$id_produit?>)" src="img produits/img portrait/<?=$id_produit?>c.png">
      <?php } ?>
    </section>
    <img class="imgProduit" id="imgProduit" src="img produits/img portrait/<?=$id_produit?>a.png">
  </section>
  <div id="productInfo">
  <section id="infoProduit">
    <span class="mainInfo"><?=$mainArtcl['titre']?></span>
    <span class="mainInfo"><?=$mainArtcl['prix']?>€</span>
    <br>
    <label class="typeInfo" for="adresse">Adresse : </label>
    <span class="info" id="adresse"><?=$mainArtcl['addrs']?></span>
    <br>
    <div id="box_iti">
      <section id="infoIti">

        <label class="typeInfo" for="tps">Temps(voiture) :</label>
        <span class="info" id="tps"></span>
        <label class="typeInfo" for="dstc">Distance :</label>
        <span class="info" id="dstc"></span>
      </section>
      <a class="showMap" href="#carte">Afficher la carte</a>
    </div>
    <br>
    <label class="typeInfo" for="dscrp">Description : </label>
    <span class="info" id="dscrp"><?=$mainArtcl['contenu']?></span>
    <br>
    <label class="typeInfo" for="dscrp">Mis en ligne le : </label>
    <span class="info" id="dscrp"><?=$date?></span>
    <br>
    <section id="linkNote">
      <a href="#interaction" onclick="interaction('avs');" class="linkspace">
        <?php
        for ($i=0; $i < 5 ; $i++) { 
          if ($i < $note) { ?>
            <span  class="star_nota" style="color: yellow;" >★</span>
          <?php
        }else{ ?>
          <span  class="star_nota" style="color: #d3d3d3;" >★</span>
         <?php }
        }
        ?>
      </a>
      <small> | </small>
      <a href="#interaction" onclick="interaction('qst');" class="linkspace">Questions</a>
    </section>
  </section>

  </div>

</div>

<div id="carte">
  <a class="close_map" href="#">×</a>
  <div id="map"></div>
</div>

<div id="enLien" >
  <h3 class="titleLien">En lien</h3>
  <section class="arclEnLien">
    <?php
      $splitKey = explode("/", $mainArtcl['keyWords']);
      $resultArray = array();
      for ($i=0; $i < count($splitKey) ; $i++) { 
        $mot =  $splitKey[$i];
        $searchResult = $tnt->search($mot);
        $resultArray = array_merge($resultArray, $searchResult['ids']);
      }
      $countValues = array_count_values($resultArray);
      $filteredArray = array_filter($countValues, function($value) {
          return $value >= 2;
      });
      $finalResult = array_keys($filteredArray);
      $finalResult = array_diff($finalResult,[$mainArtcl['id_item']]);
      $finalResult = implode(', ', $finalResult);
      if (strlen($finalResult) > 0) {
        $linkQuery = $db->query("SELECT * FROM produits WHERE id_item IN ($finalResult) ORDER BY FIELD(id_item,$finalResult)");
        if ($linkQuery->rowCount() > 0) {
          while ($articles = $linkQuery->fetch()) {
            include "config/articles.php";    
          }
        }else{
          ?><span class="NoEnLien">Auncun articles en lien trouvés</span><?php 
        }
      }else{
          ?><span class="NoEnLien">Auncun articles en lien trouvés</span><?php 
        }
    ?>
  </section>
</div>

<div id="interaction">
  <section class="whichInteraction">
    <a onclick="interaction('avs');" class="titleInter">Avis</a>
    <a onclick="interaction('qst');" class="titleInter">Questions</a>
  </section>
  <div id="showInteraction"></div>

</div>
<?php }else{ ?>

<div class="mainDenied">
  <h2 style="color: white;">Cet article n'existe pas encore</h2>
  <section id="contain_btn_info">
    <a class="homeDenied" href="../main.php">Retourner sur dipla</a>
  </section>
</div>


<?php } ?>
</div>
 <?php include "footer.php" ?>
</div>

</body>
<script src="javascript/page_artcl.js"></script>
</html>
