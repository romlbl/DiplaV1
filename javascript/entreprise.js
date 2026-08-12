var ans_upper = document.getElementById('ans_upper');
var getIdRep = document.getElementById('getIdRep');
var errUpper = document.getElementById('errUpper');
var contentQst_upper = document.getElementById('contentQst_upper');

function onglet_upper(index){
	if (index == 0) {
		$.ajax({
		  type: 'POST',
		  url: 'modification.php',
		  data:  {queryOnglet: "qst"},
		  success: function(response) {
		    $('#inter_upper').html(response);
		  }
		}); 
	}
	else if (index == 1) {
		$.ajax({
		  type: 'POST',
		  url: 'modification.php',
		  data:  {queryOnglet: "avs"},
		  success: function(response) {
		    $('#inter_upper').html(response);
		  }
		}); 
	}
}

// on gère les réponse
function getId(value=null,content=null,cancel=false){
	if(cancel == false){
		getIdRep.value = value;
		contentQst_upper.innerHTML=content;
		ans_upper.style.display="flex";
	}
	else if(cancel == true){
		ans_upper.style.display="none";
	}
}

function sendRepUpper(){ 
	rep_qst = getIdRep.value;
	content = document.getElementById('questionUpper').value;
	$.ajax({
	  type: 'POST',
	  url: 'modification.php',
	  data:  {id_rep_U: rep_qst, content_U: content},
	  success: function(response) {
	  	errUpper.innerHTML="Réponse ajoutée";
	  	ans_upper.style.display="none";
	    $('#inter_upper').html(response);
	  }
	});  
}

// recherch en live
 $('#liveRecherche').keyup(function(){
 	var input = $(this).val();
	$.ajax({
	  type: 'POST',
	  url: 'modification.php',
	  data:  {input: input},
	  success: function(response) {
	    $('#inter_lower').html(response);
	  }
	}); 
 })

// on gère l'ajout d'article
var formData = new FormData();
formData.set('action','addSet')
const crop_window = document.getElementById("crop_window")
const croppedImg = document.getElementById("croppedImg");
const cropPreview = document.getElementById("cropPreview");
var error = document.getElementById("error");
const cropButton = document.getElementById("cropButton");
const cancelButton = document.getElementById("cancelButton");
cancelButton.addEventListener("click",cancelCrop);
cropButton.addEventListener('click', cropImg);
var cropper = null
var parent = null;
var side = null;
var num =  null;

// choisir img
function addImage(element,sideImg="V",numImg){
	var fileInput = element.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.click();
    	parent = element;
        side = sideImg;
        num = numImg;
        fileInput.addEventListener('change', function(event) {
        	event.stopPropagation();
	        getImage(event);
	        fileInput.value="";
        });
    }
}
// cropper image
function getImage(event) {
    var fileInput = event.target;
    const imageToProcess = fileInput.files[0];
	var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
	if (allowedExtensions.exec(fileInput.value)) {
		// on vérifie que l'img est pas trop volumineuse (128Mo au total mais secu a 120Mo)
		if (imageToProcess.size <= 30000000 ) {
			crop_window.style.cssText="display:flex";
			let newImg = new Image(imageToProcess.width, imageToProcess.height);
			newImg.src = URL.createObjectURL(imageToProcess);
			newImg.style.cssText="width:100%;height:auto;";
			croppedImg.appendChild(newImg);
			if (side == "V"){
				processImg(newImg,2,3);
			}
			else if(side == "H"){
				processImg(newImg,4,3);
			}
		}else{
			error.innerHTML = "L'image est trop volumineux";
		}
	}else if(fileInput.value != ""){
		error.innerHTML = "Le fichier n'est pas reconnu comme une image";
	}
}

// fonction pour preparer le crop
function processImg(gettedImg,ratio_a,ratio_b){
	cropPreview.style.height = 0.5*contain_btn_preview.offsetHeight+"px";
	cropPreview.style.width = 0.5*(ratio_a/ratio_b)*(contain_btn_preview.offsetHeight)+"px";
	cropper = new Cropper(gettedImg,{
	 	aspectRatio : ratio_a/ratio_b,
	 	preview: "#cropPreview",
	});
}
document.getElementById('rotateButton').addEventListener('click', function () {
    cropper.rotate(90);
});
// fonction pour crop
function cropImg(){
	if (cropper != null) {
		let finalImg = cropper.getCroppedCanvas();
		let trash = document.createElement("i");
		let var1 = side;
		let var2 = num;
		let var3 = parent;
		trash.className = 'las la-trash-alt';
		trash.onclick =  function() {
			delImg(var1,var2,var3);
			 return false;
		  };
		finalImg.toBlob(function(blob){
			if(blob.size > 10000000){
				var compressRate = 10000000/blob.size;
				compressBlob(blob, compressRate).then(function(compressedBlob) {
				formData.set(var1+var2, compressedBlob);
				});
			}else {
				formData.set(var1+var2, blob);
			}		
		})
	    parent.childNodes.forEach(function(element) {
	    	if (element.nodeType === 1) {
	    		element.style.display="none";
	    	}
	    });
		parent.appendChild(finalImg);
		parent.append(trash);
		cropper.destroy();
		while (croppedImg.hasChildNodes()) {
		  croppedImg.removeChild(croppedImg.firstChild);
		}
		parent = null;
		side = null;
		num = null;
		cropPreview.style.height = "0px";
		crop_window.style.cssText="display:none";
	}
}
function delImg(typeImg,numImg,parent,modif=false){
	if(modif ==false && formData.has(typeImg+numImg)){
		let imgP = parent.querySelector('canvas');
		let poubelle = parent.querySelector('.la-trash-alt');
		formData.delete(typeImg+numImg);
		imgP.remove();
		poubelle.remove();
	    parent.childNodes.forEach(function(element) {
	    	if (element.nodeType === 1 && element.tagName !== 'INPUT') {
	    		element.style.display="block";
	    	}
	    });
	}else if(modif == true){
		let addInput = document.createElement("input");
		let imgP = parent.querySelector('canvas');
		if (imgP) {
			imgP = imgP;
		}else{
			 imgP = parent.querySelector('img');
		}
		let poubelle = parent.querySelector('.la-trash-alt');
		formData.delete(typeImg+numImg);
		imgP.remove();
		poubelle.remove();
		addInput.className = 'input_img';
		addInput.type = 'file';
		if(parent.id === "mainImg"){
			let clickForm  = document.createElement("span");
			clickForm.innerHTML = "Ajouter une image";
			addInput.id = 'verticalMainImg';
			parent.append(clickForm);
			parent.append(addInput)
		}else{
			let clickForm  = document.createElement("i");
			clickForm.className = 'las la-plus';
			parent.append(clickForm);
			parent.append(addInput)
		}
	}
}
// annuler le crop
function cancelCrop(){
	if (cropper) {
		cropper.destroy();
	}
	while (croppedImg.hasChildNodes()) {
	  croppedImg.removeChild(croppedImg.firstChild);
	}
	parent = null;
	side = null;
	num = null;
	cropPreview.style.height = "0px";
	cropPreview.style.width = "0px";
	crop_window.style.cssText="display:none";	
}
//compresser
function compressBlob(blob, compressionRate) {
  return new Promise(function (resolve) {
    var reader = new FileReader();

    reader.onloadend = function () {
      var tempImg = new Image();
      tempImg.src = reader.result;

      tempImg.onload = function () {
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');

        canvas.width = tempImg.width;
        canvas.height = tempImg.height;

        ctx.drawImage(tempImg, 0, 0);

        canvas.toBlob(function (compressedBlob) {
          resolve(compressedBlob);
        }, 'image/png', compressionRate);
      };
    };

    reader.readAsDataURL(blob);
  });
}

// la carte
var hrefMap = document.getElementById('hrefMap');
var localAddr = document.getElementById('localAddr');
var showAddr = document.getElementById('addr_artcl');
var suggestCont = document.querySelectorAll('.suggest');
var villeMap = document.getElementById('villeMap');
var startLat = document.getElementById('getLatCarte').value;
var startLng = document.getElementById('getLngCarte').value
var suggestContMap = document.querySelectorAll('.suggestMapBtn');
var closeMap = document.getElementById('close_map');
// section edit
var putLatEdit = document.getElementById('putLatEdit');
var putLngEdit = document.getElementById('putLngEdit');


// créé une instance pour la carte
const platform = new H.service.Platform({
    'apikey': "DqPoy6CZmPGSgVNSDlpObo7f4ULAQTAhhFUqbuANW9w"
});
const defaultLayers = platform.createDefaultLayers();
const map = new H.Map(
    document.getElementById("map"),
    defaultLayers.vector.normal.map, {
        zoom: 17,
        center: {
            lat: startLat,
            lng: startLng
        }
    });
const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
const ui = H.ui.UI.createDefault(map, defaultLayers);
hrefMap.addEventListener('click', setUpClickListener(map));

// adresse du commerce
localAddr.addEventListener('click', function(){
  var geocoder = platform.getSearchService();
    reverseGeocodingParameters = {
       at: startLat+','+startLng+',150',
       limit: '1'
    };
    geocoder.reverseGeocode(
     reverseGeocodingParameters,
     function(result){
      var location = result.items;
      var coords = location[0]['position'];
      showAddr.value = location[0]['title'];
      formData.set('putLat',coords['lat']);
      formData.set('putLng',coords['lng']);
    }
   );
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
      formData.set('putLat',coords['lat']);
      formData.set('putLng',coords['lng']);
      closeMap.click();
    }
   );
  });
}
function suggestion(recherche,startLat,startLng,list,searchBar,edit=false){
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

function successLoad(response){
	//on reinitialise le formdata
  	formData = new FormData();
	$('#bench_item').html(response);
	hrefMap = document.getElementById('hrefMap');
	localAddr = document.getElementById('localAddr');
	showAddr = document.getElementById('addr_artcl');
	suggestCont = document.querySelectorAll('.suggest');
	putLatInput = document.getElementById('putLatInput').value;
	putLngInput = document.getElementById('putLngInput').value;
	supprArtclBtn = document.getElementById('delItem');
	canSupprArtcl = document.getElementById('canSuppArtcl');
	delArtclFrame = document.getElementById('confSuppArtcl');
	error = document.getElementById("error");
	// ajout des image actuel
	formData.set("putLat",putLatInput);
	formData.set("putLng",putLngInput); 
	formData.set("action","addSet"); 
	var imgIDs = ['ImgVa', 'ImgVb', 'ImgVc', 'ImgHa'];
	imgIDs.forEach(id => {
	    let imgElement = document.getElementById(id);
	    if (imgElement) {
	       fetch(imgElement.src).then(function (response) {
	         return response.blob();
	      }).then(function (blob) {
	      	formData.append(id.slice(-2),blob);
	      });
	    }
	});
	//maj live recherche
	$.ajax({
	  type: 'POST',
	  url: 'modification.php',
	  data:  {input: "TqMmeVwU"},
	  success: function(responseLive) {
	    $('#inter_lower').html(responseLive);
	  }
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
	// adresse du commerce
	localAddr.addEventListener('click', function(){
	  var geocoder = platform.getSearchService();
	    reverseGeocodingParameters = {
	       at: startLat+','+startLng+',150',
	       limit: '1'
	    };
	    geocoder.reverseGeocode(
	     reverseGeocodingParameters,
	     function(result){
	      var location = result.items;
	      var coords = location[0]['position'];
	      showAddr.value = location[0]['title'];
	      formData.set('putLat',coords['lat']);
	      formData.set('putLng',coords['lng']);
	    }
	   );
	})
	// masquer les suggestion au click en dehors
	document.addEventListener("click", function(event) {
	    if (!showAddr.contains(event.target)) {
		    for (var i = suggestCont.length - 1; i >= 0; i--) {
		      suggestCont[i].style.cssText="display:none";
		    }
	    }
	});
	if (supprArtclBtn && canSuppArtcl) {
		// supprimer l'article
		supprArtclBtn.addEventListener('click',function(){
			delArtclFrame.style.display="flex";
		});
		canSupprArtcl.addEventListener('click',function(){
			delArtclFrame.style.display="none";
		});
	}
	}

function showAddPanel(){
	$.ajax({
	  type: 'POST',
	  url: 'modification.php',
	  data:  {action: "showPanel", customId :"None"},
	  success: function(response) {
	  	successLoad(response);
	  }
	}); 
}

 //selectionner le produit a modif
 function SelectCustomItem(id_item){
	$.ajax({
	  type: 'POST',
	  url: 'modification.php',
	  data:  {action: "selectArtlc",id_item: id_item},
	  success: function(response) {
	    successLoad(response);
	  }
	}); 
 }

// envoi du formdata
function loadArtcl(){
	let customId = document.getElementById('customId').value;
	let titre = document.getElementById('name_artcl').value;
	let prix = document.getElementById('price_artcl').value;
	let adress = document.getElementById('addr_artcl').value;
	let typeArtcl = document.getElementById('type_add_artcl').value;
	let keysW = document.getElementById('keyWords').value;
	let dscrp = document.getElementById('dscrp_artcl').value;

	if( formData.has("Va") && formData.has("Ha")){
		if (titre != "" && prix != "" && adress != "" && typeArtcl != "" && keyWords != "" && dscrp != "") {
			if (titre.length < 255) {
				formData.set('customId',customId);
				formData.set('titre',titre);
				formData.set('prix',prix);
				formData.set('adress',adress);
				formData.set('typeArtcl',typeArtcl);
				formData.set('keysW',keysW);
				formData.set('dscrp',dscrp);

				if(!formData.has("putLat") && !formData.has("putLng")){
					// on geocode
					var service = platform.getSearchService();
					service.geocode({
					  q: adress
					}, (result) => {
						if (result){
						  	formData.set("putLat",result.items[0].position['lat']);
						  	formData.set("putLng",result.items[0].position['lng']);
						  	$.ajax({
							  type: 'POST',
							  url: 'modification.php',
							  processData: false,
		        			  contentType: false,
							  data:  formData,
							  success: function(response) {
							  	successLoad(response);
							  }
							});
						}else{
							error.innerHTML="Problème de geolocalisation de l'adresse";
						}
					});
				}else{
					$.ajax({
					  type: 'POST',
					  url: 'modification.php',
					  processData: false,
        			  contentType: false,
					  data:  formData,
					  success: function(response) {
					  	successLoad(response);
					  }
					}); 
				}

			}else{
				error.innerHTML="Le nom de l'article est trop long";
			}
		}else{
			error.innerHTML="Tous les champs ne sont pas complets";
		}
	}else{
			error.innerHTML="Ajouter au moins une image portait et une image paysage";
		}
}

function deleteArtcl(idTarget,idEntre){
	$.ajax({
	  type: 'POST',
	  url: 'modification.php',
	  data:  {action: "deleteArtlc",id_item: idTarget},
	  success: function(response) {
	    showAddPanel();
		onglet_upper(0);
		//maj live recherche
		$.ajax({
		  type: 'POST',
		  url: 'modification.php',
		  data:  {input: "TqMmeVwU"},
		  success: function(responseLive) {
		    $('#inter_lower').html(responseLive);
		  }
		}); 
	  }
	}); 
}
// modif et param
var edit_addr = document.getElementById('edit_addr');
var suggestEdit = document.querySelectorAll('.suggestEdit');
var errorEdit = document.getElementById('errorEdit');
var closeEdit = document.querySelectorAll('.link_close_set_edit')[0];
// suggestion edit
edit_addr.addEventListener('input', function() {
  recherche = edit_addr.value;
  if (recherche.length >= 4 && recherche.length % 4 === 0) {
    suggestion(recherche,startLat,startLng,suggestEdit,edit_addr,true);
  }else if(recherche.length === 0){
    for (var i = suggestEdit.length - 1; i >= 0; i--) {
      suggestEdit[i].style.cssText="display:none";
    }
  }
});

function ModifEntre(){
	let edit_name = document.getElementById('edit_name').value;
	let edit_mail = document.getElementById('edit_mail').value;
	let edit_addr = document.getElementById('edit_addr').value;
	let check = document.getElementById('checkNone').value;
	let latEdit = putLatEdit.value;
	let lngEdit = putLngEdit.value;
	if(latEdit == "" && lngEdit == ""){
		// on geocode
		var service = platform.getSearchService();
		service.geocode({
		  q: edit_addr
		}, (result) => {
			if (result.lenght > 0){
				result.items.forEach((item) => {
				latEdit = item.position['lat'];
				lngEdit = item.position['lng'];
				$.ajax({
				  type: 'POST',
				  url: 'modification.php',
				  data:  {action: "editEntre",name: edit_name, mail: edit_mail,
				   newAdrr: edit_addr, latEdit: latEdit, lngEdit: lngEdit, onPrdt: check},
				  success: function(response) {
				  	closeEdit.click();
				  }
				}); 
		  	});
			}else{
				errorEdit.innerHTML="Problème de geolocalisation de l'adresse";
			}
		});
	}else{
		$.ajax({
		  type: 'POST',
		  url: 'modification.php',
		  data:  {action: "editEntre",name: edit_name, mail: edit_mail,
		   newAdrr: edit_addr, latEdit: latEdit, lngEdit: lngEdit, onPrdt: check},
		  success: function(response) {
		  	if (response == "sucess") {
		  		closeEdit.click();
		  		window.location.reload();
		  	}else{
		  		errorEdit.innerHTML= response;
		  	}
		  }
		}); 
	}
				
}

function deleteEntre(idTarget){
	$.ajax({
	  type: 'POST',
	  url: 'modification.php',
	  data:  {action: "supprEntre"},
	  success: function(response) {
	  	window.location.href="config/deconnexion.php";
	  }
	});	
}