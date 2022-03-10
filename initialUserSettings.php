<?php
//initialUserSettings.php
//Purpose: This function will be called on load from index.html. It will check if
//the user exisits in the kaUserProfileSettings database. If a user does not exisit
//it will create a new entry in the DB and set the default to SD. If the user
//does exisit it will query the users settings and show them as checked off in the
//list of profiles.
//Author: Evie Vanderveer


require_once('class.php');
require_once('infraProfiles.php');
$username = $_SERVER['REMOTE_USER'];
$profiles = $GLOBALS['profiles'];
$numberOfProfiles = $GLOBALS['numberOfProfiles'];


//Set up initial SQL Query for infra profile settings
	$sqlQuery = "SELECT Username,";
	$i = 0;
	foreach($profiles as $profile)
	{
		$sqlQuery .= " " . $profile;
		$i++;
		if($i < $numberOfProfiles)
		{
			$sqlQuery .= ", ";
		}
	}
	$sqlQuery .= " FROM kaUserProfileSettings WHERE username = '" . $username . "'"; 

	
//Query DB for profile records
	try {
		$myDB = new DatabaseConnection("../kadb_userinfo.ini");
	} catch(Exception $e) {
		$myDE->setError($e->getMessage());
	}

	if (isset($myDB)) {
		$counter = 0; 
		$result = $myDB->connection->query($sqlQuery);
		$myData = $result->fetchall();
		$myDB = NULL;
	}
	
		$i = 0;
		
//If no profile exists, create a new DB entry that defaults to all profiles
	if($myData == null) 
	{
		$newProfileSql = "INSERT INTO kaUserProfileSettings VALUES ('" . $username . "',";
		
		//Set sql query to have every profile selected for a new user
		foreach($profiles as $profile) 
		{
			$newProfileSql .= " 'True'";
			$i++;
			if($i < $numberOfProfiles)
			{
				$newProfileSql .= ",";
			}
		}
		$newProfileSql .= ");";
		

		
		//Insert new profile into database
		try {
			$myDB = new DatabaseConnection("../kadb_userinfo.ini");
		} catch(Exception $e) {
			$myDE->setError($e->getMessage());
		}

		if (isset($myDB)) {
			$counter = 0; 
			$result = $myDB->connection->query($newProfileSql);
			$myDB = NULL;
		}
		
		//Query databse for new user info
		try {
			$myDB = new DatabaseConnection("../kadb_userinfo.ini");
		} catch(Exception $e) {
			$myDE->setError($e->getMessage());
		}

		if (isset($myDB)) {
			$counter = 0; 
			$result = $myDB->connection->query($sqlQuery);
			$myData = $result->fetchall();
			$myDB = NULL;
		}	
	}
	
//Set up HTML variables to populate Profile Settings "infraProfileSettings" div for index.html
	$htmlbeg =  '<div class="col-md-3"><div class="checkbox-inline"><h5><input value="';
	$htmlmid1 =  '" name="searchProfile" type="checkbox"';
	$htmlchecked = ' checked="checked"';
	$htmlmid2 =  '/>';
	$htmlend =   '</h5></div></div>';

	$usersActualProfile = array();

//Loop through user settings from DB and available profiles and assign all true profiles to an array of $userSetting
	foreach ($myData as $userSetting)
	{
		foreach($profiles as $profile) 
		{
		($userSetting[$profile] == "True" ? array_push($usersActualProfile, $profile) : "");
		}
	}	

	

//Loop through known infra profiles and populate html from this data.

		foreach ($profiles as $profile)
		{
			$tempProfile = substr ($profile, 2);
			if(in_array($profile, $usersActualProfile))
			{
				echo $htmlbeg . $profile . $htmlmid1 . $htmlchecked . $htmlmid2 . $tempProfile . $htmlend;
			} else
			{
				echo $htmlbeg . $profile . $htmlmid1 . $htmlmid2 . $tempProfile . $htmlend;
			}
		}
	 
	

?>