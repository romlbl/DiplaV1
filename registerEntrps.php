<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Dipla</title>
 <meta charset="utf8_general_ci"> 
 <meta name="viewport" content="width=device-width, initial-scale=1.0" >
 <link rel="icon" type="shortcut icon" href="imgLocal/shortcut.svg"/>
 <link rel="stylesheet" type="text/css" href="style.css" ver="1.1">
 <link rel="stylesheet" href="line-awesome-1.3.0/1.3.0/css/line-awesome.min.css">
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-core.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-service.js"></script>
<script type="text/javascript" src="https://js.api.here.com/v3/3.1/mapsjs-mapevents.js"></script>
<script type="text/javascript" charset="utf-8" src="https://js.api.here.com/v3/3.1/mapsjs-ui.js" ></script>
<link rel="stylesheet" type="text/css" href="https://js.api.here.com/v3/3.1/mapsjs-ui.css" />
 <script src="javascript/jquery.min.js"></script>
</head>
<body>
<?php	include "config/database.php";
global $db;

if(!empty($_SESSION['latitudeFrom']) && !empty($_SESSION['longitudeFrom'])){
	$getLatCarte = htmlspecialchars($_SESSION['latitudeFrom']);
	$getLngCarte = htmlspecialchars($_SESSION['longitudeFrom']);
}else{
	$getLatCarte = "";
	$getLngCarte = "";
}

//si click sur suivant
if (isset($_POST['name']) && isset($_POST['mail']) && isset($_POST['mdp']) && isset($_POST['adresse']) && isset($_POST['putLat']) && isset($_POST['putLng'])) {
	//on recupère les champs
	$name = htmlspecialchars($_POST['name']);
	$mail = htmlspecialchars($_POST['mail']);
	$mdpSet = htmlspecialchars($_POST['mdp']);
	$adresse = htmlspecialchars($_POST['adresse']);
	$putLat = htmlspecialchars($_POST['putLat']);
	$putLng = htmlspecialchars($_POST['putLng']);
	//On verifie si les champs obligatoire sont completés
	if (!empty($name) && !empty($mail) && !empty($mdpSet) && !empty($putLat) && !empty($putLng)) {
			$mdp = hash('md5', $mdpSet);
			if (strlen($name) < 50) {
			   $verif_mail = $db->prepare("SELECT * FROM entreprise WHERE mail_entre = ?  ");
			   $verif_mail->execute(array($mail));
				if ($verif_mail->rowCount() == 0) {
				   $verif_mdp = $db->prepare("SELECT * FROM entreprise WHERE mdp_entre = ?  ");
				   $verif_mdp->execute(array($mdp));
				   if ($verif_mdp->rowCount() == 0) {
				   		if(!empty($putLat)&&!empty($putLng)){
						   	$crée_cmt = $db->prepare("INSERT INTO entreprise (nom_entre,mail_entre,addrs_entre,lat_entre,lng_entre,mdp_entre) VALUES (?,?,?,?,?,?) ");
		        			$success = $crée_cmt -> execute(array($name,$mail,$adresse,$putLat,$putLng,$mdp));
		        			if ($success) {
		        				$id_new_entre = $db->lastInsertId();
		        				$_SESSION['id_entreprise'] = $id_new_entre;
		        				header('Location:entreprise.php');
		        				exit();
		        			}else{
		        				$erreur = "Une erreur est survenue";
		        			}
				   		}else{
				   			$erreur = "Problème de géolocalisation";
				   		}
				   }//sinon on affiche erreur
					else{
						$erreur = 'Votre mot de passe est déja utilisée';
					}
				}
				else{
					$erreur = 'Votre adresse mail est déja utilisée';
				}
			}
			else{
				$erreur = 'Votre nom est trop long';
			}
	}
	else{
		$erreur = 'Tous les champs ne sont pas complétés';
	}
}
?>

<div id="carteSelectRGST" style="display: none;">
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
<input type="hidden" id="getLatCarte" value="<?php if (!empty($getLatCarte)) {echo $getLatCarte;} ?>">
<input type="hidden" id="getLngCarte" value="<?php if (!empty($getLngCarte)) {echo $getLngCarte;} ?>">

<div id="carré_main">
	<div id="containe_dipla" >
		<a href="main.php"><img id="dipla" src="imgLocal/dipla.svg" ></a>
	</div>
	<form method="POST" id="formCréa">
		<input type="hidden" id="putLat" name="putLat">
		<input type="hidden" id="putLng" name="putLng">

		<label class="errorLabel" id="errorLabel"><?php if (isset($erreur)) {echo $erreur;} ?></label>

		<label for="name">Nom de votre entreprise :</label>
		<input class="registerSub" type="text" name="name">

		<label for="mail">Mail :</label>
		<input class="registerSub" type="email" name="mail">

		<label for="mdp">Mot de passe :</label>
		<div class="contain_mdp">
			<input class="registerSub" type="password" id="mdpCrea" name="mdp" style="margin: 0;border: none;">
			<i class="las la-eye-slash" id="eye" onclick="voir_mdp();"></i>	
		</div>

		<label for="adresse">Adresse de votre entreprise :</label>
		<input class="registerSub" type="text" placeholder="numéro rue" name="adresse" id="adresse">
		<div>
			<section class="suggestContain" >
			  <button type="button" class="suggest"></button>
			  <button type="button" class="suggest"></button>
			  <button type="button" class="suggest"></button>
			  <button type="button" class="suggest"></button>
			  <button type="button" class="suggest"></button>
			</section> 
		</div>
		<a href="#carteSelectRGST" class="btn_true" id="hrefMap">Choisir sur la carte</a>
		<a class="linkBottomLog" href="politique.php">Politique de confidentialité</a>
	</form>
		 <section class="contain_btnCheck">
		   <a class="btn_false" href="main.php" >Annuler</a>
		   <button class="btn_true" id="suivant" >Suivant</button>
		 </section>
</div>

</body>
<script src="javascript/register.js"></script>
</html>