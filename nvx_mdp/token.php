<!DOCTYPE html>
<html>
<head>
<title>Dipla</title>
<meta charset="utf8_general_ci"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" >
<link rel="icon" type="image/ico" href="../imgLocal/shortcut.ico" />
<link rel="stylesheet" type="text/css" href="../style.css" ver="1.1">
 <link rel="stylesheet" href="../line-awesome-1.3.0/1.3.0/css/line-awesome.min.css">
<script src="../javascript/jquery.min.js"></script>
</head>
<body>
<?php
include "../config/database.php";
global $db;

if (isset($_POST['sendCode']) && !empty($_POST['mailNewMdp'])) {
	$mail = htmlspecialchars($_POST['mailNewMdp']);
	//on vérifie que le compte existe
	$stmt = $db->prepare('SELECT id_user FROM utilisateurs WHERE mail = ?');
	$stmt->execute(array($mail));
	if ($stmt->rowCount() > 0) {
		// on génère un token
		$token =  hash('md5', mt_rand(10000000,99999999));
		$url = "http://localhost/DIPLA/nvx_mdp/passwordUpdate.php?token=".$token."";
		//on envoie le mail
		$message = "Voici votre lien pour reinitialiser votre mot de passe : ".$url."";
		$header .= "Content-Type: text/html; charset=utf-8\r\n";
		$header .= "From: Nom Expéditeur <dipla@dipla.site\r\n";;

		if (mail($mail,"Changement de mot de passe",$message,$header)) {
			// on set le token au compte
			$addToken = $db->prepare("UPDATE utilisateurs SET token = ? WHERE mail = ?");
			$addToken->execute(array($token,$mail));
			$errorNewMdp = "Mail envoyé";
		}else{
			$errorNewMdp = "Une erreur est survenue";
		}
	}else{
		$errorNewMdp = "Le compte n'a pas été trouvé";
	}
}

?>

<form method="POST" class="containeUpdateMdp">
	<a href="../main.php" class="LogoNewMdp">
		<img  style="height: 100%;width: auto;" src="../imgLocal/dipla.svg">
	</a>
	<span class="textTitleNew">Nous allons vous envoyé un lien</span>
	<label class="errorNewMdp"><?php if (isset($errorNewMdp)) {echo $errorNewMdp;} ?></label>
	<input type="email" name="mailNewMdp" class="textNewMdp" placeholder="Adresse mail du compte">
	<input type="submit" name="sendCode" class="SendNewMdp" value="Envoyer">
</form>

</body>
</html>
