/***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************/

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
		//alert('At the border');
	} else {

		socket.send("moveUser;"+(coordinates -1));
		
	}
}


function goUp() {

	var user =  document.querySelector("td[class = user]");
	var coordinates = parseInt(user.id);

	if ( (coordinates<15) ) {
		//alert('At the border');
	} else {

		socket.send("moveUser;"+(coordinates -15));

	}

}

function goRight() {

	
	var user =  document.querySelector("td[class = user]");
	var coordinates = parseInt(user.id);

	if ( ((coordinates+1)%15 == 0) && coordinates != 0){
		//alert('At the border');
	} else {

		socket.send("moveUser;"+(coordinates +1));

	}

	

}

function goDown() {

	var user =  document.querySelector("td[class = user]");
	var coordinates = parseInt(user.id);

	if ( (coordinates>209) ) {
		//alert('At the border');
	} else {

		socket.send("moveUser;"+(coordinates +15));
	
	}

}

/***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************/

var socket;
var user;
init();


 
    function init() {
        var host = "ws://localhost:9000/multiplayergameserver.php"; // SET THIS TO YOUR SERVER
         
        try
        {
            socket = new WebSocket(host);
            log('WebSocket - status ' + socket.readyState);
             
            socket.onopen = function(msg) 
            { 
                if(this.readyState == 1)
                {
                    log("We are now connected to websocket server. readyState = " + this.readyState); 

                    
                    socket.send("initGame");
                    
                }
            };
             
            //Message received from websocket server
            socket.onmessage = function(msg) 
            { 

            	var keyMessage = msg.data.substring(0,2);

            	switch (keyMessage) {

            		case "is":
            		placeSweets(msg.data);
            		break;

            		case "iu":
            		placeUser(msg.data);
            		break;

            		case "ss":
            		supprSweet(msg.data);
            		break;

            		case "mu":
            		moveUser(msg.data);
            		break;

            		default:
            		log(" [ + ] Received: " + msg.data); 

            	}
                
            };
             
            //Connection closed
            socket.onclose = function(msg) 
            { 
                log("Disconnected - status " + this.readyState); 
            };
             
            socket.onerror = function()
            {
                log("Some error");
            }
        }
         
        catch(ex)
        { 
            log('Some exception : '  + ex); 
        }
         
        $("msg").focus();
    }
  
    // Utilities
    function $(id)
    { 
        return document.getElementById(id); 
    }
     
    function log(msg)
    { 
        $('log').innerHTML += '<br />' + msg; 
        $('log').scrollTop = $('log').scrollHeight;
    }

    function score(msg)
    { 
        $('score').innerHTML =  msg; 
        $('score').scrollTop = $('score').scrollHeight;
    }


     
/***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************
***************************************************************************************************************************************************************************************/

function placeSweets(stringArrayOfSweets) {

	var arrayOfSweets = stringArrayOfSweets.split(";");

	var tds = document.querySelectorAll("#gameTable td");

	for (i=1 ; i<arrayOfSweets.length ; i++) {


		var sweetsCoordinates = parseInt(arrayOfSweets[i]);
		var td = tds[sweetsCoordinates];
		td.innerHTML = '0';
		td.setAttribute('style', 'color:blue');
		td.setAttribute('class', 'sweet');


	}

}

function placeUser(stringArrayUser) {

	var arrayUser = stringArrayUser.split(";");

	user = arrayUser[3];

	var tds = document.querySelectorAll("#gameTable td");

	var usertd = tds [parseInt(arrayUser[1])];

	usertd.innerHTML = '@';
	usertd.setAttribute('class', 'user');
	usertd.setAttribute('userID', 1);
	usertd.setAttribute('style', ('color:'+arrayUser[2]));

}

function supprSweet (stringArraySupprSweet) {

	var arraySupprSweet = stringArraySupprSweet.split(";");

	var tds = document.querySelectorAll("#gameTable td");

	var sweetToSuppr = tds[parseInt(arraySupprSweet[1])];

	if (sweetToSuppr.getAttribute('class')!= 'user') {
		sweetToSuppr.innerHTML = '.';
		sweetToSuppr.setAttribute('style', "width:50px");
		sweetToSuppr.setAttribute('class', 'nothing');
	} 

	score(arraySupprSweet[2]);
}

function moveUser (stringArrayMoveUser) {

	var arrayMoveUser = stringArrayMoveUser.split(";");

	var us =  document.querySelector("td[class = user]");


	var tds = document.querySelectorAll("#gameTable td");
	var nextCell = tds[parseInt(arrayMoveUser[3])];

	// test : is oldlocation ours ?
	// test : is newlocation a location of another user ?

	var weAreMoving = (parseInt(arrayMoveUser[2]) == parseInt(us.id) );
	
	var someOneIsOnNextCell = ((nextCell.getAttribute('class') == 'other') || (nextCell.getAttribute('class') == 'user'));
	

	if (!someOneIsOnNextCell) {
		if (weAreMoving) {

			//log('nextCell.class '+nextCell.getAttribute('class')+' / '+someOneIsOnNextCell);

			var exUserCellStyle = us.getAttribute('style');

			us.innerHTML = '.';
			us.setAttribute('style', nextCell.getAttribute('style'));
			us.setAttribute('class', 'nothing');


			nextCell.innerHTML = '@';
			nextCell.setAttribute('style', exUserCellStyle);
			nextCell.setAttribute('class', 'user');

		} else {
			var otherUser = tds[parseInt(arrayMoveUser[2])]; 
		
			var exUserCellStyle = 'color:'+arrayMoveUser[4];

			otherUser.innerHTML = '.';
			otherUser.setAttribute('style', nextCell.getAttribute('style'));
			otherUser.setAttribute('class', 'nothing');


			nextCell.innerHTML = '@';
			nextCell.setAttribute('style', exUserCellStyle);
			nextCell.setAttribute('class', 'other');
		}
	} else {log('someOneIsOnNextCell '+someOneIsOnNextCell);}


	// 0 : mu
	// 1 : id of the user
	// 2 : old localization of the user
	// 3 : new localization
	// 4 : color of the moving user
}






