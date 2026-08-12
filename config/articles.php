<?php 
//calcule note 
// on récupère la note 
$note_atcl = $articles['note'];

// on recupères les coords
$latitudeTo =  $articles['latitude'];
$longitudeTo =  $articles['longitude'];

if (!empty($_SESSION['latitudeFrom']) && !empty($_SESSION['longitudeFrom'])) {
  $coords_lat_from = $_SESSION['latitudeFrom'];
  $coords_lng_from = $_SESSION['longitudeFrom'];
}else{
  $coords_lat_from ='';
  $coords_lng_from = ''; 
}

// distance rayon
//on vérifie que les coordonnées sont bonne
if ($coords_lat_from != '' && $coords_lng_from != '' && $latitudeTo != '' && $longitudeTo != '' ) {
  global $dist_oiseau;
  if (($coords_lat_from == $latitudeTo) && ($coords_lng_from == $longitudeTo)) {
   $dist_oiseau = '0km';
}
else {
  $theta = $coords_lng_from - $longitudeTo;
  $dist = sin(deg2rad($coords_lat_from)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($coords_lat_from)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $dist_oiseau =  round(($miles * 1.609344),2).'km' ;
};
}
if (!empty($dist_oiseau)) {
	$distanceC = $dist_oiseau;
}else{
	$distanceC = 'erreur de géolocalisation';
}
//fin calcule distance

$idUser = null;
//source de ajout au fav
if (!empty($_SESSION['id_user'])) {
	$idUser = htmlspecialchars($_SESSION['id_user']);
	$favoris = $db->prepare('SELECT id_item FROM favoris WHERE id_item = ? AND id_user = ?');
	$favoris->execute(array($articles['id_item'],$idUser));
	if($favoris->rowCount() >= 1){
	 $src = 'fill';
	}else{
		$src = 'unfill';
	}
}else{
	$src = 'unfill';
}
?>

<div class="contain_all">
	<div class="enveloppe_produit">
		<div class="containe_fav_1" onclick="traitementFavoris(<?=$articles['id_item']?>,event);">
			<?php 
			if ($src == 'fill') { ?>
				<i class="las la-heart"></i>
			<?php }elseif ($src == 'unfill') { ?>
				<i class="lar la-heart"></i>
			<?php } ?>
		</div>
		<a href="page_artcl.php?id=<?=$articles['id_item']?>" class="lien_prdt" >
		<img class="img_prdt" src="<?='img produits/img portrait/'. $articles['id_item'] . 'a.png'?>">
		<section class="info_prdt">
			<article class="containe_titre" >
				<span><?=$articles['titre']?></span>
			</article>
			<element class="conteneur_small" >
				<article class="containe_info">
					<i class="las la-map-marker"></i>
					<span><?=$articles['addrs']?></span>
				</article>
				<article class="containe_info">
					<span><?=$articles['prix']."€"?></span>
				</article>	
				<article class="containe_info">
					<span><?=$distanceC?></span>
				</article>	
				<article class="containe_info">
					<span>
						<?php
						for ($i=0; $i < 5 ; $i++) { 
				          if ($i < $note_atcl) { ?>
				           <span class="star_nota" style="color: yellow">★</span>
				          <?php
				        }else{ ?>
				           <span class="star_nota" style="color: #d3d3d3">★</span>
				         <?php }
				        }
				        ?>
					</span>
				</article>
			</element>	
		</section>
		</a>
		<section class="containe_fav" onclick="traitementFavoris(<?=$articles['id_item']?>,event);">
			<?php 
			if ($src == 'fill') { ?>
				<i class="las la-heart"></i>
			<?php }elseif ($src == 'unfill') { ?>
				<i class="lar la-heart"></i>
			<?php } ?>
		</section>
	</div>
</div>