 <?php 
include "config/database.php";
global $db;

 
?>
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

<?php include "entête.php"; 
$erreur = "";
// on gere la localisation
if (!empty($_SESSION['latitudeFrom'])) {
	$getLatUser = $_SESSION['latitudeFrom'];
	$getLngUser = $_SESSION['longitudeFrom'];
}else{
	$getLatUser = 200;
	$getLngUser = 200;
}

// on vérifie qu'on est connecté
if(!empty($_SESSION['id_user'])){ 
	$id = htmlspecialchars($_SESSION['id_user']);

	// pour supprimer le compte
	if(isset($_POST['del_account'])){
		$del_avis = $db->prepare('DELETE FROM avis WHERE id_user = ?');
		$del_avis->execute(array($id));
		$discu_q = $db->prepare("SELECT * FROM discussion WHERE id_user = ?");
		$discu_q->execute(array($id));
		if($discu_q->rowCount() > 0){
			while ($info_discu = $discu_q->fetch()) {
				$del_discu = $db->prepare('DELETE FROM discussion WHERE id_ans = ?');
				$del_discu->execute(array($info_discu['id_qst']));

			}
		}
		$del_discu_main = $db->prepare('DELETE FROM discussion WHERE id_user = ?');
		$del_discu_main->execute(array($id));
		$del_favo = $db->prepare('DELETE FROM favoris WHERE id_user = ?');
		$del_favo->execute(array($id));
		$del_historique = $db->prepare('DELETE FROM historique WHERE id_user = ?');
		$del_historique->execute(array($id));
		$del_cmpt = $db->prepare('DELETE FROM utilisateurs WHERE id_user = ?');
		$del_cmpt->execute(array($id));
		header("Location:config/deconnexion.php");
	}


// on récupère les info de l'utilisateur
$query = $db->prepare("SELECT * FROM utilisateurs WHERE id_user = ?");
$query->execute(array($id));
if($query->rowCount() > 0){
	while ($info_user = $query->fetch()) {
		$date = date("d/m/y",strtotime($info_user['date']));
		$modif = false;
		if(isset($_POST['valide_edit'])){
			if(isset($_POST['edit_name']) and $_POST['edit_name'] != $info_user['nom'] and !empty($_POST['edit_name']) ){
				$new_name = htmlspecialchars($_POST['edit_name']);
				$query_name = $db->prepare('UPDATE utilisateurs SET nom = ? WHERE id_user = ?');
				$query_name->execute(array($new_name,$id));
				$modif = true;
			}
			// le mail
			if(isset($_POST['edit_mail']) and !empty($_POST['edit_mail']) and $_POST['edit_mail'] != $info_user['mail'] ){
				$new_mail = htmlspecialchars($_POST['edit_mail']);
				$query_mail = $db->prepare('UPDATE utilisateurs SET mail = ? WHERE id_user = ?');
				$query_mail->execute(array($new_mail,$id));
				$modif = true;
			}
			// si case pas adresse coché
			if (isset($_POST['checkNone'])) {
				$query_addr = $db->prepare('UPDATE utilisateurs SET addrs = ?, latitude = ?, longitude = ? WHERE id_user = ?');
				$query_addr->execute(array(NULL,NULL,NULL,$id));
				$modif = true;
			} 
			// l'adresse
			else if(isset($_POST['edit_addr'])and !empty($_POST['edit_addr']) and $_POST['edit_addr'] != $info_user['addrs'] ){
				$new_addr = htmlspecialchars($_POST['edit_addr']);
				if (isset($_POST['putLat']) && !empty($_POST['putLat'])) {
					$lat = htmlspecialchars($_POST['putLat']);
					$lng = htmlspecialchars($_POST['putLng']);
				}else{
					$lat = '';
					$lng = '';
				}
				// on update la base de données
				$query_addr = $db->prepare('UPDATE utilisateurs SET addrs = ?, latitude = ?, longitude = ? WHERE id_user = ?');
				$query_addr->execute(array($new_addr,$lat,$lng,$id));
				$_SESSION['latitudeFrom'] = $lat;
				$_SESSION['longitudeFrom'] = $lng;
				$modif = true;
				}else{
					$erreur = "Aucune adresse trouvée";
				}
			}
			// on refresh avec javascript si on a fait des modif
			if ($modif == true){
				header("Location: {$_SERVER['PHP_SELF']}");
			}
?>
<!-- geocodage -->
<input type="hidden" id="getLatCarte" value="<?=$getLatUser ?>">
<input type="hidden" id="getLngCarte" value="<?=$getLngUser ?>">

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
          <a href="nvx_mdp/token.php" class="list_compte">Modifier mon mot de passe</a>
        </li>
        <li class="list_section">
          <a href="#confirmation" style="color: red;" class="list_compte">Supprimer mon compte</a>
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
            <a href="politique.php" class="list_compte" style="color: #e28989">Politique de confidentialité</a>
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
	<label class="errorlabel"><?=$erreur ?></label>
	<a href="politique.php" class="list_compte" style="color: #e28989">Politique de confidentialité</a> 
	<form class="main_set_edit" method="POST">
		<element class="element_edit">
			<span class="edit_type">Nom :&nbsp </span>
			<input class="zne_edit" type="text" name="edit_name" placeholder="<?=$info_user['nom']?>">
		</element>
		<element class="element_edit">
			<span class="edit_type">Mail :&nbsp </span>
			<input class="zne_edit" type="text" name="edit_mail" placeholder="<?=$info_user['mail']?>">
		</element>
		<element class="element_edit">
			<span class="edit_type">Adresse :&nbsp </span>
			<input class="zne_edit" type="text" id="edit_addr" name="edit_addr" placeholder="<?=$info_user['addrs']?>">
			<div>
				<section class="suggestContain" >
				  <button type="button" class="suggest"></button>
				  <button type="button" class="suggest"></button>
				  <button type="button" class="suggest"></button>
				  <button type="button" class="suggest"></button>
				  <button type="button" class="suggest"></button>
				</section> 
			</div>
		</element>
		<input type="hidden" id="putLat" name="putLat">
		<input type="hidden" id="putLng" name="putLng">
		<element  class="element_edit" id="none_adresse">
			<span class="edit_type">Aucune Adresse</span>
			<input type="checkbox" name="checkNone" id="checkNone">
		</element>
		<element class="containe_valider">
	        <input class="valide_set_edit" type="submit" name="valide_edit" value="Valider">
	    </element>
	</form>
</div>

<div id="confirmation">
	<span class="txt_del_account">Confirmez-vous la suppression de toutes vos données sur Dipla ?</span>
	<section class="containe_valider">
		<a href="#" class="anul_del_account">Annuler</a>
		<form method="POST">
	    	<input class="valide_set_edit" type="submit" name="del_account" value="Confirmer">
		</form>
	</section>
</div>

<div class="contenu">
<div class="contenant">

<div class="allPart">
	<section class="leftPart">
		<div class="containLeft">
			<a class="linkLeft" href="#setting">
				<span class="txtLeft">Paramètre</span>
				<i class="las la-cog"></i>
			</a>
			<a class="linkLeft" href="#edit">
				<span class="txtLeft">Modifier</span>
				<i class="las la-puzzle-piece"></i>
			</a>	
			<a class="linkLeft" onclick="onglet(0);">
				<span class="txtLeft">Favoris</span>
				<i class="las la-certificate"></i>
			</a>		
		</div>
		<div class="containLeft">
			<a class="linkLeft" href="config/deconnexion.php">
				<span class="txtLeft">Déconnexion</span>
				<i class="las la-arrow-circle-left"></i>
			</a>
		</div>	
	</section>

	<section class="middleRightPart">
		<div class="middlePart">
			<div class="containerInfo">
				<element class="infoUser">
					<span class="typeInfoUser">Nom : &nbsp</span>
					<span class="mainInfoUser"><?=$info_user['nom']?></span>
				</element>
				<element class="infoUser">
					<span class="typeInfoUser">Mail : &nbsp</span>
					<span class="mainInfoUser"><?=$info_user['mail']?></span>
				</element>		
				<element class="infoUser">
					<span class="typeInfoUser">Adresse : &nbsp</span>
					<span class="mainInfoUser" id="infoAddrs"><?=$info_user['addrs']?></span>
				</element>	
				<element class="infoUser">
					<span class="typeInfoUser">Date de création : &nbsp</span>
					<span class="mainInfoUser"><?=$date?></span>
				</element>
				<element class="infoUser" id="iconeMiddle">
					<a class="linkLeft" href="#setting">
						<i class="las la-cog"></i>
					</a>
					<a class="linkLeft" href="#edit">
						<i class="las la-puzzle-piece"></i>
					</a>	
					<a class="linkLeft" onclick="onglet(0);">
						<i class="las la-certificate"></i>
					</a>
					<a class="linkLeft" href="config/deconnexion.php">
						<i class="las la-arrow-circle-left"></i>
					</a>
				</element>	
			</div>

			<div class="all_histo">
				<section class="headerHisto">
					<span class="txtHeaderHisto">Historique</span>
						<input class="txtHeaderHisto" type="submit" id="txtVideHisto" onclick="emptyHisto();" value="Vider l'historique">
				</section>
				<section class="mainHisto" id="containHistorique">
 				<?php 
 				$query = $db->prepare("SELECT * FROM historique WHERE id_user = ? ORDER BY date DESC");
				$query->execute(array($id));
				if($query->rowCount() > 0){
					while ($historique = $query->fetch()) {	

						$query_artcl = $db->prepare("SELECT * FROM produits WHERE id_item = ?");
						$query_artcl->execute(array($historique['id_item']));

						if($query_artcl->rowCount() > 0){
							while ($articles = $query_artcl->fetch()) {
								include "config/articles_phone.php";
							}
						}
					}
				}
				else{ ?>
					<span>Pas d'historique</span>
				<?php	}
				?>				
				</section>
				
			</div>
			
		</div>
		<div class="RightPart">
			<section class="headerRight">
				<span class="navig_tittle" onclick="onglet(0);">Favoris</span>
				<span class="navig_tittle" onclick="onglet(1);">Avis</span>
				<span class="navig_tittle" onclick="onglet(2);">Questions</span>
				<span class="navig_tittle" id="navig_tittle_histo" onclick="onglet(3);">Historique</span>
			</section>
			<div id="supprAvsQst">
				<span>Voulez-vous supprimez<small id="nbSuppr"></small>?</span>
				<section class="confirmSupprAvsQst">
						<div class="btnConteneur" style="background-color: transparent;color:#d3d3d3;" onclick="showSuppr('close');">Annuler</div>
						<div class="btnConteneur" onclick="deleteAvsQst();">Confirmer</div>
				</section>
			</div>

			<div class="conteneurRight active" >
				<section class="conteneurRightSub" id="containFavoris">
				<?php 
				$query_fav = $db->prepare("SELECT * FROM favoris WHERE id_user = ?");
				$query_fav->execute(array($id));
				if($query_fav->rowCount() > 0){
					while ($favories = $query_fav->fetch()) {	

						$query_artcl = $db->prepare("SELECT * FROM produits WHERE id_item = ?");
						$query_artcl->execute(array($favories['id_item']));

						if($query_artcl->rowCount() > 0){
							while ($articles = $query_artcl->fetch()) {
								include "config/articles_phone.php";
							}
						}
					}
				}
				else{ ?>
					<span>Aucun favoris</span>
				<?php }
				?>					
				</section>		
			</div>

			<div class="conteneurRight">
				<section class="headerRightSub">
					<button class="btnConteneur" onclick="showSuppr('open');">Supprimer</button>
					<div>
						<span>Tout : &nbsp</span>
						<input type="checkbox" id="AllcocheAvs">
					</div>
				</section>

				<section class="conteneurRightSub" id="containeAvis">
				<?php 
					    $queryAvis = $db->prepare('SELECT * FROM avis WHERE id_user = ? ORDER BY date DESC');
				        $queryAvis->execute(array($id));
				        if ($queryAvis->rowCount() > 0) {
				          while ($avis = $queryAvis->fetch()) {
				            include "config/avis.php";
				          }
				        }
				        else { ?>
				        <span>Aucun avis pour l'instant</span>
				      <?php } 
				?>					
				</section>		
			</div>

			<div class="conteneurRight">
				<section class="headerRightSub" >
					<button class="btnConteneur" onclick="showSuppr('open');">Supprimer</button>
					<div>
						<span>Tout : &nbsp</span>
						<input type="checkbox" id="AllcocheQst">
					</div>
				</section>

				<section class="conteneurRightSub" id="containeQuestion">
				<?php 
			        // on recupères les info de l'artciles
			        $queryQst = $db->prepare("SELECT * FROM discussion WHERE id_ans IS NULL AND id_user = ? ORDER BY date DESC");
			        $queryQst->execute(array($id));
			        if ($queryQst->rowCount() > 0) {
			          while ($question = $queryQst->fetch()) {
			            include "config/question.php";
			          }
			        }
			        else{ ?>
			        <span>Aucune question pour l'instant</span>
			      <?php } 
				?>					
				</section>		
			</div>

			<div class="conteneurRight">
				<section class="headerRightSub">
						<button class="btnConteneur" onclick="emptyHisto();">Vider l'historique</button>
				</section>
				<section class="conteneurRightSub" id="containHistorique">
				<?php 
				$query = $db->prepare("SELECT * FROM historique WHERE id_user = ? ORDER BY date DESC");
				$query->execute(array($id));
				if($query->rowCount() > 0){
					while ($historique = $query->fetch()) {	

						$query_artcl = $db->prepare("SELECT * FROM produits WHERE id_item = ?");
						$query_artcl->execute(array($historique['id_item']));

						if($query_artcl->rowCount() > 0){
							while ($articles = $query_artcl->fetch()) {
								include "config/articles_phone.php";
							}
						}
					}
				}
				else{ ?>
					<span>Pas d'historique</span>
				<?php	}
				?>					
				</section>		
			</div>
			
		</div>
	</section>
</div>


<?php }}}
?>

</div>
<?php include "footer.php" ;?>
</div>
</body>
<script src="javascript/compte.js"></script>
</html>