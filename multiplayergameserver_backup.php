<?php

// exec : C:\wamp\bin\php\php5.5.12\php instead of php
// cd C:\wamp\www\PM\code\SR_PM_DETRE_Corentin

require_once('websockets/websockets.php');
require_once('interface_multiplayergame.php');

class multiplayer_backup extends interface_multiplayer {

		protected function process($user, $message) {
			
			parent::process($user,$message);

			//recoUser;color
			if(explode(";",$message)[0] == "recoUser") {
				$color = explode(";",$message)[1];

				deleteUser($user->id);

				global $link;
				$mysqli_result = mysqli_query($link,"UPDATE user_infos SET id='".$user->id."' WHERE color='".$color."';");
				echo "Update : ".$user->id." from color ".$color."\n";
			}



		}

		protected function connected ($user) {

			global $isMain;
			if(!$isMain) {
				$isMain =true;
			}
			if(isGameEnded()) {
				exit;
			}

			// We add each new user to an array in order to ba able to broadcast messages later, 
			// for instance : new localisation of user after they move.
			global $arrayOfUsers;
			$arrayOfUsers[] = $user;

			// We always insert the user (wether he is new or reconnecting) and we process the difference later
			global $link;
			$mysqli_result = mysqli_query($link,"INSERT INTO user_infos (id, sweets_eaten) VALUES ('".$user->id."', 0)");
			
								
  	}

}

/**************************************************************************************************************************************************************************************
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
$port = '9010';
 
$server = new multiplayer_backup($host , $port );

$size = 15;
$numberOfSweets = 15;

$isMain = false;
$userIdArray = [];

try {
	//Connection to the database for the server
	$link = connectToDatabase();
	
	//Getting the sweets coordinates from DB
	$arrayOfSweets = getSweetsFromDB();;
	//Creation of an Array of User used for the broadcast
	$arrayOfUsers;
	
	initUsersForBackupServer();
  	$server->run();
  	
}
catch (Exception $e) {
  	$server->stdout($e->getMessage());
	disconnectFromDatabase($link);
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

function getSweetsFromDB() {
	global $link;

	$mysqli_result = mysqli_query($link,"SELECT id, coordinates FROM sweets_infos ");

	$result = [];

	if (mysqli_num_rows($mysqli_result) > 0) {
    	// output data of each row
    	while($row = mysqli_fetch_assoc($mysqli_result)) {
        	$result[$row["id"]] = $row["coordinates"];
    	}
	}

	return $result;
}

function initUsersForBackupServer() {
	global $userIdArray;

	global $link;

	$mysqli_result = mysqli_query($link,"SELECT id FROM user_infos ");

	if (mysqli_num_rows($mysqli_result) > 0) {
    	// output data of each row
    	while($row = mysqli_fetch_assoc($mysqli_result)) {
        	$userIdArray[] = $row["id"];
    	}
	}
}

function isGameEnded() {
	global $link;

	$mysqli_result = mysqli_query($link,"SELECT COUNT(*) AS AreThereSweetsLeft FROM sweets_infos ")->fetch_assoc();

	return ($mysqli_result["AreThereSweetsLeft"]==0);


}

?>

