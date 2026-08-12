var type = 'artcl';
var errorFiltre = document.getElementById('errorFiltre');
var sliderP = document.getElementById("sliderPrix");
var outputP = document.getElementById("prix_value");
outputP.innerHTML = 'Prix max:   ' + sliderP.value + '€'; 

var sliderD = document.getElementById("sliderDistance");
var outputD = document.getElementById("dist_value");
outputD.innerHTML = 'Distance max:   ' + sliderD.value + 'km'; 

var prix = 0;
var dstc = 0;
var div = document.getElementById("box");

// on recupere certaines variable php ici 
var mot_rsch = document.getElementById("mot_rsch").value;
var formData = new FormData();
formData.set('rslt_ids', mot_rsch);

sliderP.oninput = function() {
  outputP.innerHTML = 'Prix max:   ' + this.value + '€';
}

sliderD.oninput = function() {
  outputD.innerHTML = 'Distance max:   ' + this.value + 'km';
}

$('#artc').click(function() {
        type = 'artcl';    
        formData.set('type',type);
        formData.set('conditP',sliderP.value);
        formData.set('conditD',sliderD.value);
        sendData();      

});

$('#srvc').click(function() {
        type = 'srvc';     
        formData.set('type',type);
        formData.set('conditP',sliderP.value);
        formData.set('conditD',sliderD.value);
        sendData();                    
});

$('#send').click(function() {
        formData.set('type',"");
        formData.set('conditP',sliderP.value);
        formData.set('conditD',sliderD.value);
        sendData();             
});

function sendData(){
    if(showAddr.value != "" &&!formData.has("specLat") && !formData.has("specLng")){
    // on geocode
    var service = platform.getSearchService();
    service.geocode({
      q: showAddr.value
    }, (result) => {
      if (result){
          formData.set("specLat",result.items[0].position['lat']);
          formData.set("specLng",result.items[0].position['lng']);
          $.ajax({
            type: 'POST',
            url: 'result.php',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#cadre').html(response);
                formData.delete('type');
                formData.delete('conditP');
                formData.delete('conditD');
            }
        });    
        }else{
          errorFiltre.innerHTML="Problème de geolocalisation de l'adresse";
      }
    });
  }else{
          $.ajax({
          type: 'POST',
          url: 'result.php',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
              $('#cadre').html(response);
              formData.delete('type');
              formData.delete('conditP');
              formData.delete('conditD');
          }
      });
      }
}

const hrefMap = document.getElementById('hrefMap');
const showAddr = document.getElementById('specLocation');
const villeMap = document.getElementById('villeMap');
var startLat = document.getElementById('getLatCarte').value;
var startLng = document.getElementById('getLngCarte').value;
var closeMap = document.getElementById('close_map');
var suggestCont = document.querySelectorAll('.suggest');
var suggestContMap = document.querySelectorAll('.suggestMapBtn');
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

// masquer les suggestion au click en dehors
document.addEventListener("click", function(event) {
    if (!showAddr.contains(event.target)) {
      for (var i = suggestCont.length - 1; i >= 0; i--) {
        suggestCont[i].style.cssText="display:none";
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
      formData.set('specLat',coords['lat']);
      formData.set('specLng',coords['lng']);
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