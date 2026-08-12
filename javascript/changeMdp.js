var password = document.querySelector('#newMdp');
var eye = document.getElementById('eyeChange');

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