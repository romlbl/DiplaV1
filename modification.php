<?php  
session_start();
include "config/database.php";
global $db;
require('vendor/autoload.php');
use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\Indexer\TNTGeoIndexer;
$config = [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'dipla',
    'username'  => 'root',
    'password'  => '',
    'storage'   => '.',
    'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
];

// qst et avs upper
if(isset($_POST['queryOnglet']) and isset($_SESSION['id_entreprise'])){
	$queryUpper = htmlspecialchars($_POST['queryOnglet']);
	$id_entre = htmlspecialchars($_SESSION['id_entreprise']);
	if ($queryUpper == "qst") {
		$queryQst = $db->prepare("SELECT * FROM discussion WHERE id_ans IS NULL AND id_item IN (SELECT id_item FROM produits WHERE id_entre = ?) ORDER BY date DESC");
	    $queryQst->execute(array($id_entre));
	    if ($queryQst->rowCount() > 0) {
	      while ($question = $queryQst->fetch()) {
	        include "config/question.php";
	      }
	    }
	    else{ ?>
	    <span>Aucune questions</span>
	  <?php } 
	}
	elseif ($queryUpper == "avs") {
	    $queryAvs = $db->prepare("SELECT * FROM avis WHERE id_item IN (SELECT id_item FROM produits WHERE id_entre = ?) ORDER BY date DESC");
	    $queryAvs->execute(array($id_entre));
	    if ($queryAvs->rowCount() > 0) {
	      while ($avis = $queryAvs->fetch()) {
	        include "config/avis.php";
	      }
	    }
	    else{ ?>
	    <span>Aucune questions</span>
	  <?php } 
	}
}

//réponse question upper
if (isset($_POST['id_rep_U']) and isset($_SESSION['id_entreprise']) and isset($_POST['content_U'])) {
	$rep_U = htmlspecialchars($_POST['id_rep_U']);
	$id_entre = htmlspecialchars($_SESSION['id_entreprise']);
	$content_U = htmlspecialchars($_POST['content_U']);
	$getId = $db->prepare("SELECT id_item FROM discussion WHERE id_qst = ?");
	$getId->execute(array($rep_U));
	$rslt = $getId->fetch();
	$rslt = $rslt[0];
	$postQuery = $db->prepare("INSERT INTO discussion (id_item,id_ans,id_entre,contenu,certif) VALUES (?,?,?,?,?)");
	$postQuery->execute(array($rslt,$rep_U,$id_entre,$content_U,true));

	$queryQst = $db->prepare("SELECT * FROM discussion WHERE id_ans IS NULL AND id_item IN (SELECT id_item FROM produits WHERE id_entre = ?) ORDER BY date DESC");
    $queryQst->execute(array($id_entre));
    if ($queryQst->rowCount() > 0) {
      while ($question = $queryQst->fetch()) {
        include "config/question.php";
      }
    }
    else{ ?>
    <span>Aucune questions</span>
  <?php }

}

// recherche live
if (isset($_POST['input']) and isset($_SESSION['id_entreprise'])) {
	$input = htmlspecialchars($_POST['input']);
	$id_entre = htmlspecialchars($_SESSION['id_entreprise']);
	if ($input != "" and $input != "TqMmeVwU") {
		$tnt = new TNTSearch;
		$tnt->loadConfig($config);
		$tnt->selectIndex("config/articles.index");
		$tnt->fuzziness = true;
		// on décompose la rechecherche
		$explode_search = explode(' ', $input);
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
		  $prod = $db->prepare("SELECT * FROM produits WHERE id_item IN (?) AND id_entre = ? ORDER BY FIELD(id_item, $ids)");
		  $prod->execute(array($ids,$id_entre));
		  if ($prod->rowCount() > 0) {
		    while ($articles = $prod->fetch()) {
		      include "config/articles_entreprise.php";   
		    }
		  }else{
		    ?><span>Aucun résultats</span><?php 
		  }
		}
	}else if( $input == "TqMmeVwU"){
		  $prod = $db->prepare("SELECT * FROM produits WHERE id_entre = ? ORDER BY date DESC");
		  $prod->execute(array($id_entre));
		  if ($prod->rowCount() > 0) {
		    while ($articles = $prod->fetch()) {
		      include "config/articles_entreprise.php";   
		    }
		  }else{
		    ?><span>Aucun résultats</span><?php 
		  }
	}else{
		$prod = $db->prepare("SELECT * FROM produits WHERE id_entre = ? ORDER BY date DESC");
		  $prod->execute(array($id_entre));
		  if ($prod->rowCount() > 0) {
		    while ($articles = $prod->fetch()) {
		      include "config/articles_entreprise.php";   
		    }
		  }else{
		    ?><span>Aucun résultats</span><?php 
		  }
	}
}

// ajout ou modification de l'artcl

function addImages($file,$path,$name){
	if (getimagesize($file['tmp_name']) !== false) {
	    $completPath = $path . $name.".png";
	    if (file_exists($completPath)) {
	        unlink($completPath);
	    }
	    if (move_uploaded_file($file["tmp_name"], $completPath)) {
	    	return true;
	    } else {
	        return false;
	    }
	}else{
		return false;
	}
}


if (isset($_POST['action']) and isset($_POST['customId']) and isset($_SESSION['id_entreprise'])) {
	$actionType = htmlspecialchars($_POST['action']);
	$echec = false;
	$lat_add = htmlspecialchars($_SESSION['latitudeFrom']);
	$lng_add = htmlspecialchars($_SESSION['longitudeFrom']);
	if ($actionType == "addSet") {
	//on met a jour TNTsearch
	$tnt = new TNTSearch;
	$tnt->loadConfig($config);
	$tnt->selectIndex("config/articles.index");
	$index = $tnt->getIndex();
	// on récupère les info de l'article
	$customId = htmlspecialchars($_POST['customId']);
	$name_artcl = htmlspecialchars($_POST['titre']);
	$price_artcl = htmlspecialchars($_POST['prix']);
	$type_add_artcl = htmlspecialchars($_POST['typeArtcl']);
	$addrs_artcl = htmlspecialchars($_POST['adress']);
	$lat_add = htmlspecialchars($_POST['putLat']);
	$lng_add = htmlspecialchars($_POST['putLng']);
	$dscrp_artcl = htmlspecialchars($_POST['dscrp']);
	$dscrp_artcl = nl2br($dscrp_artcl);
	$keyWords = htmlspecialchars($_POST['keysW']);
	$id_entre_add = htmlspecialchars(($_SESSION['id_entreprise']));
	// on récupère l'id de l'artcle en recupérant le nombre de row de la table
	$query_row = $db->prepare('SELECT id_item FROM produits ');
	$query_row->execute();
	if ($type_add_artcl == 'produit') {
		$type_add_artcl = 'artcl';
	}
	else if ($type_add_artcl == 'service') {
			$type_add_artcl = 'srvc';
	}

	if ($customId == "new") {
		$finalId = $query_row->rowCount();
		$id_new_prdt = $finalId + 1;
		$set_artcl = $db->prepare("INSERT INTO produits (id_entre,titre,prix,contenu,type,addrs,latitude,longitude,keyWords) VALUES (?,?,?,?,?,?,?,?,?)  ");
		$tabInfo = array($id_entre_add,$name_artcl,$price_artcl,$dscrp_artcl,$type_add_artcl,$addrs_artcl,$lat_add,$lng_add,$keyWords);
	}
	else{
		$id_new_prdt = $customId;
		$set_artcl = $db->prepare("UPDATE produits SET titre = ?,prix = ?,contenu = ?,type = ?,addrs = ?,latitude = ?,longitude = ?,keyWords = ?,date = NOW() WHERE id_item = ? LIMIT 1");
		$tabInfo = array($name_artcl,$price_artcl,$dscrp_artcl,$type_add_artcl,$addrs_artcl,$lat_add,$lng_add,$keyWords,$customId);
	$index->update($customId,['id' => $customId, 'titre' => $name_artcl, 'contenu' => $dscrp_artcl,'keyWords' => $keyWords]);	
	}

	$verifAdd = $set_artcl-> execute($tabInfo);
	if ($customId == "new" and $verifAdd == true) {
		$id_new_prdt = $db->lastInsertId();
		$index->insert(['id' => $id_new_prdt, 'titre' => $name_artcl, 'contenu' => $dscrp_artcl,'keyWords' => $keyWords]); 
	}else if($customId == "new"){
		$echec = true;
	}

	// on ajoute les images
	if ($echec == false && isset($_FILES['Va']) && isset($_FILES['Ha'])) {
		if(addImages($_FILES['Va'],"img produits/img portrait/",$id_new_prdt . "a") and
		   addImages($_FILES['Ha'],"img produits/img paysage/",$id_new_prdt . "a")){
			if (isset($_FILES['Vb'])) {
				if(addImages($_FILES['Vb'],"img produits/img portrait/",$id_new_prdt . "b")){
				}else{
					$echec = true;
				}
			}else if(!isset($_FILES['Vb']) && file_exists("img produits/img portrait/".$id_new_prdt . "b.png")){
				unlink("img produits/img portrait/".$id_new_prdt . "b.png");
			}
			if (isset($_FILES['Vc'])) {
				if(addImages($_FILES['Vc'],"img produits/img portrait/",$id_new_prdt . "c")){
				}else{
					$echec = true;
				}
			}else if(!isset($_FILES['Vc']) && file_exists("img produits/img portrait/".$id_new_prdt . "c.png")){
				unlink("img produits/img portrait/".$id_new_prdt . "c.png");
			}
		}else{
			$echec = true;
		}
	}else{
		$echec = true;
	}
	// obliger de recompiler toute la base de donné pour mettre a jour la geoSearch
	$tnt_geo = new TNTGeoIndexer;
	$tnt_geo->loadConfig($config);

	$tnt_geo->setPrimaryKey('id_item');
	$tnt_geo->createIndex('config/geo_search.index');
	$tnt_geo->query('SELECT id_item, latitude, longitude FROM produits;');
	$tnt_geo->run();

	}

	if ($echec == false || $actionType == "showPanel") { ?>
	<input type="hidden" id="customId" value="new">
		<input type="hidden" id="putLatInput" value="<?=$lat_add?>">
		<input type="hidden" id="putLngInput" value="<?=$lng_add?>">
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
	<div class="contain_all_add">
		<section class="info_add" >
			<div>
			    <table>
			      <tr class="td_table_parent">
			        <td class="td_table" >
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
			      <tr class="td_table_parent">
			        <td class="td_table" >
			          <label class="label_titre_add" for="dscrp_artcl">Mots clés : </label>
			        </td>
			        <td>
			          <textarea rows="5" id="keyWords"  placeholder="mot1/mot2/mot3/..."></textarea>
			        </td>
			      </tr>
			      <tr class="td_table_parent">
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


	<?php }
}

if (isset($_POST['action']) and $_POST['action'] == "selectArtlc" and isset($_POST['id_item'])) { 
	$gettedId = htmlspecialchars($_POST['id_item']);
	$tryAtcl = $db->prepare('SELECT * FROM produits WHERE id_item = ? ');
	$tryAtcl->execute(array($gettedId));
	$article = $tryAtcl->fetch();
	if($article['type'] == 'artcl'){
		$type_add_artcl = "produit";
	}else if($article['type'] == 'srvc'){
		$type_add_artcl = "service";
	}
	$dscrpClean = str_replace("<br />", "", $article['contenu']);

	?>

	<input type="hidden" id="customId" value="<?=$gettedId?>">
	<input type="hidden" id="putLatInput" value="<?=$article['latitude']?>">
	<input type="hidden" id="putLngInput" value="<?=$article['longitude']?>">
<div class="benchSubImg">
	<element id="mainImg" onclick="addImage(this,'V','a')">
		<i class="las la-trash-alt" onclick="delImg('V','a',this.parentNode,true);"></i>
		<img id="ImgVa" src="<?='img produits/img portrait/'. $gettedId . 'a.png'?>" alt="Image">
	</element>
	<div class="subImgRow">
		<element class="verticalSubImgContain">
			<div class="verticalSubImg" onclick="addImage(this,'V','b')">
				<?php if (file_exists('img produits/img portrait/'. $gettedId . 'b.png')) { ?>
					<i class="las la-trash-alt" onclick="delImg('V','b',this.parentNode,true);"></i>
					<img id="ImgVb" src="<?='img produits/img portrait/'. $gettedId . 'b.png'?>" alt="Image">
				<?php }else{ ?>
					<i class="las la-plus"></i>
					<input class="input_img" type="file">
				<?php } ?>
			</div>
			<div class="verticalSubImg" onclick="addImage(this,'V','c')">
				<?php if (file_exists('img produits/img portrait/'. $gettedId . 'c.png')) { ?>
					<i class="las la-trash-alt" onclick="delImg('V','c',this.parentNode,true);"></i>
					<img id="ImgVc" src="<?='img produits/img portrait/'. $gettedId . 'c.png'?>" alt="Image">
				<?php }else{ ?>
					<i class="las la-plus"></i>
					<input class="input_img" type="file">
				<?php } ?>
			</div>
		</element>
		<div id="horiSubImg" onclick="addImage(this,'H','a')">
			<i class="las la-trash-alt" onclick="delImg('H','a',this.parentNode,true);"></i>
			<img id="ImgHa" src="<?='img produits/img paysage/'. $gettedId . 'a.png'?>" alt="Image">
		</div>		
	</div>
</div>
<div class="contain_all_add">
	<section class="info_add" >
		<div>
		    <table>
		      <tr class="td_table_parent">
		        <td class="td_table" >
		          <label class="label_titre_add" for="name_artcl">Nom : </label>
		        </td>
		        <td class="td_table" >
		          <input class="zone_txt_add" type="text" id="name_artcl" value="<?=$article['titre']?>" >
		        </td>
		      </tr>
		      <tr class="td_table_parent">
		        <td class="td_table" >
		          <label class="label_titre_add" for="price_artcl">Prix actuel : </label>
		        </td>
		        <td class="td_table" >
		          <input class="zone_price_add" type="number" id="price_artcl" value="<?=$article['prix']?>" >
		        </td>
		      </tr>
		      <tr class="td_table_parent">
		        <td class="td_table" >
		          <label class="label_titre_add" for="addr_artcl">Adresse : </label>
		        </td>
		        <td class="td_table" >
		          <input class="zone_txt_add" type="text" id="addr_artcl" value="<?=$article['addrs']?>">
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
		          	<select class="zone_txt_add" id="type_add_artcl"  value="<?=$type_add_artcl?>">
			            <option>produit</option>
			            <option>service</option>   
			        </select>
		        </td>
		      </tr>
		      <tr class="td_table_parent">
		        <td class="td_table" >
		          <label class="label_titre_add" for="dscrp_artcl">Mots clés : </label>
		        </td>
		        <td>
		          <textarea rows="5" id="keyWords" ><?=$article['keyWords']?></textarea>
		        </td>
		      </tr>
		      <tr class="td_table_parent">
		        <td class="td_table" >
		          <label class="label_titre_add" for="dscrp_artcl">Description : </label>
		        </td>
		        <td>
		          <textarea rows="10" id="dscrp_artcl"><?=$dscrpClean?></textarea>
		        </td>
		      </tr>
		    </table>
	    </div>
  </section>
<div>
	<input type="submit" id="delItem" value="Supprimer">
	<input type="submit" id="subData" onclick="loadArtcl();" value="Modifier">
	<label id="error"></label>
</div>
<div id="confSuppArtcl">
	<span class="txt_del_account">Confirmez-vous la suppression de <?=$article['titre']?> ?</span>
	<section class="containe_valider">
		<a id="canSuppArtcl" class="anul_del_account">Annuler</a>
	    <input class="valide_set_edit" type="submit" value="Confirmer" onclick="deleteArtcl(<?=$gettedId?>)">
	</section>
</div>
</div>

<?php }

// suppression d'un article
if (isset($_POST['action']) and $_POST['action'] == "deleteArtlc" and isset($_POST['id_item'])) {
	$idTarget = htmlspecialchars($_POST['id_item']);
	$delHisto = $db->prepare('DELETE FROM historique WHERE id_item = ? ');
	$delQst = $db->prepare('DELETE FROM discussion WHERE id_item = ? ');
	$delAvs = $db->prepare('DELETE FROM avis WHERE id_item = ? ');
	$delFav = $db->prepare('DELETE FROM favoris WHERE id_item = ? ');
	$delArtcl = $db->prepare('DELETE FROM produits WHERE id_item = ? ');
	$delHisto->execute(array($idTarget));
	$delQst->execute(array($idTarget));
	$delAvs->execute(array($idTarget));
	$delFav->execute(array($idTarget));
	$delArtcl->execute(array($idTarget));
	// images
	unlink("img produits/img portrait/".$idTarget . "a.png");
	unlink("img produits/img paysage/".$idTarget . "a.png");
	if(file_exists("img produits/img portrait/".$idTarget . "b.png")){
		unlink("img produits/img portrait/".$idTarget . "b.png");
	}
	if(file_exists("img produits/img portrait/".$idTarget . "c.png")){
		unlink("img produits/img portrait/".$idTarget . "c.png");
	}
	//TNT search
	$tnt = new TNTSearch;
	$tnt->loadConfig($config);
	$tnt->selectIndex("config/articles.index");
	$index = $tnt->getIndex();
	$index->delete($idTarget);
	// obliger de recompiler toute la base de donné pour mettre a jour la geoSearch
	$tnt_geo = new TNTGeoIndexer;
	$tnt_geo->loadConfig($config);

	$tnt_geo->setPrimaryKey('id_item');
	$tnt_geo->createIndex('config/geo_search.index');
	$tnt_geo->query('SELECT id_item, latitude, longitude FROM produits;');
	$tnt_geo->run();
}

// modification entreprise
if (isset($_POST['action']) and $_POST['action'] == "editEntre" and isset($_SESSION['id_entreprise']) ) {
	$id_entre = htmlspecialchars($_SESSION['id_entreprise']);
	$name_entre = htmlspecialchars($_POST['name']);
	$mail_entre = htmlspecialchars($_POST['mail']);
	$addr_entre = htmlspecialchars($_POST['newAdrr']);
	$lat_entre = htmlspecialchars($_POST['latEdit']);
	$lng_entre = htmlspecialchars($_POST['lngEdit']);
	$checkPrdt = htmlspecialchars($_POST['onPrdt']);
	$edit_entre = $db->prepare("UPDATE entreprise SET nom_entre = ?,mail_entre = ?,addrs_entre = ?,lat_entre = ?,lng_entre = ? WHERE id_entre = ? LIMIT 1");
	$resultQuery = $edit_entre->execute(array($name_entre,$mail_entre,$addr_entre,$lat_entre,$lng_entre,$id_entre));

	if ($checkPrdt) {
		$addrPrdt = $db->prepare("UPDATE produits SET addrs = ?, latitude = ?,longitude = ? WHERE id_entre = ?");
		$resultQueryPrdt = $addrPrdt->execute(array($addr_entre,$lat_entre,$lng_entre,$id_entre));
	}

	if ($resultQuery) {
		echo "sucess";
	}else{
		echo "une erreur est survenue";
	}
}

// supression entreprise
if (isset($_POST['action']) and $_POST['action'] == "supprEntre" and isset($_SESSION['id_entreprise']) ) {
	$idtarget = htmlspecialchars($_SESSION['id_entreprise']);
	//TNT search
	$tnt = new TNTSearch;
	$tnt->loadConfig($config);
	$tnt->selectIndex("config/articles.index");
	$index = $tnt->getIndex();
	// suppression des articles
	$getPrdtIds = $db->prepare("SELECT id_item FROM produits WHERE id_entre = ?");
	$getPrdtIds->execute(array($idtarget));
	if ($getPrdtIds->rowCount() > 0) {
	      while ($idPrdt = $getPrdtIds->fetch()) {
			$idPrdtRow = $idPrdt['id_item'];
			$delHisto = $db->prepare('DELETE FROM historique WHERE id_item = ? ');
			$delQst = $db->prepare('DELETE FROM discussion WHERE id_item = ? ');
			$delAvs = $db->prepare('DELETE FROM avis WHERE id_item = ? ');
			$delFav = $db->prepare('DELETE FROM favoris WHERE id_item = ? ');
			$delArtcl = $db->prepare('DELETE FROM produits WHERE id_item = ? ');
			$delHisto->execute(array($idPrdtRow));
			$delQst->execute(array($idPrdtRow));
			$delAvs->execute(array($idPrdtRow));
			$delFav->execute(array($idPrdtRow));
			$delArtcl->execute(array($idPrdtRow));
			// images
			unlink("img produits/img portrait/".$idPrdtRow . "a.png");
			unlink("img produits/img paysage/".$idPrdtRow . "a.png");
			if(file_exists("img produits/img portrait/".$idPrdtRow . "b.png")){
				unlink("img produits/img portrait/".$idPrdtRow . "b.png");
			}
			if(file_exists("img produits/img portrait/".$idPrdtRow . "c.png")){
				unlink("img produits/img portrait/".$idPrdtRow . "c.png");
			}
			$index->delete($idPrdtRow);
	      
		}
		// obliger de recompiler toute la base de donné pour mettre a jour la geoSearch
		$tnt_geo = new TNTGeoIndexer;
		$tnt_geo->loadConfig($config);

		$tnt_geo->setPrimaryKey('id_item');
		$tnt_geo->createIndex('config/geo_search.index');
		$tnt_geo->query('SELECT id_item, latitude, longitude FROM produits;');
		$tnt_geo->run();
	}
	// suppression de l'entreprise
	$delEntre= $db->prepare('DELETE FROM entreprise WHERE id_entre = ? ');
	$delEntre->execute(array($idtarget));
}

?>