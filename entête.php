<?php
session_start();

if (isset($_GET['recherche']) AND !empty($_GET['recherche'])) {
	header("Location:recherche.php?query=".htmlspecialchars($_GET['recherche'])."");
	exit();
 }

//pour les bouton de catégories 
if (isset($_GET['btn']) AND !empty($_GET['btn'])) {
	//si le btn est "à proximité"
	if ($_GET['btn'] == 'à proximité' ) {
	    header("Location:recherche.php?query=Hl3QXpFP");
	    exit();
	}else{
	    header("Location:recherche.php?query=".htmlspecialchars($_GET['btn'])."");
	    exit();
	}
 }

// 'rf9d8D3H' est un mot aleatoire pour que personne mettent ce mot par erreur
if (isset($_GET['rf9d8D3H']) AND !empty($_GET['rf9d8D3H'])) {
    header("Location:recherche.php?query=rf9d8D3H");
    exit();
 }

 ?>
 
<style>
    
.test{
    border-left: solid 0.15em rgb(180,180,180);
    display:flex;
	align-items: center;
	justify-content: flex-start;
    height: 85%;
    padding-left: 0.2em;
    width: 50%;
}
.las.la-map-marker.main{
    margin-left: 0;
    margin-right: 0.5em;
    color: black;
}
.slide{
	border-radius: 1em;
}
</style>
 <link rel="stylesheet" href="line-awesome-1.3.0/1.3.0/css/line-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="style.css" ver="1.1">
<header id="mainContainer" style="height: 12%;justify-content: center;" >
	<div id="upper">
		<Section id="leftPart">
			<a href="main.php" class="linkLogo" >
				<img class="logo" src="imgLocal/dipla.svg">
			</a>
			<form method="GET">
				<div class="redirectTXT" id="decouvrir">
					<button name="rf9d8D3H" value="rf9d8D3H" class="decouvrir">Découvrir
						<i class="las la-globe"></i>
					</button> 
				</div>
			</form>
		</Section>
		<form id="barre-recherche"  method="GET">
			<button type="submit" class="search_icone">
				<i class="las la-search"></i>
			</button>
			<input class="zone_search" type="text" name="recherche" placeholder="Rechercher">
			<div class="test">
			    <i class="las la-map-marker main"></i>
			    <span>Lieu</span>
			</div>
		</form>
		<Section id="redirect">
			<a href="connexion.php" class="redirectLink" >
				<div class="redirectTXT">
					<span>Mon entreprise</span>
					<i class="las la-home"></i>
				</div>
			</a>
			<a href="identification.php" class="redirectLink">
				<div class="redirectTXT">
					<span>Mon compte</span>
					<i class="las la-user-circle"></i>
				</div>
			</a>
		</Section>
	</div>
	<form method="GET" id="containerCaté" style="display: none;">
		<Section class="SectionEntete">
			<input id="caté1" class="categories" type="submit" name="btn" value="jouets">
			<input id="caté2" class="categories" type="submit" name="btn" value="cuisine">
			<input id="3" class="categories" type="submit" name="btn" value="décoration">
		</Section>
		<Section class="SectionEntete">
			<input id="caté4" class="categories" type="submit" name="btn" value="à proximité">
			<input id="caté5" class="categories" type="submit" name="btn" value="livres">
			<input id="caté6" class="categories" type="submit" name="btn" value="vêtement">
		</Section>
		<Section class="SectionEntete">
			<input id="caté7" class="categories" type="submit" name="btn" value="sport">
			<input id="caté8" class="categories" type="submit" name="btn" value="nourriture">
			<input id="caté9" class="categories" type="submit" name="btn" value="bricolage">
		</Section>
	</form>
</header>