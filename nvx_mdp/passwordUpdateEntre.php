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

if (isset($_POST['SubNewmdp'])) {
	if (!empty($_POST['newMdp']) && !empty($_GET['token'])) {
		$gotToken = htmlspecialchars($_GET['token']);
		$password = htmlspecialchars($_POST['newMdp']);
		$hashedMdp = hash('md5', $password);
		// on met à jour et on suppr le token
		$newPswrd = $db->prepare("UPDATE entreprise SET mdp_entre = ? , token = NULL WHERE token = ?");
		$check = $newPswrd->execute(array($hashedMdp,$gotToken));
		if($check){
		    header('Location: ../connexion.php');
		}else{
    		$errorNewMdp = 'Une erreur est survenue';
    	}
	}else{
		$errorNewMdp = 'Une erreur est survenue';
	}
}


?>

<form method="POST" class="containeUpdateMdp">
	<a href="../main.php" class="LogoNewMdp">
		<img  style="height: 100%;width: auto;" src="../imgLocal/dipla.svg">
	</a>
	<label class="errorNewMdp"><?php if (isset($errorNewMdp)) {echo $errorNewMdp;} ?></label>
	<label for="newMdp">Nouveau mot de passe : </label>
	<section id="BoxNewMdp">
		<input type="password" name="newMdp" id="newMdp">
		<i class="las la-eye-slash" id="eyeChange" onclick="voir_mdp();"></i>	
	</section>
	<input type="submit" name="SubNewmdp" class="SendNewMdp" value="Valider">
</form>

</body>
<script src="../javascript/changeMdp.js"></script>
</html>
