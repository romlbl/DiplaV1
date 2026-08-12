//l'img et les subImg
const imgProduit = document.getElementById('imgProduit');
const subImg = document.getElementsByClassName('subImg');
//les variables de coords:
var var_lat = document.getElementById('coords_lat').value;
var var_lng = document.getElementById('coords_lng').value;
var var_lat_from = document.getElementById('coords_lat_from').value;
var var_lng_from = document.getElementById('coords_lng_from').value;
// initialisation de la map
var dstcShow = document.getElementById('dstc');
var tpsShow = document.getElementById('tps');
const platform = new H.service.Platform({
  'apikey': "DqPoy6CZmPGSgVNSDlpObo7f4ULAQTAhhFUqbuANW9w"
});
const defaultLayers = platform.createDefaultLayers();
const map = new H.Map(
  document.getElementById("map"),
  defaultLayers.vector.normal.map, {
     zoom: 10,
      center: {
          lat: var_lat_from,
          lng: var_lng_from
        }
    }); 
const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
const ui = H.ui.UI.createDefault(map, defaultLayers);
window.addEventListener('resize', () => map.getViewPort().resize());
// pour les interaction
var formData = new FormData();
var id_artcl = document.getElementById('id_produit').value;
var idRep = null;
formData.append('id_artcl',id_artcl);
//pour enlever les img des avis et quest
const linkImg = document.querySelectorAll(".linkImg");
for (var i = 0; i < linkImg.length; i++) {
  linkImg[i].style.display = "none";
}

//les étoiles
window.onload = () => {
  // afficher les avis au chargement
  interaction('avs');

}
 
function interaction(wich){
  formData.append('type',wich);
  formData.append('post', 'false');
  $.ajax({
      type: 'POST',
      url: 'interaction.php',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
          $('#showInteraction').html(response);
      }
  });   
}

function selectImg(which,id){
  let path = "img produits/img portrait/"+id+which+".png";
  imgProduit.src = path;
  for (var i = subImg.length - 1; i >= 0; i--) {
    subImg[i].style.cssText="border:none;opacity:0.75;";
  }
  document.getElementById("sub_"+which).style.cssText="border:solid 0.15em #e28989;opacity:1;";
}


function sendAvis(){
  objet = document.getElementById('obj').value;
  content = document.getElementById('content').value;
  formData.delete('post');
  formData.append('post', 'true');
  formData.append('obj',objet);
  formData.append('rate',note);
  formData.append('content',content);
  $.ajax({
      type: 'POST',
      url: 'interaction.php',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
          $('#showInteraction').html(response);
          formData.delete('obj');
          formData.delete('rate');
          formData.delete('content');
      }
  });   
}

function sendQst(type){
  question = document.getElementById('question').value;
  formData.delete('post');
  formData.append('post', 'true');
  formData.append('askContent',question);
  if (idRep !== null) {
    formData.append('idRep',idRep);
  }
  $.ajax({
      type: 'POST',
      url: 'interaction.php',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
          $('#showInteraction').html(response);
          formData.delete('idRep');
          formData.delete('askContent');
          idRep = null;
      }
  });  
}

//appeler la fonction itinéraire ( coordeonnées d'exemple)

//fonction php pour avoir la distance etle temps d'un trajet en voiture entre 2 coordonnées
function itinéraire(platform,latfrom,lngfrom,latto,lngto) {
  var router = platform.getRoutingService(null, 8),
      routeRequestParams = {
        routingMode: 'fast',
        transportMode: 'car',
        origin: latfrom+','+lngfrom,
        destination: +latto+','+lngto,
        return: 'polyline,turnByTurnActions,actions,instructions,travelSummary'
      };

  router.calculateRoute(
    routeRequestParams,
    onSuccess
  );
}
function onSuccess(result) {
  var route = result.routes[0];
  let duration = 0, distance = 0;
  route.sections.forEach((section) => {
  let linestring = H.geo.LineString.fromFlexiblePolyline(section.polyline);
  let polyline = new H.map.Polyline(linestring, {
    style: {
      lineWidth: 5,
      strokeColor: 'rgba(0, 128, 255, 0.7)'
    }
  });
  map.addObject(polyline);
  map.getViewModel().setLookAtData({
    bounds: polyline.getBoundingBox()
    });
  });
  route.sections.forEach((section) => {
    distance += section.travelSummary.length;
    duration += section.travelSummary.duration;
  });
  dstcShow.innerHTML = (distance/1000).toFixed(2)+"km";
  tpsShow.innerHTML = getTime(duration);
}
function getTime(timeSummary) {
  var heures = Math.floor(timeSummary / 3600);
  var minutes = Math.floor((timeSummary % 3600) / 60);
  var temps = "";

  if (heures > 0) {
    temps += heures + " H";
    temps += " ";
  }
  
  temps += minutes + " min";
  
  return temps;
}

if ( var_lat_from != '' && var_lng_from != '') {
  itinéraire(platform,var_lat_from, var_lng_from,var_lat,var_lng);
}

