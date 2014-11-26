




//get numberOfCoordinates random coordinates from a size and numberOfCoordinates, if no numberOfCoordinates indicated, get half the size
function getRandomCoordinates(size, numberOfCoordinates) {

	if((typeof(numberOfCoordinates) === undefined)) {
		numberOfCoordinates = Math.ceil(size/2);
	}

	var arrayOfRandoms = [];

	for (i=0 ; i<numberOfCoordinates ; i++) {
		//We get numberOfCoordinates random numbers between 1, the user position, and (size² -1)
		var randomNumber = Math.max(1,(Math.floor(Math.random()*size * size) )); 	
		arrayOfRandoms.push(randomNumber);
	}

	return arrayOfRandoms;

}

function placeSweets(size, numberOfCoordinates) {

	var arrayOfRandomCoordinates = getRandomCoordinates(size,numberOfCoordinates);

	//alert('arrayOfRandomCoordinates done : '+arrayOfRandomCoordinates);

	var tds = document.querySelectorAll("#gameTable td");

	for (i=0 ; i<numberOfCoordinates ; i++) {
		var randomCoordinates = arrayOfRandomCoordinates[i];
		var td = tds[randomCoordinates];
		td.innerHTML = '0';
		td.setAttribute('style', 'color:blue');
		td.setAttribute('class', 'sweet');
	}


}

function placeUser() {

	var firsttd = document.querySelector("#gameTable td");

	firsttd.innerHTML = '@';
	firsttd.setAttribute('style', 'color:red');
	firsttd.setAttribute('class', 'user');

}

placeSweets(15,15);
placeUser();

(function() {

	document.addEventListener('keydown', (function(e) {
	//37 = left arrow
	//38 = up arrow
	//39 = right arrow
	//40 = down arrow
	var val = e.keyCode - 37;
	
	followKey(val);
	}),false);

    // Code isolé

})();


function followKey(valKey) {



	switch (valKey) {
		case 0:
		goLeft();
		break;

		case 1:
		goUp();
		break;

		case 2:
		goRight();
		break;

		case 3:
		goDown();
		break;

		default:
		break;


	}
}


function goLeft() {

	var user =  document.querySelector("td[class = user]");
	var coordinates = parseInt(user.id);

	if ( (coordinates%15 == 0) ) {
		alert('At the border');
	} else {

		var nextCell = document.getElementById(coordinates -1);

		//TODO : if nextCell is a sweet

		if (nextCell.getAttribute('class') == 'sweet') {
			sweetEaten();
		}

		newUserCell(user, nextCell);
		
	}
}


function goUp() {

	var user =  document.querySelector("td[class = user]");
	var coordinates = parseInt(user.id);

	if ( (coordinates<15) ) {
		alert('At the border');
	} else {

		var nextCell = document.getElementById(coordinates -15);

		//TODO : if nextCell is a sweet

		if (nextCell.getAttribute('class') == 'sweet') {
			sweetEaten();
		}

		newUserCell(user, nextCell);
		
	}

}

function goRight() {

	//alert('start goRight()');
	var user =  document.querySelector("td[class = user]");
	var coordinates = parseInt(user.id);

	if ( ((coordinates+1)%15 == 0) && coordinates != 0){
		alert('At the border');
	} else {

		//alert('goRight() : not at the border');

		var nextCell = document.getElementById(coordinates +1);

		//TODO : if nextCell is a sweet

		if (nextCell.getAttribute('class') == 'sweet') {
			sweetEaten();
		}

		newUserCell(user, nextCell);
		//alert('goRight() : done everything');

	}

	//alert(coordinates);

}

function goDown() {

	var user =  document.querySelector("td[class = user]");
	var coordinates = parseInt(user.id);

	if ( (coordinates>209) ) {
		alert('At the border');
	} else {

		var nextCell = document.getElementById(coordinates +15);

		//TODO : if nextCell is a sweet

		if (nextCell.getAttribute('class') == 'sweet') {
			sweetEaten();
		}

		newUserCell(user, nextCell);
		
	}

}

function sweetEaten() {
	var sweets = document.getElementById("sweets");
	var nb = parseInt(sweets.getAttribute('nb'));
	sweets.setAttribute('nb',nb+1);
	sweets.innerHTML = 'Sweet Eaten '+nb;
	alert('Sweet Eaten');
}

function newUserCell(user,nextCell) {

	nextCell.innerHTML = '@';
	nextCell.setAttribute('style', 'color:red');
	nextCell.setAttribute('class', 'user');

	user.innerHTML = '.';
	user.setAttribute('style', 'color:black');
	user.setAttribute('class', 'nothing');

}