<?php

// exec : C:\wamp\bin\php\php5.5.12\php instead of php
// cd C:\wamp\www\PM\code\SR_PM_DETRE_Corentin

require_once('websockets/websockets.php');

class multiplayer extends WebSocketServer {

	protected function process($user, $message) {
		//$this->send($user,$message);

		if($message == "initGame") {

			$this->initGame($user);
		}

		if(explode(";",$message)[0] == "moveUser") {
			$messageExploded = explode(";",$message);
			$newLocation = intval($messageExploded[1]);

			if ( (isThereASweet($newLocation)) ) {
				userEatsSweet($user,$newLocation);
				$messageUserEatsSweet = $newLocation.";".whoWin();
				$this->sendToAll("ss;".$messageUserEatsSweet);

				global $arrayOfSweets;
				if (count($arrayOfSweets) <= 1 ) {
					$this->endGame();
				}	
			}

			if (!isThereAUser($newLocation)) {
				$messageUserIsMoving = userIsMoving($user,$newLocation);
				$this->sendToAll("mu;".$messageUserIsMoving);
			}
		}
		
	}

	protected function connected ($user) {
		

		global $link;
		$mysqli_result = mysqli_query($link,"INSERT INTO user_infos (id, sweets_eaten) VALUES ('".$user->id."', 0)");
		$this->send($user, 'Hello '.$user->id.', you\'re now connected !');


		// We add each new user to an array in order to ba able to broadcast messages later, 
		// for instance : new localisation of user after they move.
		global $arrayOfUsers;
		$arrayOfUsers[] = $user;

		$this->sendToAll("User ".$user->id." has entered the ARENA OF DEATH");
		
  	}
  
  	protected function closed ($user) {

  		deleteUser($user->id);

		// We delete the user for the broadcasting Array.
  		global $arrayOfUsers;
  		if (($key = array_search($user, $arrayOfUsers)) !== false) {
   			unset($arrayOfUsers[$key]);
		}

  	}

  	protected function initGame($user) {

		// The user requests the array of sweets for the initialization
			global $arrayOfSweets;
			$stringArrayOfSweets = implode(";",$arrayOfSweets);
			$this->send($user, "is;".$stringArrayOfSweets);

		// The user requests his localization for the initialization

			$arrayNewUser = getLocalizationOfNewUser();
			$arrayNewUser[] = $user->id;
			$this->send($user, "iu;".implode(";",$arrayNewUser));

		// We can update the DB with the coordinates and color of the new user
			global $link;
			$mysqli_result = mysqli_query($link,"UPDATE user_infos SET coordinates=".$arrayNewUser[0]." WHERE id='".$user->id."';");

			$mysqli_result = mysqli_query($link,"UPDATE user_infos SET ` color`='".$arrayNewUser[1]."' WHERE id='".$user->id."';");


	}

	protected function sendToAll($msg) {

		global $arrayOfUsers;

		foreach ($arrayOfUsers as $user) {
			$this->send($user,$msg);
		}
	}

	protected function endGame() {

		$user = whoWin();

		$this->sendToAll($user."GG everybody ! It's the end ! Bye !");

		exit;

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

// Initialization of the wersocket server

$host = '0.0.0.0';
$port = '9000';
 
$server = new multiplayer($host , $port );

$size = 15;
$numberOfSweets = 15;

try {
	//Connection to the database for the server
	$link = connectToDatabase();
	//Cleaning of the table with the coordinates of the sweets
	$mysqli_result = mysqli_query($link,"TRUNCATE TABLE `sweets_infos`");
	$mysqli_result = mysqli_query($link,"TRUNCATE TABLE `user_infos`");
	//Creation of the new sweets coordinates
	$arrayOfSweets = placeSweets($size, $numberOfSweets);
	//Creation of an Array of User used for the broadcast
	$arrayOfUsers;
	
  	$server->run();
  	
}
catch (Exception $e) {
  	$server->stdout($e->getMessage());
	disconnectFromDatabase($link);
}

function connectToDatabase() {
	$mysqli = new mysqli("localhost", "root", "", "pm_multi");
	if ($mysqli->connect_errno) {
    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	echo $mysqli->host_info . "\n";
	return $mysqli;
}

function disconnectFromDatabase($mysqli) {
	
	echo 'connection to database closed';
}

function deleteUser($id_user) {
	global $link;
	$sql = "DELETE FROM user_infos WHERE id='".$id_user."'";

	if (mysqli_query($link, $sql)) {
    	echo "Record deleted successfully \n";
	} else {
   		echo "Error deleting record: " . mysqli_error($link);
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

// Initialization functions


function placeSweets($size, $numberOfCoordinates) {

	$arrayOfRandoms = UniqueRandomNumbersWithinRange(0, ($size * $size -1), $numberOfCoordinates) ;
	global $link;

	for ($i = 0; $i<$size; $i++) {

		$coordinates = $arrayOfRandoms[$i];

		
		$mysqli_result = mysqli_query($link,"INSERT INTO sweets_infos (id, coordinates) VALUES (".$i.",".$coordinates.")");
		
    }


    return $arrayOfRandoms;
}

function UniqueRandomNumbersWithinRange($min, $max, $quantity) {
    $numbers = range($min, $max);
    shuffle($numbers);
    return array_slice($numbers, 0, $quantity);
}

function getLocalizationOfNewUser() {

	global $link;

	$mysqli_result = mysqli_query($link,"SELECT COUNT(*) AS NumberOfUsers FROM user_infos")->fetch_assoc();

	switch ($mysqli_result["NumberOfUsers"]) {
		case "1":
		return [0,'red'];

		case "2":
		return [14,'green'];

		case "3":
		return [210,'orange'];

		case "4":
		return [224,'yellow'];

		default:
		return 0;
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

// Answers to the movement of a user

function isThereASweet($newLocation) {
	global $link;

	$mysqli_result = mysqli_query($link,"SELECT COUNT(*) AS IsThereASweet FROM sweets_infos WHERE coordinates=".$newLocation)->fetch_assoc();

	return $mysqli_result["IsThereASweet"];
}

function userEatsSweet($user, $newLocation) {

	global $link;

	// First we update the count of sweets eaten by the user

	$mysqli_result = mysqli_query($link,"SELECT sweets_eaten FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();
	$old_sweets_eaten = $mysqli_result["sweets_eaten"];
	$mysqli_result = mysqli_query($link,"UPDATE user_infos SET sweets_eaten=".($old_sweets_eaten+1)." WHERE id='".$user->id."'");

	// Then we update both the table and the array with the sweets infos 
	// The information is duplicated because both the supports have differente roles : 
	// The DB info is needed for the persistence
	// The array is needed for the reactivity.

	$mysqli_result = mysqli_query($link,"DELETE FROM sweets_infos WHERE coordinates=".$newLocation);

	global $arrayOfSweets;
  	if (($key = array_search($newLocation, $arrayOfSweets)) !== false) {
   			unset($arrayOfSweets[$key]);
	}


}

function isThereAUser($newLocation) {
	global $link;

	$mysqli_result = mysqli_query($link,"SELECT COUNT(*) AS IsThereAUser FROM user_infos WHERE coordinates=".$newLocation)->fetch_assoc();

	return $mysqli_result["IsThereAUser"];
}

function userIsMoving($user,$newLocation) {
	global $link;

	$mysqli_result = mysqli_query($link,"SELECT coordinates FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();
	$oldLocation = $mysqli_result["coordinates"];

	$mysqli_result = mysqli_query($link,"UPDATE user_infos SET coordinates=".$newLocation." WHERE id='".$user->id."'");
	$mysqli_result = mysqli_query($link,"SELECT ` color` AS c FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();
	$colorOfUser = $mysqli_result["c"];

	// 1 : id of the user
	// 2 : old localization of the user
	// 3 : new localization
	// 4 : color of the moving user
	$messageUserIsMoving = $user->id.";".$oldLocation.";".$newLocation.";".$colorOfUser;

	return $messageUserIsMoving;
}

function getScore($user) {

	global $link;

	$mysqli_result = mysqli_query($link,"SELECT sweets_eaten FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();

	return ";".$user->id.";".$mysqli_result["sweets_eaten"];

}

function whoWin() {

	global $arrayOfUsers;

	$userMax;
	$max = 0;

	foreach ($arrayOfUsers as $user) {
		$score = explode(";",getScore($user))[2];
		if ($max <= $score) {
			$max = $score;
			$userMax = $user;
		}
	}
	return "<p style='color:red'>".$userMax->id." is winning with ".$max." sweets eaten ! </p>";
}


?>