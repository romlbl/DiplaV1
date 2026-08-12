<?php
session_start();
include "config/database.php";
global $db;

if (isset($_POST['data']) && isset($_SESSION['id_user']) && isset($_POST['type'])) {
	$id = htmlspecialchars($_SESSION['id_user']);
	$type = htmlspecialchars($_POST['type']);
	$data = $_POST['data'];

	if ($type === 'avs') {
		if ($data[0] !== 'all') {
			for ($i=0; $i < count($data) ; $i++) { 
				$statement = $db->prepare('DELETE FROM avis WHERE id_avis = ? ');
				$statement->execute(array($data[$i]));
			}
		}else{
			$statement = $db->prepare('DELETE FROM avis WHERE id_user = ?');
			$statement->execute($id);
		}
		// on retourne la mise a jour
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

	} 

	else if ($type === 'qst') {
		if ($data[0] !== 'all') {
			for ($i=0; $i < count($data) ; $i++) { 
				$statement = $db->prepare('DELETE FROM discussion WHERE id_qst = ? OR id_ans = ?');
				$statement->execute(array($data[$i],$data[$i]));
			}
		}else{
			$statement = $db->prepare('DELETE FROM discussion WHERE id_user = ?');
			$statement->execute($id);
		}

        // on retourne la mise a jour
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
	}

}

if (isset($_POST['action']) && $_POST['action'] == 'delHisto' && isset($_SESSION['id_user'])) {
	$id = htmlspecialchars($_SESSION['id_user']);
	$query_vide = $db->prepare("DELETE FROM historique WHERE id_user = ?");
	$query_vide->execute(array($id));
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
}

if (isset($_POST['action']) && $_POST['action'] == 'showFavoris' && isset($_SESSION['id_user'])) {
	$getid_user = htmlspecialchars($_SESSION['id_user']);
	$query_fav = $db->prepare("SELECT * FROM favoris WHERE id_user = ?");
	$query_fav->execute(array($getid_user));
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
}


?>