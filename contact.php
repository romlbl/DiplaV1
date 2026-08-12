<!DOCTYPE html>
<html>
<head>
	<title>Dipla</title>
 <meta charset="utf8_general_ci"> 
 <meta name="viewport" content="width=device-width, initial-scale=1.0" >
<link rel="icon" type="image/ico" href="imgLocal/shortcut.ico" />
 <link rel="stylesheet" type="text/css" href="style.css" ver="1.1">
 <script src="javascript/jquery.min.js"></script>
 <link rel="stylesheet" href="line-awesome-1.3.0/1.3.0/css/line-awesome.min.css">
</head>
<body>
<?php
include "config/database.php";
global $db;

if (isset($_POST['sendCom'])) {
	if (!empty($_SESSION['id_user']) or !empty($_SESSION['id_entre'])) {
		if (!empty($_POST['comObj']) && !empty($_POST['comMsg'])) {
			$obj = htmlspecialchars($_POST['comObj']);
			$msg = htmlspecialchars($_POST['comMsg']);
			if (!empty($_SESSION['id_user'])) {
				$id= htmlspecialchars($_SESSION['id_user']);
				$stmt = $db->prepare('SELECT mail FROM utilisateur WHERE id_user = ?');
				$stmt->execute(array($id));
				$rslt= $stmt->fetch();
				$mailUser = $rslt['mail'];
				$type = '(user)';
			}else if(!empty($_SESSION['id_entre'])){
				$id= htmlspecialchars($_SESSION['id_entre']);
				$stmt = $db->prepare('SELECT mail_entre FROM entreprise WHERE id_entre = ?');
				$stmt->execute(array($id));
				$rslt= $stmt->fetch();
				$mailUser = $rslt['mail_entre'];
				$type = '(entreprise)';
			}
			
			$header = "Content-Type: text/html; charset=utf-8\r\n";
			$msg = implode(" | ", array($msg,"id : ",$id," ",$type));
			$mail = "mail de dipla";

			if (mail($mailUser,$obj,$msg,$header)) {
				$errorCom = "Message envoyé";
			}else{
				$errorCom = "Une erreur est survenue";
			}
		}else{
			$errorCom = "Les champs ne sont pas complétés";
		}
	}else{
		$errorCom = "Connectez-vous";
	}
}

?>
<div class="mainCom">
	<a href="main.php" class="linkComDipla">
		<img style="height: 100%;width: 100%;" src="imgLocal/dipla.svg">
	</a>
	<label id="errorCom" class="errorLabel"><?php if (isset($errorCom)) {echo $errorCom;} ?></label>
	<form method="POST" class="wideCom">
		<table  class="wideCom">
			<tr>
				<td class="tdCom">
					<label class="labCom" for="comObj">Objet:&nbsp</label>
					<input class="txtCom" type="text" name="comObj" id="comObj">
				</td>
			</tr>
			<tr>
				<td class="tdCom">
					<label class="labCom" for="comMsg">Message:&nbsp</label>
					<textarea class="txtCom" id="comMsg" name="comMsg" rows="10"></textarea>
				</td>
			</tr>
		</table>
		  <section class="contain_btnCheck">
		    <a class="btn_false" href="main.php" >Annuler</a>
		    <button class="btn_true" name="sendCom">Envoyer</button>
		  </section>
	</form>
</div>

</body>
</html>