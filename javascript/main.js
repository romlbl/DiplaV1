var checkLat = document.getElementById("checkLat");
var checkLng = document.getElementById("checkLng");
var permission = document.getElementById("information");
var dark_back = document.getElementById("dark_back");
var errorlabel = document.getElementById('errorGPS');
var adresePanel = document.getElementById('adresePanel');
var showPanel = document.getElementById('showPanel');
var showAddr = document.getElementById('showAddr');
var suggestCont = document.querySelectorAll('.suggest');
var lat = 200;
var lng = 200;

if (checkLat.value == "" && checkLng.value == "" ) {
  permission.style.display="flex";
  dark_back.style.cssText="background-color:black;opacity:0.2;";
}

showPanel.addEventListener('click', function(){
  adresePanel.style.display="block";
})

document.addEventListener("click", function(event) {
    if (!showAddr.contains(event.target)) {
      for (var i = suggestCont.length - 1; i >= 0; i--) {
        suggestCont[i].style.cssText="display:none";
      }
    }
});
// les slider
$(document).ready(function(){
  $('.slider').slick({
    dots: true,
    arrows: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 5000,
    pauseOnHover:true,
  });
});

$(document).ready(function(){
  $('.slider500').slick({
    dots: true,
    arrows: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 5000,
    pauseOnHover:true,
  });
});


function get_coords(){
  if (navigator.geolocation) {
    // si oui, on appelle la fonction pour avoir la latitude et la longitude
    navigator.geolocation.getCurrentPosition(showPosition,showError);

  } 
  else {
   errorlabel.innerHTML = "La géolocalisation n'est pas supporter par votre navigateur";
  }

  function showPosition(position){
    lat = position.coords.latitude;//46.798500
    lng =  position.coords.longitude;//5.615250
      $.ajax({
        type: 'POST',
        url: 'config/traitement.php',
        data: {latitude: lat, longitude: lng },
        success: function(response) {
          location.reload();
        }
      });   
       
  }

  function showError(error){
    errorlabel.innerHTML = "La géolocalisation n'est pas supporter par votre navigateur";
  }
}

function sendGeo(){
  if (lat != 200 && lng != 200) {
       $.ajax({
        type: 'POST',
        url: 'config/traitement.php',
        data: {latitude: lat, longitude: lng },
        success: function(response) {
          location.reload();
        }
      });      
     }
    else if(showAddr.value != ""){
        // on geocode
        var service = platform.getSearchService();
        service.geocode({
          q: showAddr.value,
        }, (result) => {
          if (result){
            lat = result.items[0].position['lat'];
            lng = result.items[0].position['lng'];
           $.ajax({
            type: 'POST',
            url: 'config/traitement.php',
            data: {latitude: lat, longitude: lng },
            success: function(response) {
              location.reload();
            }
          }); 
          }else{
            errorlabel.innerHTML = "Problème de geolocalisation de l'adresse";
          }
        });
    }else{
      errorlabel.innerHTML = "Problème de geolocalisation de l'adresse";
    }
}

function traitementFavoris(idArtcl,event){
  $.ajax({
    type: 'POST',
    url: 'config/traitement.php',
    data: {display: "false",idItem: idArtcl},
    success: function(response) {
      if (response == "suppr") {
        event.target.classList.remove("las","la-heart");
        event.target.classList.add("lar","la-heart");
      }
      else if(response == "ajout"){
        event.target.classList.remove("lar","la-heart");
        event.target.classList.add("las","la-heart");
      }
    }
 }); 
}
// créé une instance pour la carte
const platform = new H.service.Platform({
    'apikey': "DqPoy6CZmPGSgVNSDlpObo7f4ULAQTAhhFUqbuANW9w"
});

showAddr.addEventListener('input', function() {
  recherche = showAddr.value;
  if (recherche.length >= 4 && recherche.length % 4 === 0) {
    suggestion(recherche,suggestCont,showAddr);
  }else if(recherche.length === 0){
    for (var i = suggestCont.length - 1; i >= 0; i--) {
      suggestCont[i].style.cssText="display:none";
    }
  }
});

function suggestion(recherche,list,showTarget){
  var service = platform.getSearchService(),
      geocodingParameters = {
        q: recherche,
        at: 0+','+0,
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
        lat = coords['lat'];
        lng = coords['lng'];
        for (var i = list.length - 1; i >= 0; i--) {
          list[i].style.cssText="display:none";
        }
        });
      }
  }
  );
}