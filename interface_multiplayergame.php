<?php

// exec : C:\wamp\bin\php\php5.5.12\php instead of php
// cd C:\wamp\www\PM\code\SR_PM_DETRE_Corentin

require_once('websockets/websockets.php');

abstract class interface_multiplayer extends WebSocketServer {

	protected function process($user, $message) {
		//$this->send($user,$message);

		if($message == "initGame") {
			$this->initGame($user);

			global $link;
			$mysqli_result = mysqli_query($link,"SELECT coordinates FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();
			$locationNewUser = $mysqli_result["coordinates"];

			$messageUserIsMoving = userIsMoving($user,$locationNewUser);
			$this->sendToAll("mu;".$messageUserIsMoving);
		}

		if(explode(";",$message)[0] == "moveUser") {
			$messageExploded = explode(";",$message);
			$newLocation = intval($messageExploded[1]);

			if ( (isThereASweet($newLocation)) ) {
				userEatsSweet($user,$newLocation);
				$messageUserEatsSweet = $newLocation.";".whoWin();
				$this->sendToAll("ss;".$messageUserEatsSweet);

				global $arrayOfSweets;
				if (count($arrayOfSweets) < 1 ) {
					$this->endGame();
				}	
			}

			if (!isThereAUser($newLocation)) {
				$messageUserIsMoving = userIsMoving($user,$newLocation);
				$this->sendToAll("mu;".$messageUserIsMoving);
			}
		}
		
	}

 
  	protected function closed ($user) {

  		global $arrayOfColors;
  		global $link;

		$color = getColorUser($user->id);
  		$arrayOfColors[] = $color;

  		$mysqli_result = mysqli_query($link,"INSERT INTO `available_colors`(`color`) VALUES  ('".$color."')");
  		$mysqli_result = mysqli_query($link,"SELECT coordinates FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();
		$oldLocation = $mysqli_result["coordinates"];

  		deleteUser($user->id);

		// We delete the user for the broadcasting Array.
  		global $arrayOfUsers;
  		if (($key = array_search($user, $arrayOfUsers)) !== false) {
   			unset($arrayOfUsers[$key]);
		}

		$this->sendToAll("su;".$oldLocation);

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

			$mysqli_result = mysqli_query($link,"UPDATE user_infos SET color='".$arrayNewUser[1]."' WHERE id='".$user->id."';");

			$this->send($user, 'Hello '.$user->id.', you\'re now connected !');
			$this->sendToAll("<strong style='color:".$arrayNewUser[1]."'>User ".$arrayNewUser[1]." has entered the ARENA OF DEATH </strong>");

			return $arrayNewUser;
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

$arrayOfColors = ['red','green','orange','yellow'];


function placeSweets($size, $numberOfCoordinates) {

	$arrayOfRandoms = randomCoordinates($size, $numberOfCoordinates) ;
	global $link;

	for ($i = 0; $i<$size; $i++) {

		$coordinates = $arrayOfRandoms[$i];
		
		$mysqli_result = mysqli_query($link,"INSERT INTO sweets_infos (id, coordinates) VALUES (".$i.",".$coordinates.")");
		
    }


    return $arrayOfRandoms;
}

function randomCoordinates($size, $number) {
	$result = [];
	
	$left_top_corner = 0;
	$right_top_corner = $size -1;
	$left_bottom_corner = $size * ($size -1);
	$right_bottom_corner = $size * $size -1;

	for ($i=0 ; $i< $number ; ++$i) {

		$randomNumber = rand(1, ($size * $size -2));
		// Got rid of left_top_corner and right_bottom_corner

		while ($randomNumber == $right_top_corner 
			OR $randomNumber == $left_bottom_corner 
			OR in_array($randomNumber, $result)) {

			$randomNumber = rand(1, ($size * $size -2));
		}

		$result[] = $randomNumber;

	}

	return $result;
}

function getLocalizationOfNewUser() {

	
	global $size;
	global $arrayOfColors;

	$color = getNextColor();

	switch ($color) {
		case "red":
		return [0,$color];

		case "green":
		return [$size-1,$color];

		case "orange":
		return [($size * ($size -1)),$color];

		case "yellow":
		return [$size * $size -1,$color];

		default:
		return 0;
	}

}


function getNextColor() {

	global $arrayOfColors;
	global $link;

	$mysqli_result = mysqli_query($link,"SELECT * FROM available_colors LIMIT 1")->fetch_assoc();

	$color = $mysqli_result["color"];	

	$mysqli_result = mysqli_query($link,"DELETE FROM available_colors WHERE color='".$color."'");

	array_shift($arrayOfColors);

	return $color;
}

function getColorUser($id) {
	global $link;

	$mysqli_result = mysqli_query($link,"SELECT color FROM user_infos WHERE id='".$id."'")->fetch_assoc();
	
	return $mysqli_result["color"];
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
	$mysqli_result = mysqli_query($link,"SELECT `color` AS c FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();
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

	$mysqli_result = mysqli_query($link,"SELECT sweets_eaten,color FROM user_infos WHERE id='".$user->id."'")->fetch_assoc();

	return ";".$user->id.";".$mysqli_result["color"].";".$mysqli_result["sweets_eaten"];

}

function whoWin() {

	global $arrayOfUsers;

	$userMax;
	$max = 0;

	foreach ($arrayOfUsers as $user) {
		$score = explode(";",getScore($user))[3];
		if ($max <= $score) {
			$max = $score;
			$userMax = $user;
		}
	}
	$scoreUserMax = explode(";",getScore($userMax));
	return "<p style='color:".$scoreUserMax[2]."'>". $scoreUserMax[2]." is winning with ".$max." sweets eaten ! </p>";
}


?>