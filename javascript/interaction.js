
// on gère les étoiles
  var stars = document.querySelectorAll(".la-star");
  var note_user = document.querySelectorAll("#input_note");

  for(star of stars){
    star.addEventListener("mouseover", function(){
      resetStars();
      this.style.color = "yellow";
      this.classList.add("las");
      this.classList.remove("lar");

      let étoile_avant = this.previousElementSibling;

      while(étoile_avant){
        étoile_avant.style.color = "yellow";
        étoile_avant.classList.add("las");
        étoile_avant.classList.remove("lar");
        étoile_avant = étoile_avant.previousElementSibling;
      }

    });

    star.addEventListener("click", function(){
      note_user.value = this.dataset.value;
      note = this.dataset.value;
    });

    star.addEventListener("mouseout", function(){
      resetStars(note_user.value);
    });
}

function resetStars(nb = 0){
  for(star of stars){
    if (star.dataset.value > nb ) {
      star.style.color = "black"; 
      star.classList.add("lar");
      star.classList.remove("las");   
    }else{
      star.style.color = "yellow";
      star.classList.add("las");
      star.classList.remove("lar");
    }
    
  }
}

// on enlève les images des avis et des questions
var imgs = document.querySelectorAll('.linkImg');
for(img of imgs){
  img.style.display = "none";
}
 
// on gère les réponse
function getId(value=null,content=null,cancel=false){
  showQst = document.getElementById('contentRep');
  QstType = document.getElementById('QstType');
  CancelRep = document.getElementById('CancelRep');
  if (cancel == false) {
    CancelRep.style.display="block";
    showQst.innerHTML = content.toString().replace(/\//g, "'");
    QstType.innerHTML = "Réponse:";
    idRep = value;
  }
  if (cancel == true) {
    CancelRep.style.display="none";
    showQst.innerHTML = "";
    QstType.innerHTML = "Question:";
    idRep = null;
  }
}

