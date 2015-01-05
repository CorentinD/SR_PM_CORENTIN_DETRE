<?php

// exec : C:\wamp\bin\php\php5.5.12\php instead of php
// cd C:\wamp\www\PM\code\SR_PM_DETRE_Corentin

require_once('websockets/websockets.php');
require_once('interface_multiplayergame.php');

class multiplayer extends interface_multiplayer {

		protected function connected ($user) {
		

		global $link;
		$mysqli_result = mysqli_query($link,"INSERT INTO user_infos (id, sweets_eaten) VALUES ('".$user->id."', 0)");
		// We add each new user to an array in order to ba able to broadcast messages later, 
		// for instance : new localisation of user after they move.
		global $arrayOfUsers;
		$arrayOfUsers[] = $user;

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
	$mysqli_result = mysqli_query($link,"TRUNCATE TABLE `game_infos`");
	$mysqli_result = mysqli_query($link,"TRUNCATE TABLE `available_colors`");
	//Creation of the new sweets coordinates
	$arrayOfSweets = placeSweets($size, $numberOfSweets);
	initInfosGame($size,$numberOfSweets,$arrayOfColors);
	//Creation of an Array of User used for the broadcast
	$arrayOfUsers;
	
  	$server->run();
  	
}
catch (Exception $e) {
  	$server->stdout($e->getMessage());
	disconnectFromDatabase($link);
}

function initInfosGame($size,$numberOfSweets,$arrayOfColors) {
	global $link;

	$mysqli_result = mysqli_query($link,"INSERT INTO game_infos (size, numberofsweets) VALUES (".$size.",".$numberOfSweets.");");

	foreach ($arrayOfColors as $color) {
		$mysqli_result = mysqli_query($link,"INSERT INTO available_colors (color) VALUES ('".$color."')");	
	}
	
}

?>