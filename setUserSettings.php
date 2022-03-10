<?php 

	require_once('class.php');
	require_once('infraProfiles.php');
	$myDE = new DataExchange();
	$data = $myDE->convertRequestDataToObject();
	$userNewProfiles = $data->searchProfile;
	$profiles = $GLOBALS["profiles"];


	$username = $_SERVER['REMOTE_USER'];	
	$updateSqlProfile = "Update dbo.kaUserProfileSettings SET ";						//Start UPDATE SQL query
	$numberNewProfiles = count($userNewProfiles);

	$numberOfProfiles = $GLOBALS["numberOfProfiles"];
	$i = 0;
	
	if($numberNewProfiles == 0) 
	{
		return;
	} elseif ($numberNewProfiles == 1)
	{
		$userNewProfiles = array($userNewProfiles);
	}
	

	
	//For each infra profile check if it is in the users checked flags and set SQL query accordingly
	 foreach ($profiles as $profile)
	 {
		 if (in_array($profile, $userNewProfiles))
		 {
			 $updateSqlProfile .= $profile . " = 'True'";
			 $i++;
		 }else
		 {
			 $updateSqlProfile .= $profile . " = 'False'";
			 $i++;
		 }
		 if($i < $numberOfProfiles)														//As long as there are more profiles, add a comma to sql statement
		 {
			$updateSqlProfile .= ", ";
		 }
	 }
	
	$i = 0;
	$updateSqlProfile .= " OUTPUT ";
	foreach( $profiles as $profile)
	{
		$updateSqlProfile .= " inserted." . $profile;
		$i++;
		if($i < $numberOfProfiles)
		{
			$updateSqlProfile .= ", ";
		}
		
	}
	
	$updateSqlProfile .= " WHERE Username = '" . $username . "'";

	try {
		$myDB = new DatabaseConnection("../kadb_userinfo.ini");
	} catch(Exception $e) {
		$myDE->setError($e->getMessage());
	}

	if (isset($myDB)) {
		$counter = 0; 
		$result = $myDB->connection->query($updateSqlProfile);
		$myData = $result->fetchall();
		$myDB = NULL;
	}

	

	//Parse OUTPUT to return success message
	$output = "";
	
	//Loop through user settings from DB and available profiles and assign all true profiles to an array of $userSetting
	foreach ($myData as $userSetting)
	{
		foreach($profiles as $profile) 
		{
		($userSetting[$profile] == "True" ? $output .= substr($profile,2). " " : "");
		}
	}
	
	echo $myDE->respond($output);
	
	
?>