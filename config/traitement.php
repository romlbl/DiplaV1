<?php
session_start();
include "database.php";
global $db;
 
if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
	$_SESSION['latitudeFrom'] = htmlspecialchars($_POST['latitude']);
	$_SESSION['longitudeFrom'] = htmlspecialchars($_POST['longitude']);
}


if (isset($_POST['idItem']) && $_POST['idItem'] != 0 && isset($_SESSION['id_user']) && isset($_POST['display'])) {
	$getid = htmlspecialchars($_POST['idItem']);
	$getid_user = htmlspecialchars($_SESSION['id_user']);
	$display = htmlspecialchars($_POST['display']);

	$check_fav = $db->prepare('SELECT id_fav FROM favoris WHERE id_item = ? AND id_user = ?');
	$check_fav->execute(array($getid,$getid_user));

	if ($check_fav->rowCount() == 1) {
		$del_fav = $db->prepare('DELETE FROM favoris WHERE id_item = ? AND id_user = ? LIMIT 1');
		$checkstate = $del_fav->execute(array($getid,$getid_user));
		if ($checkstate) {
			echo "suppr";
		}

	}elseif($check_fav->rowCount() == 0){
		$inserer = $db->prepare('INSERT INTO favoris (id_user,id_item) VALUES (?,?)');
		$checkstate = $inserer->execute(array($getid_user,$getid));
		if ($checkstate ) {
			echo "ajout";
		}
	}
}


 
?>