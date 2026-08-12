
<?php
//on récupère le nom de l'user
$queryName = $db->prepare('SELECT nom FROM utilisateurs WHERE id_user = ? ');
$queryName->execute(array($question['id_user']));
$Name = $queryName->fetch();
// la date
$dateQst = date("d/m/y",strtotime($question['date']));
// on récupère l'id de l'article
$targetId = htmlspecialchars($question['id_item']);

$stringQst = str_replace("'", "/", htmlspecialchars( $question['contenu']));
?>
<div class="imgANDQst">
	<a href="page_artcl.php?id=<?=$targetId?>" class="linkImg" target="_blank">
		<img class="imgAvsQst" src="img produits/img portrait/<?=$targetId?>a.png">
	</a>
  <div class="contain_info">
    <div class="exceptCoche">
      	<span class="objAvis"><?=$question['contenu']?></span>
      	<div class="btnRep">
      		<button class="showRep">Voir les réponse</button>
      		<div class="containeRep">
      			<?php
			        // on recupères les info de l'articles
			        $queryRep = $db->prepare('SELECT * FROM discussion WHERE id_ans = ? ORDER BY (certif = 1) DESC ');
			        $queryRep->execute(array($question['id_qst']));
			        if ($queryRep->rowCount() > 0) {
			          while ($reponse = $queryRep->fetch()) { 
			          	//on récupère le nom de l'user
			          	if ($reponse['id_user'] != null) {
							$queryNameRep = $db->prepare('SELECT nom FROM utilisateurs WHERE id_user = ? ');
							$queryNameRep->execute(array($reponse['id_user']));
							$NameRep = $queryNameRep->fetch();
							$NameRep = $NameRep['nom'];
			          	}elseif ($reponse['id_entre'] != null) {
			          		$queryNameRep = $db->prepare('SELECT nom_entre FROM entreprise WHERE id_entre = ? ');
							$queryNameRep->execute(array($reponse['id_entre']));
							$NameRep = $queryNameRep->fetch();
							$NameRep = $NameRep['nom_entre'];
			          	}
						// la date
						$dateQstRep = date("d/m/y",strtotime($reponse['date']));
			          	?>
			          	<div class="packageRep">
			          		<span class="contenuQst"><?=$reponse['contenu']?></span>
						    <section class="Section">
						        <span class="subInfo"><?php
						        if ($reponse['certif'] == true) { ?>
						        	<span><?=$NameRep?></span>
									<img src="ImgLocal/certif.svg">
									<?php
								}else{
									echo $NameRep;
								}
						        ?></span>
						        <span class="subInfo"><?=$dateQstRep?></span>
						    </section>
			          	</div>
			            
			          <?php }
			        }
			        else { ?>
			        <span>Aucune réponse pour l'instant</span>
			      <?php } ?>
      		</div>
      	</div> 
	    <section class="Section">
	        <span class="subInfo"><?=$Name['nom']?></span>
	        <span class="subInfo"><?=$dateQst?></span>
          <span class="repondreBtn" onclick="getId(<?=$question["id_qst"]?>,'<?php echo $stringQst ?>');">Répondre</span>
	    </section>
    </div>
    <input type="checkbox" name="cocheAvis" class="cocheQst" value="<?=$question['id_qst']?>">
  </div>
</div>
