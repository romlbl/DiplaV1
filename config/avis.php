<?php
//on récupère le nom de l'user
$queryName = $db->prepare('SELECT nom FROM utilisateurs WHERE id_user = ? ');
$queryName->execute(array($avis['id_user']));
$Name = $queryName->fetch();
// la date
$dateAvis = date("d/m/y",strtotime($avis['date']));
$targetIdAvs = htmlspecialchars($avis['id_item']);
?>
<div class="imgANDAvis">
    <a href="page_artcl.php?id=<?=$targetIdAvs?>" class="linkImg" target="_blank">
      <img class="imgAvsQst" src="img produits/img portrait/<?=$targetIdAvs?>a.png">
    </a>
  <div class="contain_info">
      <div class="exceptCoche">
      <section class="Section">
        <span class="objAvis"><?=$avis['objet']?></span>
        <div>
          <?php
          for ($i=0; $i < 5 ; $i++) { 
            if ($i < $avis['note']) { ?>
              <span  class="star_nota" style="color: yellow;" >★</span>
            <?php
          }else{ ?>
            <span  class="star_nota" style="color: #d3d3d3;" >★</span>
           <?php }
          }
          ?>
        </div>
      </section>
      <!--  -->
      <div class="contenuAvis"><?=$avis['contenu']?></div>
      <section class="Section">
        <span class="subInfo"><?=$Name['nom']?></span>
        <span class="subInfo"><?=$dateAvis?></span>
      </section>
    </div>
    <input type="checkbox" name="cocheAvis" class="cocheAvis" value="<?=$avis['id_avis']?>">
  </div>
</div>