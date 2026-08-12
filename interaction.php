 <?php 
include "config/database.php";
global $db;

$id_produit = htmlspecialchars($_POST['id_artcl']);
$interType =  htmlspecialchars($_POST['type']);

if (!empty($_SESSION['id_user']) && $_POST['post'] == 'true') {
	if(!empty($_POST['obj']) && !empty($_POST['rate']) && !empty($_POST['content']) && !empty($id_produit)){
		$objet = htmlspecialchars($_POST['obj']);
		$rate = htmlspecialchars($_POST['rate']);
		$content = htmlspecialchars($_POST['content']);
		$user = htmlspecialchars($_SESSION['id_user']);

		$postQuery = $db->prepare("INSERT INTO avis (id_user,id_item,objet,contenu,note) VALUES (?,?,?,?,?)");
		$postQuery->execute(array($user,$id_produit,$objet,$content,$rate));
		$errorInter = "Avis posté !";
	}elseif (!empty($_POST['askContent'])) {
		$mainContent = htmlspecialchars($_POST['askContent']);
		if (!empty($_POST['idRep'])) {
			$idRep = htmlspecialchars($_POST['idRep']);
		}else{
			$idRep = NULL;
		}
		$postQuery = $db->prepare("INSERT INTO discussion (id_item,id_ans,id_user,contenu) VALUES (?,?,?,?)");
		$postQuery->execute(array($id_produit,$idRep,$user,$mainContent));
		$errorInter = "Envoyé !";
	}else{
		$errorInter = "Tous les champs ne sont pas remplis";
	}
}elseif(empty($_SESSION['id_user'])) {
	$errorInter = "Connectez-vous pour posté un avis ou une question";
}

if ($interType == 'avs') { ?>

	<div class="interFetch">
		<?php
			$queryAvis = $db->prepare('SELECT * FROM avis WHERE id_item= ? ');
			$queryAvis->execute(array($id_produit));
			if ($queryAvis->rowCount() > 0) {
				while ($avis = $queryAvis->fetch()) {
				  include "config/avis.php";
				}
			}else{
				?><span class="EmptyResult">Aucun avis pour le moment</span><?php
			}
		?>
	</div> 
	<hr class="separation">
	<div class="SendInter">
		<label class="errorLabel"><?php if (isset($errorInter)){echo $errorInter;}?></label>
		<table class="tableForm">
		  <tr class="td_table_parent">
		    <td><label for="nom" class="titleZone">Objet :</label></td>
		    <td><input class="inputZone" type="text" id="obj" required></td>
		  </tr>
		  <tr class="td_table_parent">
		    <td><label for="note" class="titleZone">Note :</label></td>
		    <td><div class="containeStar" id="note"><li class="lar la-star" data-value="1"></li><li class="lar la-star" data-value="2"></li><li class="lar la-star" data-value="3"></li><li class="lar la-star" data-value="4"></li><li class="lar la-star" data-value="5"></li></div></td>
		    <td><input type="hidden" id="input_note" value="0"></td>
		  </tr>
		  <tr class="td_table_parent">
		    <td><label for="content" class="titleZone">Contenu :</label></td>
		    <td><textarea class="largeZone" id="content" rows="7" required></textarea></td>
		  </tr>
		</table>
		<div class="postFrame">
			<button class="postBtn" onclick="sendAvis();">Poster</button>
		</div>
	</div>

<?php }
elseif ($interType == 'qst') { ?>

	<div class="interFetch">
		<?php
			$queryQst = $db->prepare('SELECT * FROM discussion WHERE id_item = ? AND id_ans IS NULL ');
			$queryQst->execute(array($id_produit));
			if ($queryQst->rowCount() > 0) {
				while ($question = $queryQst->fetch()) {
				  include "config/question.php";
				}
			}else{ 
				?><span class="EmptyResult">Aucune question pour le moment</span><?php
			}
		?>
	</div>
	<hr class="separation">
	<div class="SendInter">
		<label class="errorLabel"><?php if (isset($errorInter)){echo $errorInter;}?></label>
		<small id="contentRep"></small>
		<table class="tableForm">
		  <tr class="td_table_parent">
		    <td><label for="question" class="titleZone" id="QstType">Question :</label></td>
		    <td><textarea class="largeZone" id="question" rows="7" required></textarea></td>
		  </tr>
		</table>
		<div class="postFrame">
			<button class="postBtnCancel" id="CancelRep" onclick="getId(null,null,true)">Annuler</button>
			<button class="postBtn" onclick="sendQst('ask');">Poster</button>
		</div>
	</div>

<?php }

?>
<script type="text/javascript" src="javascript/interaction.js"></script>