  <!DOCTYPE html>
<html>
<head> 
	<title>Dipla</title>
 <meta charset="utf8_general_ci"> 
 <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" >
 <link rel="icon" type="shortcut icon" href="imgLocal/shortcut.svg"/>
 <link rel="stylesheet" type="text/css" href="style.css" ver="1.1">
<link rel="stylesheet" type="text/css" href="slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="slick/slick-theme.css"/>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-core.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-service.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-mapevents.js"></script>
<script type="text/javascript" charset="utf-8" src="https://js.api.here.com/v3/3.1/mapsjs-ui.js" ></script>
<link rel="stylesheet" type="text/css" href="https://js.api.here.com/v3/3.1/mapsjs-ui.css" />

</head>
<body>
<?php	include "config/database.php";
global $db;

include "entête.php";
require('vendor/autoload.php');
use TeamTNT\TNTSearch\TNTGeoSearch;

// on récupères les id des articles qui sont a moins de 200km grâce a tntsearch si ils on peut
if (!empty($_SESSION['latitudeFrom']) && !empty($_SESSION['longitudeFrom'])) {
  $checkLat = $_SESSION['latitudeFrom'];
  $checkLng = $_SESSION['longitudeFrom'];
  $currentLocation = [
      'longitude' => $_SESSION['longitudeFrom'],
      'latitude'  => $_SESSION['latitudeFrom']
  ];

  $distance = 200; //km

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
  if (!empty($near_products_ids)) {
    $geo_condition = " AND id_item IN (". implode(',', $near_products_ids) .") ";
  }else{
    // on met une condition impossible pour qu'il n'y ai pas de résultat
    $geo_condition = " AND id_item < 0 ";
  }
}else{
  $geo_condition = ' AND id_item < 0 ';
  $checkLat = null;
  $checkLng = null;
}
?>
 
<input type="hidden" id="checkLat" value="<?=$checkLat?>">  
<input type="hidden" id="checkLng" value="<?=$checkLng?>">  
<div id="information">
  <label id="errorGPS" class="errorLabel"></label>
  <p>Afin de trouver les articles qui vous entourent et de proposer les meilleurs itinéraires, Dipla a besoin d'accéder aux coordonnées GPS fournies par votre navigateur ou de géolocaliser votre adresse.
    <br>
    <a class="linkBottomLog" href="politique.php">En savoir plus sur vos données</a> 
  </p>
  <section class="contain_btnCheck">
    <button class="btn_true main" id="showPanel">Chercher mon adresse</button>
    <button class="btn_true" onclick="get_coords();">Me geolocaliser</button>
  </section>
  <section id="adresePanel">
    <input id="showAddr" class="zne_edit main" type="text" placeholder="Mon adresse">
      <div>
        <section class="suggestContain" >
          <button type="button" class="suggest"></button>
          <button type="button" class="suggest"></button>
          <button type="button" class="suggest"></button>
          <button type="button" class="suggest"></button>
          <button type="button" class="suggest"></button>
        </section> 
      </div>
    <button class="btn_true mainAddr" onclick="sendGeo()">Valider</button>
  </section>
</div>

<div class="contenu" id="dark_back">
<div class="allSlider">
  <div class="slider" >
    <img class="slide" src="ImgLocal/livre.png">
  </div>
  <div class="slider500">
    <img class="slide" src="imgLocal/backgroundPhone.svg">
    <?php 
    // ".$geo_condition." : a mettre apres 0
      $request = $db->query("SELECT * FROM produits WHERE id_item > 0 ".$geo_condition." ORDER BY rand() LIMIT 10");
      $nb = $request->rowCount();
      if ($nb >= 2 ) {
        while ($articles = $request->fetch()){ ?>      
          <a class="containSlide" href="page_artcl.php?id=<?=$articles['id_item']?>" target="_blank">
           <img class="slideImg" src="img produits/img paysage/<?=$articles['id_item']?>a.png">
          </a>
       <?php }
      }
      ?>
  </div>
  <div class="frameTxt">

  </div>
</div>

<style type="text/css">
  .containExplain{
    width: 90%;
    display: none;
    align-items: center;
    justify-content: space-around;
    padding: 1em;
    margin-top: 2em;
  }
  .explainFrame{
    display: flex;
    text-align: center;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1em;
    width: 20%;
    background: white;
    border-radius: 0.4em;
  }
  .explainFrame i {
    margin: 0;
    font-size: 6em;
    color: #636363;
  }

  .txtExplain{
  }


</style>

<div class="containExplain">
  <section class="explainFrame">
    <i class="las la-shopping-bag"></i>
    <p class="txtExplain">ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
  </section>
  <section class="explainFrame">
    <i class="las la-search"></i>
    <p class="txtExplain">ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
  </section>
  <section class="explainFrame">
    <i class="las la-biking"></i>
    <p class="txtExplain">ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
  </section>
</div>
<div class="grand-carre">
	<div id="pré_car_nvx">
		<div class="texte-nvx">Nouveau</div>
		<div class="g-carre-nouveau">

		<!--pour id 1-->
			<?php
				$request = $db->query("SELECT * FROM produits WHERE date > date('Y-m-d H:i:s') ".$geo_condition."  ORDER BY date DESC");
        if ($request->rowCount() > 0) {
        while ($articles = $request->fetch()) {
            include "config/articles.php";
         }
        }else{
          ?><span class="emptyCarre">Aucun articles pour le moment</span> <?php
        }
			?>
		</div>	
	</div>

	<div id="pré_car_reco">
		<div class="texte-reco">Recommandations</div>
		<div class="g-carre-reco" id="recommandation">
		<!--pour id 1-->
			<?php
			//tableau ou sont sotcke les id les plus ajouter aux favoris
			$freq = [];
			// on recupère l'id des articles qui on ete le plus ajoute eu fav (frequence) par ordre croissant
			$avis = $db->query("SELECT id_item, COUNT(*) AS freq FROM favoris GROUP BY id_item ORDER BY freq DESC");

      if ($avis->rowCount() > 0) {
        while ($getId = $avis->fetch()) {
        //on les met dans un tableau
        array_push($freq, $getId['id_item']);
        }
        // on selectionne tt les artcl dont l'id est dans le tableau
          $request = $db->query("SELECT * FROM produits WHERE id_item IN (".implode(',',$freq).") ".$geo_condition." LIMIT 16 ");
          if ($request->rowCount() > 0) {
          while ($articles = $request->fetch()) {
              include "config/articles.php";
           }
          }else{
            ?><span class="emptyCarre">Aucun articles pour le moment</span> <?php
          }
      }else{
            ?><span class="emptyCarre">Aucun articles pour le moment</span> <?php
          }


			?>
		</div>
	</div>

	<div id="pré_car_note">
		<div class="texte-noté">Mieux notés</div>
		<div class="g-carre-noté" id="bestNote">
		<!--pour id 1-->
			<?php

				$request = $db->query("SELECT * FROM produits WHERE note >= 4 ".$geo_condition." LIMIT 16");
        if ($request->rowCount() > 0) {
        while ($articles = $request->fetch()) {
            include "config/articles.php";
         }
        }else{
          ?><span class="emptyCarre">Aucun articles pour le moment</span> <?php
        }
			?>
		</div>
	</div>

</div>

<?php include "footer.php" ?>

</div>
<script src="javascript/jquery.min.js"></script>
<script type="text/javascript" src="slick/slick.min.js"></script>
<script src="javascript/main.js"></script>
</body>
</html>	