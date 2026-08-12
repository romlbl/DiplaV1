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
  <script type="text/javascript" src="javascript/cropper.js"></script>
 <link rel="stylesheet" type="text/css" href="cropper.css">
</head>

<body>

<?php 
include "entête.php";
include "config/database.php";
global $db;

if(!empty($_SESSION['id_entreprise'])){

$id_entre = $_SESSION['id_entreprise'];
$customId = "new";

// recuperation données
$query = $db->prepare("SELECT * FROM entreprise WHERE id_entre = ?");
$query->execute(array($id_entre));
if($query->rowCount() > 0){
	while ($info_entre = $query->fetch()) {
		$date = date("d/m/y",strtotime($info_entre['date']));
		$getLatCarte = $info_entre['lat_entre'];
		$getLngCarte =  $info_entre['lng_entre'];
		$_SESSION['latitudeFrom'] = $getLatCarte;
		$_SESSION['longitudeFrom'] = $getLngCarte;
		//stats entre
		$query_view_entre = $db->prepare("SELECT COUNT(*) AS nb_ligne FROM historique WHERE id_item IN (SELECT id_item FROM produits WHERE id_entre = ?)");
		$query_view_entre->execute(array($id_entre));
		$view_entre = $query_view_entre->fetch();
		$view_entre = $view_entre[0];
		$query_fav_entre = $db->prepare("SELECT COUNT(*) AS nb_ligne FROM favoris WHERE id_item IN (SELECT id_item FROM produits WHERE id_entre = ?)");
		$query_fav_entre->execute(array($id_entre));
		$fav_entre = $query_fav_entre->fetch();
		$fav_entre = $fav_entre[0];
?>

<!-- la fenêtre de crop -->
<div id="crop_window">
	<div id="croppedImg"></div>
	<section id="contain_btn_preview" >
		<div id="cropPreview"></div>
		<section id="containe_btn">
			<button id="cropButton" class="btn_true">Valider</button>
			<button id="cancelButton" class="btn_true">Annuler</button>	
			<button id="rotateButton" class="btn_true">Rotation</button>
		</section>
	</section>
</div>

<!-- la carte -->
<input type="hidden" id="getLatCarte" value="<?php if (!empty($getLatCarte)) {echo $getLatCarte;} ?>">
<input type="hidden" id="getLngCarte" value="<?php if (!empty($getLngCarte)) {echo $getLngCarte;} ?>">

<div id="carteSelectAdd" style="display: none;">
  <a class="close_map" id="close_map" href="#big_lower">×</a>
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

<!-- setting et modif -->

<div id="setting" class="set_part">
  <element class="element_set_edit">
    <span class="name_edit_set">Paramètre</span>
    <a href="#" class="link_close_set_edit">
      <i class="las la-times"></i>
    </a>
  </element>
  <element class="main_set_edit">
    <section class="section_set_edit">
      <nav>
        <span class="title_section">Compte :</span>
        <li class="list_section">
          <a href="nvx_mdp/token.php" class="list_compte">Modifier le mot de passe</a>
        </li>
        <li class="list_section">
          <a href="#confirmation" style="color: red;" class="list_compte">Supprimer mon entreprise</a>
        </li>
      </nav>
    </section>

    <section class="section_set_edit">
      <nav>
        <span class="title_section">Aide :</span>
          <li class="list_section">
            <a href="contact.php" class="list_compte" style="color: #e28989">Signaler un problème</a>
          </li>
          <li class="list_section">
            <a href="politique.php" target="_blank" class="list_compte" style="color: #e28989">Politique de confidentialité</a>
          </li>
      </nav>
    </section>
  </element>
</div>


<div id="edit" class="edit_part">
	<section class="element_set_edit">
		<span class="name_edit_set">Modifier</span>
		<a href="#" class="link_close_set_edit">
			<i class="las la-times"></i>
		</a>
	</section>
	<label class="errorlabel" id="errorEdit"></label>
	<a href="politique.php" target="_blank" class="list_compte" style="color: #e28989">Politique de confidentialité</a> 
	<element class="element_edit">
		<span class="edit_type">Nom :&nbsp </span>
		<input class="zne_edit" type="text" id="edit_name" value="<?=$info_entre['nom_entre']?>">
	</element>
	<element class="element_edit">
		<span class="edit_type">Mail :&nbsp </span>
		<input class="zne_edit" type="text" id="edit_mail" value="<?=$info_entre['mail_entre']?>">
	</element>
	<element class="element_edit">
		<span class="edit_type">Adresse :&nbsp </span>
		<input class="zne_edit" type="text" id="edit_addr" value="<?=$info_entre['addrs_entre']?>">
		<div>
			<section class="suggestContain" >
			  <button type="button" class="suggestEdit"></button>
			  <button type="button" class="suggestEdit"></button>
			  <button type="button" class="suggestEdit"></button>
			  <button type="button" class="suggestEdit"></button>
			  <button type="button" class="suggestEdit"></button>
			</section> 
		</div>
	</element>
	<element  class="element_edit" id="none_adresse">
		<span class="edit_type">Changer l'adresse de mes produits</span>
		<input type="checkbox" id="checkNone">
	</element>
	<input type="hidden" id="putLatEdit" value="<?=$info_entre['lat_entre']?>">
	<input type="hidden" id="putLngEdit" value="<?=$info_entre['lng_entre']?>">
	<element class="containe_valider">
        <input class="valide_set_edit" type="submit" onclick="ModifEntre();" value="Valider">
    </element>
</div>

<div id="confirmation">
	<span class="txt_del_account">Confirmez-vous la suppression de toutes vos données ainsi que tous vos produits sur Dipla ?</span>
	<section class="containe_valider">
		<a href="#" class="anul_del_account">Annuler</a>
	    <input type="submit" class="valide_set_edit" onclick="deleteEntre(<?=$id_entre?>)" value="Confirmer">
	</section>
</div>

<div class="contenu">
<div class="contenant" >


<section class="big_upper" >
	<section class="upper_left">
		<div class="containLeft">
			<a class="linkLeft" href="#setting">
				<span class="txtLeft">Paramètre</span>
				<i class="las la-cog"></i>
			</a>
			<a class="linkLeft" href="#edit">
				<span class="txtLeft">Modifier</span>
				<i class="las la-puzzle-piece"></i>
			</a>	
			<a class="linkLeft" href="#big_lower" onclick="showAddPanel()">
				<span class="txtLeft">Ajouter</span>
				<i class="las la-shopping-bag"></i>
			</a>		
		</div>
		<div class="containLeft">
			<a class="linkLeft" href="config/deconnexion.php">
				<span class="txtLeft">Déconnexion</span>
				<i class="las la-arrow-circle-left"></i>
			</a>
		</div>	
	</section>
	<section class="upper_middle">
		<div class="containerInfoEntre">
			<element class="infoUser">
				<span class="typeInfoUser">Nom : &nbsp</span>
				<span class="mainInfoUser"><?=$info_entre['nom_entre']?></span>
			</element>
			<element class="infoUser">
				<span class="typeInfoUser">Mail : &nbsp</span>
				<span class="mainInfoUser"><?=$info_entre['mail_entre']?></span>
			</element>		
			<element class="infoUser">
				<span class="typeInfoUser">Adresse : &nbsp</span>
				<span class="mainInfoUser" id="infoAddrs"><?=$info_entre['addrs_entre']?></span>
			</element>	
			<element class="infoUser">
				<span class="typeInfoUser">Date de création : &nbsp</span>
				<span class="mainInfoUser"><?=$date?></span>
			</element>
			<element class="infoUser" id="iconeMiddle" >
				<a class="linkLeft" href="#setting">
					<i class="las la-cog"></i>
				</a>
				<a class="linkLeft" href="#edit">
					<i class="las la-puzzle-piece"></i>
				</a>	
				<a class="linkLeft" href="add_artcl.php">
					<i class="las la-shopping-bag"></i>
				</a>
				<a class="linkLeft" href="config/deconnexion.php">
					<i class="las la-arrow-circle-left"></i>
				</a>
			</element>	
		</div>
		<a class="btn_true" href="#big_lower" onclick="showAddPanel()">Ajouter un article</a>
		<div class="stats_entre">
			<span class="sub_txt_stats">Statistiques :</span>
			<span class="sub_stats">Nombre de vues total : <?=$view_entre?> </span>
			<span class="sub_stats">Ajoutés aux favoris : <?=$fav_entre?> </span>
		</div>
	</section>
	<section class="upper_right">
		<div class="entete_right">
			<span class="navig_tittle" onclick="onglet_upper(0);">Questions</span>
			<span class="navig_tittle" onclick="onglet_upper(1);">Avis</span>
		</div>
		<section id="inter_upper">
			<?php 
			    $queryQst = $db->prepare("SELECT * FROM discussion WHERE id_ans IS NULL AND id_item IN (SELECT id_item FROM produits WHERE id_entre = ?) ORDER BY date DESC");
			    $queryQst->execute(array($id_entre));
			    if ($queryQst->rowCount() > 0) {
			      while ($question = $queryQst->fetch()) {
			        include "config/question.php";
			      }
			    }
			    else{ ?>
			    <span>Aucunes questions</span>
			  <?php } 
			?>
		</section>
		<section class="anwser_upper_part" id="ans_upper">
			<input type="hidden" id="getIdRep">
			<span class="errorLabel" id="errUpper"></span>
			<span id="contentQst_upper"></span>
			<textarea class="largeZone" id="questionUpper" rows="7" required></textarea>
			<div class="postFrame">
				<button class="btn_false" onclick="getId(null,null,true)">Annuler</button>
				<button class="postBtn" onclick="sendRepUpper();">Répondre</button>
			</div>
		</section>
	</section>
</section>

<section class="big_lower" id="big_lower" >
	<aside class="lower_left" id="bench_item">
	<input type="hidden" id="customId" value="<?php if (!empty($customId)) {echo $customId;} ?>">
	<div class="benchSubImg">
		<element id="mainImg" onclick="addImage(this,'V','a')">
			<span>Ajouter une image</span>
			<input class="input_img" type="file" id="verticalMainImg">
		</element>
		<div class="subImgRow">
			<element class="verticalSubImgContain">
				<div class="verticalSubImg" onclick="addImage(this,'V','b')">
					<i class="las la-plus"></i>
					<input class="input_img" type="file">
				</div>
				<div class="verticalSubImg" onclick="addImage(this,'V','c')">
					<i class="las la-plus"></i>
					<input class="input_img" type="file">
				</div>
			</element>
			<div id="horiSubImg" onclick="addImage(this,'H','a')">
				<i class="las la-plus"></i>
				<input class="input_img" type="file">
			</div>		
		</div>
	</div>
	<div class="containAllAddEntr">
		<section class="info_add">
			<div>
			    <table>
			      <tr class="td_table_parent" >
			        <td class="td_table">
			          <label class="label_titre_add" for="name_artcl">Nom : </label>
			      	</td>
			        <td class="td_table" >
			          <input class="zone_txt_add" type="text" id="name_artcl" >
			        </td>
			      </tr>
			      <tr class="td_table_parent">
			        <td class="td_table" >
			          <label class="label_titre_add" for="price_artcl">Prix actuel : </label>
			        </td>
			        <td class="td_table" >
			          <input class="zone_price_add" type="number" id="price_artcl" >
			        </td>
			      </tr>
			      <tr class="td_table_parent">
			        <td class="td_table" >
			          <label class="label_titre_add" for="addr_artcl">Adresse : </label>
			        </td>
			        <td class="td_table" >
			          <input class="zone_txt_add" type="text" id="addr_artcl"  placeholder="numéro et rue">
			          <div>
			          	<section class="suggestContain" >
					      <button type="button" class="suggest"></button>
					      <button type="button" class="suggest"></button>
					      <button type="button" class="suggest"></button>
					      <button type="button" class="suggest"></button>
					      <button type="button" class="suggest"></button>
					    </section> 
			          </div>
			        </td>
			      </tr>
			      <tr class="td_table_parent">
			        <td class="td_table" >
			          <label class="label_titre_add" for="hrefMap"></label>
			        </td>
			        <td class="td_table" >
			          <a id="localAddr" class="btn_true">Mon adresse</a>
			          <a id="hrefMap" class="btn_true" href="#carteSelectAdd">Choisir sur la carte</a>
			        </td>
			      </tr>
			      <tr class="td_table_parent">
			        <td class="td_table" >
			          <label class="label_titre_add" for="type_add_artcl">Type : </label>
			        </td>
			        <td class="td_table" >
			          	<select class="zone_txt_add" id="type_add_artcl">
				            <option>produit</option>
				            <option>service</option>   
				        </select>
			        </td>
			      </tr>
			      <tr class="td_table_parent" >
			        <td class="td_table" >
			          <label class="label_titre_add" for="dscrp_artcl">Mots clés : </label>
			        </td>
			        <td>
			          <textarea rows="5" id="keyWords"  placeholder="mot1/mot2/mot3/..."></textarea>
			        </td>
			      </tr>
			      <tr class="td_table_parent" >
			        <td class="td_table" >
			          <label class="label_titre_add" for="dscrp_artcl">Description : </label>
			        </td>
			        <td>
			          <textarea rows="10" id="dscrp_artcl" ></textarea>
			        </td>
			      </tr>
			    </table>
		    </div>
	  </section>
	<div>
		<input type="submit" id="subData" onclick="loadArtcl();" value="Ajouter">
		<label id="error"></label>
	</div>
	</div>

	</aside>
	<aside class="lower_right">
		<div class="entete_right">
			<div class="searchEntre">
				<button type="submit" class="search_icone">
					<i class="las la-search"></i>
				</button>
				<input class="zone_search" type="text" id="liveRecherche" placeholder="Rechercher">
			</div>
			<a class="btn_true" href="#big_lower" onclick="showAddPanel()">Ajouter un article</a>
		</div>
		<section id="inter_lower">
		<?php 
		$prod = $db->prepare("SELECT * FROM produits WHERE id_entre = ? ORDER BY date DESC");
		$prod->execute(array($id_entre));
		if ($prod->rowCount() > 0) {
		while ($articles = $prod->fetch()) {
		  include "config/articles_entreprise.php";   
		}
		}else{
		?><span>Aucun résultats</span><?php 
		}
		?>
		</section>
	</aside>
</section>

<?php
}}}
?>
</div>
<?php include "footer.php" ;?>
</div>
</body>
<script src="javascript/entreprise.js"></script>
</html>