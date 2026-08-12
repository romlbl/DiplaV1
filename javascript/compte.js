var onglets = document.querySelectorAll(".conteneurRight");
var cocheAvs = document.querySelectorAll(".cocheAvis");
var cocheQst = document.querySelectorAll(".cocheQst");
var allcocheAvs = document.getElementById("AllcocheAvs");
var allcocheQst = document.getElementById("AllcocheQst");
var suppr = document.getElementById("supprAvsQst");
var txt_contain = document.getElementById("nbSuppr"); 
var txt = '';
var startLat = document.getElementById('getLatCarte').value;
var startLng = document.getElementById('getLngCarte').value;
var putLat = document.getElementById('putLat');
var putLng = document.getElementById('putLng');
var suggestCont = document.querySelectorAll('.suggest');
const showAddr = document.getElementById('edit_addr');

function onglet(index){
	for (var i = onglets.length - 1; i >= 0; i--) {
		onglets[i].classList.remove("active");
	}
	if (index == 1) {
		type = "avs";
		txt = " avis";
	}
	if (index == 2) {
		type = "qst";
		txt = " questions";
	}
	// on décoche toutes les coches
	allcocheAvs.checked = false;
	allcocheQst.checked = false;
	cocheAvs.forEach(function(node){
		node.checked = false;
	})
	cocheQst.forEach(function(node){
		node.checked = false;
	})
	// on fait apparitre l'onglet
	onglets[index].classList.add("active");
}

// on fait apparaitre les coche
cocheAvs.forEach(function(node){
	node.style.cssText="display:block";
})
cocheQst.forEach(function(node){
	node.style.cssText="display:block";
})

//on gère les coche des avis
allcocheAvs.addEventListener("change", (coche) => {
  if (coche.target.checked) {
	cocheAvs.forEach(function(node){
		node.checked = true;
	})
  }
  else {
	cocheAvs.forEach(function(node){
		node.checked = false;
	})
  }
});
// décocher la coche principal si une coche est décochée 
cocheAvs.forEach(function(node){
	node.addEventListener("change", (elem) => {
		if (elem.target.checked === false) {
			allcocheAvs.checked = false;
		}
	})
})

//on gère les coche des question
allcocheQst.addEventListener("change", (coche) => {
  if (coche.target.checked) {
	cocheQst.forEach(function(node){
		node.checked = true;
	})
  }
  else {
	cocheQst.forEach(function(node){
		node.checked = false;
	})
  }
});
// décocher la coche principal si une coche est décochée 
cocheQst.forEach(function(node){
	node.addEventListener("change", (elem) => {
		if (elem.target.checked === false) {
			allcocheQst.checked = false;
		}
	})
})

function showSuppr(state){
	if (state == "open") {
		var tabCoches = [];
		// on balai chaque type de coche;
		if (allcocheAvs.checked == true && type == 'avs') {
			data = 'all';
			n = "tous les ";
		}else if(type == 'avs'){
			cocheAvs.forEach(function(node){
				if (node.checked == true) {
					tabCoches.push(node.value);
				}
			})
			data = tabCoches;
			n = data.length;	
		}

		if (allcocheQst.checked == true && type == 'qst') {
			data = 'all';
			n = "toutes les ";
		}else if(type == 'qst'){
			cocheQst.forEach(function(node){
				if (node.checked == true) {
					tabCoches.push(node.value);
				}
			})
			data = tabCoches;
			n = data.length;
		}
		if (n != 0) {
			suppr.style.display="flex";
			txt_contain.innerHTML =" " + n +txt;
		}
	}
	if (state == "close") {
		suppr.style.display="none";
		var tabCoches = [];
		data = tabCoches
	}
}

function deleteAvsQst(){
	// ajax
    $.ajax({
      type: 'POST',
      url: 'modificationUser.php',
      data: {type: type, data: data},
	  success: function(response) {
	  	if (type == 'avs') {
	  		$('#containeAvis').html(response);
	  	}
	  	else if (type == 'qst') {
	  		$('#containeQuestion').html(response);
	  	}
	   suppr.style.display="none";
	  }
   }); 
}
  
function emptyHisto(){
    $.ajax({
      type: 'POST',
      url: 'modificationUser.php',
      data: {action:"delHisto"},
	  success: function(response) {
	  	$('#containHistorique').html(response);
	  }
   }); 
}

function traitementFavoris(idArtc,event){
    $.ajax({
      type: 'POST',
      url: 'config/traitement.php',
      data: {display: "true",idItem: idArtc},
	  success: function(response) {
	      if (response == "suppr") {
	        event.target.classList.remove("las","la-heart");
	        event.target.classList.add("lar","la-heart");
	      }
	      else if(response == "ajout"){
	        event.target.classList.remove("lar","la-heart");
	        event.target.classList.add("las","la-heart");
	      }
	    $.ajax({
		      type: 'POST',
		      url: 'modificationUser.php',
		      data: {action:"showFavoris"},
			  success: function(response) {
			  	$('#containFavoris').html(response);
			  }
		   }); 
	  }
   }); 
}

// créé une instance pour la carte
const platform = new H.service.Platform({
    'apikey': "DqPoy6CZmPGSgVNSDlpObo7f4ULAQTAhhFUqbuANW9w"
});
// suggestion principale
showAddr.addEventListener('input', function() {
  recherche = showAddr.value;
  if (recherche.length >= 4 && recherche.length % 4 === 0) {
    suggestion(recherche,startLat,startLng,suggestCont,showAddr);
  }else if(recherche.length === 0){
    for (var i = suggestCont.length - 1; i >= 0; i--) {
      suggestCont[i].style.cssText="display:none";
    }
  }
});

function suggestion(recherche,startLat,startLng,list,showTarget){
  var service = platform.getSearchService(),
      geocodingParameters = {
        q: recherche,
        at: startLat+','+startLng,
        limit: 5
      };
    service.autosuggest(
    geocodingParameters,
    function(result){
      var location = result.items;
      for (var i = list.length - 1; i >= 0; i--) {
        list[i].style.cssText="display:none";
      }
      for (var i = location.length - 1; i >= 0; i--) {
        title = location[i]['title'];
        coords = location[i]['position'];
        list[i].style.cssText="display:block;";
        list[i].innerHTML = title;
		list[i].addEventListener('click', function() {
		showTarget.value = title;
		putLat.value = coords['lat'];
		putLng.value = coords['lng'];
		});
	    for (var i = list.length - 1; i >= 0; i--) {
	      list[i].style.cssText="display:none";
	    }
      }
  }
  );
}