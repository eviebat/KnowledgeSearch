<?php 
//userSettings.php
//Purpose: This file contains the function getUserSettings() which will
//query the infra profile settings DB to pull the users saved settings.
//It will send this information back as an array.
//Authors: Evie Vanderveer


function getUserSettings () 
{

	$username = $_SERVER['REMOTE_USER'];
	$profiles = $GLOBALS["profiles"];
	$numberOfProfiles = $GLOBALS["numberOfProfiles"];

	
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

//Query kaUserProfileSettings DB Table for users infra profile settings
	try 
	{
		$myDB = new DatabaseConnection("../kadb_userinfo.ini");
	} catch(Exception $e) 
	{
		$myDE->setError($e->getMessage());
	}

	if (isset($myDB)) 
	{
		$counter = 0; 
		$result = $myDB->connection->query($sqlQuery);
		$myData = $result->fetchall();
		$myDB = NULL;
	}


	$sqlProfile = "";																			//SQL Profile Statement to be added to full sql query
	$usersActualProfile = array();

//Loop through profiles and user profiles and assign all true profiles to an array of $userSetting
	foreach ($myData as $userSetting)
	{
		foreach($profiles as $profile) 
		{
		($userSetting[$profile] == "True" ? array_push($usersActualProfile, $profile) : "");
		}
	}
	return $usersActualProfile;
	
}




?>