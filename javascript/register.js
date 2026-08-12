var password = document.querySelector('#mdpCrea');
var eye = document.getElementById('eye');

function voir_mdp(){

	if (password.getAttribute('type')==='password') {
		password.setAttribute('type', 'text');
		eye.classList.remove("la-eye-slash");
		eye.classList.add("la-eye");
	 }
	else{
		password.setAttribute('type', 'password');
		eye.classList.remove("la-eye");
		eye.classList.add("la-eye-slash");
	 }
}
 
const hrefMap = document.getElementById('hrefMap');
const showAddr = document.getElementById('adresse');
const villeMap = document.getElementById('villeMap');
const putLat = document.getElementById('putLat');
const putLng = document.getElementById('putLng');
var startLat = document.getElementById('getLatCarte').value;
var startLng = document.getElementById('getLngCarte').value;
var suggestCont = document.querySelectorAll('.suggest');
var suggestContMap = document.querySelectorAll('.suggestMapBtn');
var closeMap = document.getElementById('close_map');
var sendBtn = document.getElementById('suivant');
var errorLabel = document.getElementById('errorLabel');
var formulaire = document.getElementById('formCréa');

// créé une instance pour la carte
const platform = new H.service.Platform({
    'apikey': "DqPoy6CZmPGSgVNSDlpObo7f4ULAQTAhhFUqbuANW9w"
});
const defaultLayers = platform.createDefaultLayers();
const map = new H.Map(
    document.getElementById("map"),
    defaultLayers.vector.normal.map, {
        zoom: 15,
        center: {
            lat: startLat,
            lng: startLng
        }
    });
const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
const ui = H.ui.UI.createDefault(map, defaultLayers);
hrefMap.addEventListener('click', setUpClickListener(map));

sendBtn.addEventListener('click', function(){
  if(showAddr.value != "" && putLat.value == "" && putLng.value == ""){
    // on geocode
    var service = platform.getSearchService();
    service.geocode({
      q: showAddr.value
    }, (result) => {
      if (result){
          putLat.value = result.items[0].position['lat'];
          putLng.value = result.items[0].position['lng'];
          formulaire.submit();
      }else{
        errorLabel.innerHTML="Problème de geolocalisation de l'adresse";
      }
    });
  }else  if(showAddr.value != "" && putLat.value != "" && putLng.value != ""){
    formulaire.submit();
  }
})


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

// masquer les suggestion au click en dehors
document.addEventListener("click", function(event) {
    if (!showAddr.contains(event.target)) {
      for (var i = suggestCont.length - 1; i >= 0; i--) {
        suggestCont[i].style.cssText="display:none";
      }
    }
});
// sugestionMap
villeMap.addEventListener('input', function() {
  recherche = villeMap.value;
  if (recherche.length >= 4 && recherche.length % 4 === 0) {
    suggestion(recherche,startLat,startLng,suggestContMap,showAddr);
  }else if(recherche.length === 0){
    for (var i = suggestContMap.length - 1; i >= 0; i--) {
      suggestContMap[i].style.cssText="display:none";
    }
  }
});

function setUpClickListener(map) {
  map.addEventListener('tap', function (evt) {
  var coord = map.screenToGeo(evt.currentPointer.viewportX,evt.currentPointer.viewportY);
  var geocoder = platform.getSearchService();
    reverseGeocodingParameters = {
       at: coord['lat']+','+coord['lng']+',150',
       limit: '1'
    };
    geocoder.reverseGeocode(
     reverseGeocodingParameters,
     function(result){
      var location = result.items;
      var coords = location[0]['position'];
      showAddr.value = location[0]['title'];
      putLat.value = coords['lat'];
      putLng.value = coords['lng'];
      closeMap.click();
    }
   );
  });
}
function suggestion(recherche,startLat,startLng,list,searchBar){
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
        list[i].value = [coords['lat'],coords['lng']];

        list[i].addEventListener('click', function() {
          searchBar.value = title;
          if (edit == false) {
            map.setCenter({'lat': coords['lat'], 'lng': coords['lng']});
            formData.set('putLat',coords['lat']);
            formData.set('putLng',coords['lng']);
          }else if(edit == true){
            putLatEdit.value = coords['lat'];
            putLngEdit.value = coords['lng'];
          }
          for (var i = list.length - 1; i >= 0; i--) {
            list[i].style.cssText="display:none";
          }
        });
      }
  }
  );
}
