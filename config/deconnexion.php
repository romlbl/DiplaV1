<?php
session_start();

if (isset($_SESSION['id_user'])) {
	unset($_SESSION['id_user']);
}
if (isset($_SESSION['id_entreprise'])) {
	unset($_SESSION['id_entreprise']);
}
// pour avoir des coords 
$_SESSION['latitudeFrom'] = $_SESSION['last_latitude'];
$_SESSION['longitudeFrom'] = $_SESSION['last_longitude'];
header('Location:/main.php');
exit();
?>