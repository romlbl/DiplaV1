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
 <script src="javascript/jquery.min.js"></script>
 <link rel="stylesheet" href="line-awesome-1.3.0/1.3.0/css/line-awesome.min.css">

</head>
<body>
<?php include "config/database.php";
global $db;

if (empty($_SESSION['id_user'])) { 
	if (isset($_POST['subLog'])) {
		$mdp_user = htmlspecialchars($_POST['mdpLog']);
		$mail_user = htmlspecialchars($_POST['mailLog']);
		if (!empty($mail_user) && !empty($mdp_user)) {
			//on crypte le mdp
			$mdp = hash('md5', $mdp_user);
			$req_verif_db = $db->prepare("SELECT * FROM utilisateurs WHERE mail = ? AND mdp = ? ");
			$req_verif_db->execute(array($mail_user,$mdp));
			$verif_db = $req_verif_db->rowCount();
			if ($verif_db == 1) {
				$info_user = $req_verif_db->fetch();
				$_SESSION['id_user'] = $info_user['id_user'];
				// on met les vrai coords dans les variables session si possible
				if (!empty($info_user['latitude']) && !empty($info_user['longitude']) ) {
					// on sauvegarde les ancienne au cas ou on se deco
					$_SESSION['last_latitude'] = $_SESSION['latitudeFrom'];
					$_SESSION['last_longitude'] = $_SESSION['longitudeFrom'];
					// on met a jour les nouvelles
					$_SESSION['latitudeFrom'] = $info_user['latitude'];
					$_SESSION['longitudeFrom'] = $info_user['longitude'];
				}
		        // on redirige
				header('Location:compte.php');
				exit();					
			}else{
				$erreur_iden1a = "Identifiant ou mot de passe invalide";
			}
		}else{
			$erreur_iden1a = "Tous les champs ne sont pas complétés";
		}
	}
?>

<div class="containeLog">
	<a href="main.php" class="linkLogDipla">
		<img class="logoLog" src="imgLocal/dipla.svg">
	</a>
	<i class="las la-user-circle" id="imgUserLog"></i>
	<label class="errorLabel"><?php if (isset($erreur_iden1a)) {echo $erreur_iden1a;} ?></label>
	<form method="POST" class="containInputLog">
		<div class="HideButton"></div>
		<section class="TXTinputLog">
			<element class="subInputLog">
				<input class="TXTLog" type="email" name="mailLog" placeholder="mail">
			</element>
			<element class="subInputLog">
				<input class="TXTLog" id="mdpLog" type="password" name="mdpLog" placeholder="mot de passe">
				<i class="las la-eye-slash" id="eye" onclick="voir_mdp();"></i>
			</element>
		</section>
		<button class="HideButton" name="subLog">
			<i class="las la-angle-right"></i>
		</button>
	</form>
	<div class="containelinkLog">
		<a class="linkBottomLog" href="nvx_mdp/token.php">Mot de passe oublié</a>
		<a class="linkBottomLog" href="register.php">Créer un compte</a>
	</div>
</div>


<?php }
elseif (!empty($_SESSION['id_user'])) {
	header('Location:compte.php');
	exit();
}
?>
<script src="javascript/identification.js"></script>
</body>
</html>