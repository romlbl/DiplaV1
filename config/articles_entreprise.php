 <?php 
//calcule note
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

?>	
<div class="enveloppe_produitPhone" >
	<a href="page_artcl.php?id=<?=$articles['id_item']?>" class="lien_prdtPhone" target="_blank" >
		<img class="img_prdtPhone" src="<?='img produits/img portrait/'. $articles['id_item'] . 'a.png'?>">
		<section class="info_prdtPhone">
			<article class="containe_titrePhone">
				<span><?=$articles['titre']?></span>
			</article>
			<section class="conteneur_smallPhone" >
				<article class="containe_infoPhone">
					<i class="las la-map-marker"></i>
					<span><?=$articles['addrs']?></span>
				</article>
				<article class="containe_infoPhone">
					<span><?=$articles['prix']."€"?></span>
				</article>	
				<article class="containe_infoPhone">
					<span><?=$distanceC?></span>
				</article>	
				<article class="containe_info_notePhone" >
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
			</section>
		</section> 
	</a>
	<a href="#big_lower" class="btn_true" onclick="SelectCustomItem(<?=$articles['id_item']?>);">
		<span class="modifArtcl">Modifier</span>
		<i class="lar la-edit"></i>
	</a>
</div>

