<?php
//kasearchmain.php
//Purpose: This is designed for the new KA search tool. It will pull in a query value
//as well as search options from index.html. After pulling in user settings from userSettings.php
//it cleans the search query, checking for special values. According to the profiles selected and regions 
//selected It will set the sql statements as needed for the users settings.
//Authors: Evie Vanderveer

//Include function files
	require_once('class.php');
	require_once('userSettings.php');
	require_once('infraProfiles.php');
	require_once('searchConfig.php');
	
	
//Gather POST data from HTML	
	$myDE = new DataExchange();
	$data = $myDE->convertRequestDataToObject();
	$queryValue = $data->kaSearchValue;
	$searchType = $data->searchType;
	$selectedRegions = $data ->region;
		

//********************
//Establish Variables
//********************

	$regionalPrefixBool = False;										//Boolean for if a regional prefix was checked
	$isNumericBool = False;  											//Search is a numeric KA number only 
	$tmpQV = array();													//Temporary query value holder
	$searchArray = array();												//Array of each word to be searched
	$sqlRegionPrefix = "";												//SQL query for regional prefixes
	$tmpsqlTitle = "";													//SQL query for title - will be added to $sqlQuery
	$tmpSqlAbs = "";													//SQL query for abstract - will be added to $sqlQuery
	$tmpSqlFull = "";		
	$usersActualProfile = array();
	$sqlProfile = "";
	
//********************
//End establish variables
//*********************

//******************
//Define Infra DB Connection
//******************

function infraConnection($sqlQuery) {
	try 
	{
		$myDB = new DatabaseConnection("../kasdgo_infraDR.ini");
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
	return $myData;
}


//******************
//END Infra DB
//******************





//********************
//SETUP INFRA PROFILE
//********************
//If a new profile is being added to infra, please update the array in infraProfiles.php
	
	$usersActualProfile = getUserSettings();
	$i = 0;
	$totalProfileCount = count($usersActualProfile);
	
//Check if and what SD profiles are selected
	$globalSD = in_array("kaServiceDeskPitt",$usersActualProfile);
	$capgeminiSD = in_array("kaServiceDeskCapgemini",$usersActualProfile);
	$tibaSD = in_array("kaServiceDeskTiba",$usersActualProfile);


//Loop through users true profiles from DB and assign a SQL query for each profile
	 foreach ($usersActualProfile as $userSetting)
	 {
		 foreach($profiles as $profile)
		 {
			if ($i == 0) 
			{
				if ($userSetting == $profile)
				{
					$i++;
					$sqlProfile .= " AND (SUBSTRING(k.Profile_Name,1,2) = '" . $profile[2] . $profile[3] . "' ";
				}
			} 
			else {
				if ($userSetting == $profile)
				{
					$i++;
					$sqlProfile .= " OR SUBSTRING(k.Profile_Name,1,2) = '" . $profile[2] . $profile[3] . "' ";
				}
			}
		 } 
	 }
	
	$sqlProfile = $sqlProfile . ")";
	
//***********************
//END SETUP INFRA PROFILE
//***********************






//******************************************
//SQL - Primary Query 
//******************************************	
	$sqlBeginningStr = "SELECT TOP 150 K.KNOWLEDGE_REF, A.CI_Asset_Ref SERVICE, K.TITLE, K.Profile_Name, K.Abstract, K.HITS_PORTAL FROM  RV_CONFIG_ITEM A INNER JOIN (RV_KNOWLEDGE K INNER JOIN  KB_ASSET_LINK KAL with (nolock) ON  K.KNOWLEDGE_REF = KAL.KNOWLEDGE_REF) ON  A.CI_Item_Ref =  KAL.ITEM_REF WHERE ";
	$sqlBeginningStr .= "A.ENTITY_REF = 2 AND KAL.STATUS='A' AND K.Status='active' AND K.Entry_Status_Ref <> 0 ";
	$sqlBeginningStr .= $sqlProfile;

//******************************************
//END SQL - Primary Query 
//******************************************

//********************
//CHECK DATA
//*******************

//Check to make sure search value is set and all characters that are not alphanumeric - EV
//Added \- \' to the regex" - CRS
	 if(isset($queryValue))
		 {
			 $queryValue = preg_replace('/[^a-zA-Z0-9.;:~ñ!%:\*_\/\s\-\']/', '', strip_tags($queryValue));
			 //Note that this regex is negative, so it will remove characters that are NOT in the list.
			 //EX: self-service will not be matched as \- is in the expression. self-service@ will get the @ removed
			 //file_put_contents('C:\inetpub\logs\colin.txt', print_r(strip_tags($queryValue), true));
			 
			 $queryValue= str_replace("'", "''", $queryValue); //Escape apostrophes so they can still be searched
		 }


//******************
//END CHECK DATA
//******************


//*********************
//SEARCH BY NUMBER
//******************
//If search value is numeric, convert number to a string and place in search array
		
	if(is_numeric(trim($queryValue)))
		{
			$isNumericBool = True;
			settype($queryValue, "string");
			array_push($searchArray, $queryValue);
			$sqlQuery = $sqlBeginningStr . " AND (K.Knowledge_Ref='" . $searchArray[0] . "')";
		
		}
		
//*********************
//END SEARCH BY NUMBER
//********************


//*******************
//PREPARE SEARCH Query
//*******************

//Handle only if query is non-numeric
 	if ($isNumericBool == False)
		{
			$tmpQV = explode (" ", $queryValue);					//Turns string into an array of strings delimited by space
			foreach($tmpQV as $key=>$word)
				{
					array_push($searchArray, $word);				//Pushes each term into a search array		
				}				
		} 

//*******************
//END PREPARE SEARCH Query
//*******************

//***********************
//HANDLE REGIONAL PREFIXES
//************************
	
	$i =0;
	if(!empty($selectedRegions))
	{
		$regionalPrefixBool = "true";								//Set regional prefixes flag to true
		$numberOfRegions = count($selectedRegions);
	} else
	{
		$numberOfRegions = 0;
		$regionalPrefixBool ="false";
	}
	
	if($numberOfRegions == 1)										//If only one region is selected, create an array of one region
	{
		$selectedRegions = array($selectedRegions);
	}
	
 	
	//If Query has a regional prefix and is in the Global service desk infra profile , update SQL Query beginning to include prefix in search 
 		if ($regionalPrefixBool == "true" and $globalSD == "1") 
		{
			// Query has a regional prefix, update SQL Query beginning to include prefix in search 
			foreach($selectedRegions as $userRegion)
			{
				 if($i == 0)
				 {
					 $sqlRegionPrefix = " K.Title Like '%" . $userRegion . "%'";
					 $i++;
				 } else{
					 $sqlRegionPrefix .= " OR K.Title Like '%" . $userRegion . "%'";
					 $i++;
				 }
			}
				$sqlRegionPrefix .= " OR K.Title Like '%gbl%'";
				$sqlBeginningStr .= " AND (" . $sqlRegionPrefix . ")"; 
		} 
		
		
	//If Query has a regional prefix and is in the Capgemini service desk infra profile , update SQL Query beginning to include prefix in search 
		if ($capgeminiSD == "1" and $globalSD == "0" and $regionalPrefixBool == "false" and $tibaSD == "0")
		{
				$sqlRegionPrefix .= " K.Title Like '%gbl%' OR K.Title Like '%eur%'";
				$sqlBeginningStr .= " AND (" . $sqlRegionPrefix . ")"; 
		} elseif ($capgeminiSD == "1" and $globalSD == "0" and $regionalPrefixBool == "true" and $tibaSD == "0")
		{
			if(in_array("all", $selectedRegions))
			{
			//add nothing to regional prefix
			} else {
				$sqlRegionPrefix .= " K.Title Like '%eur sh%'";
				$sqlBeginningStr .= " AND (" . $sqlRegionPrefix . ")"; 
			}
		}		
		
		
	//If Query has a regional prefix and is in the Tiba service desk infra profile , update SQL Query beginning to include prefix in search 
		 if ($tibaSD == "1" and $globalSD == "0" and $regionalPrefixBool == "false" and $capgeminiSD == "0")
		{
				$sqlRegionPrefix .= " K.Title Like '%gbl%' OR K.Title Like '%nac%' OR K.Title Like '%pcm%' OR K.Title Like '%mx%'";
				$sqlBeginningStr .= " AND (" . $sqlRegionPrefix . ")"; 
		} 
		 
		
		

		//If Query has a prefix for RIT and has a RIT support option "region" selected - add to SQL Query beginning to include prefix in search
			 
 	if(in_array("kaRIT",$usersActualProfile) and $regionalPrefixBool == "true" and $globalSD == "0")
		{
			//If tier 1 "region" is selected, remove "tier1" from array and add "Core" "Assisted" and "No Match"
			if(in_array("tier1", $selectedRegions))
			{
				array_shift($selectedRegions);
				array_push($selectedRegions,"Core", "Assisted", "No Match");
			}

			//$sqlRegionPrefix .= "k.Profile_Name = 'PAF/Assisted' OR k.Profile_Name = 'PAF/Core' OR k.Profile_Name = 'PAF/No Match' ";
				 
			foreach($selectedRegions as $userRegion)
			{
				 if($i == 0)
				 {
					 $sqlRegionPrefix .=  "(SUBSTRING(k.Profile_Name,5,2) = '" . $userRegion[0] . $userRegion[1] . "')"; 
					 $i++;
				 } else{
					 $sqlRegionPrefix .=  " OR (SUBSTRING(k.Profile_Name,5,2) = '" . $userRegion[0] . $userRegion[1] . "')";
					 $i++;
				 }
			}
			
			$sqlBeginningStr .= " AND (" . $sqlRegionPrefix . ")"; 
			
		} 
		 

	
//***********************
//END HANDLE REGIONAL PREFIXES
//************************

//***********************
//ATTEMPT NUMERIC SEARCH
//***********************

//Now that query is complete with region prefixes and such, executing query.
//If query returns results, end the script
//If not, continue to run regular search
//This allows us to search, for example, "7275"
//This is also very messy and makes me sad, it works for now but I will make these into methods later.
//If that never happens, please forgive me for I have sinned
	if ($isNumericBool == True){
		
			if(!empty($sqlQuery)){
				$myData = infraConnection($sqlQuery);
			}
			
			if(!empty($myData)){
				echo $myDE->respond($myData);
				exit(0);
			}
			
			//If myData was empty, make isNumericBool false so we can run rest of searches as normal
			$isNumericBool = False;
	}
		 
//****************************
//SEARCH BY ABSTRACT AND TITLE
//*****************************
	$dictionaryArray = array();
	
	//Creating Dictionary array from a file
	$dictFile = fopen("SearchDictionary.txt", "r") or die("Unable to open file!");
	
	while(!feof($dictFile)){
		$currLine = fgets($dictFile); //Get one line of form key: keywords keywords keywords
		if(strcmp(trim($currLine), "") == 0){
			continue;
		}
		//Strip same characters that are stripped in regular search from the dictionary entries.
		$currLine = preg_replace('/[^a-zA-Z0-9.;:~ñ!%:\*_\/\s\-\']/', '', strip_tags($currLine));
		$currLine = str_replace("'", "''", $currLine); //Escape apostrophes so they can still be searched
		$lineArr = explode(":", $currLine); //Seperate it into an array of form [key, keywords keywords keywords]
		$newKey = $lineArr[0]; 
		$keyword = explode(" ", trim($lineArr[1])); //Trim excess surrounding whitespace and create array of form [keywords, keywords, keywords]
		$dictionaryArray[$newKey] = $keyword; //Add entry to make dictArray of form [key: keywords, keywords, keywords]
	}
	fclose($dictFile);
	//Completed reading from dictionary file
	
	//file_put_contents('C:\inetpub\logs\colin_kasearch_dict.txt', print_r($dictionaryArray, true)); //print dictionary array to a log
	
	if ($isNumericBool == False and $searchType == "kaTitleAbstract")
		{
			//Just declaring these variables to build the SQL string
			$tmpSqlTitle = ""; 
			$tmpSqlAbs = "";
			
			foreach ($searchArray as $key=>$word) {
				
				//Cleaned up the if else a bit, just add AND at beginning if not the first index
				if($key != '0')
				{
					$tmpSqlTitle .= " AND ";
					$tmpSqlAbs .= " AND ";

				}
				
				//Creating the Title section of the SQL search
				$tmpSqlTitle .= "(K.Title Like '%" . $word . "%' ";
				if(isset($dictionaryArray[$word])){ //If this word has an entry in the search dictionary...
					
					foreach($dictionaryArray[$word] as $subArr => $keyword){ 
						//Add each associated keyword as an OR in the search
						$tmpSqlTitle .= " OR K.Title Like '%" . $keyword . "%' ";// add an OR for this association in the SQL search
					}
					
				}
				$tmpSqlTitle .= ")";
				//End Title section
				
				//Creating Abstract section of sql search
				$tmpSqlAbs .= "(K.Abstract Like '%" . $word . "%' ";
				
				if(isset($dictionaryArray[$word])){
					foreach($dictionaryArray[$word] as $subArr => $keyword){
						//Add each associated keyword as an OR in the search
						$tmpSqlAbs .= " OR K.Abstract Like '%" . $keyword . "%' ";
					}
				}
				
				$tmpSqlAbs .= ")";
				//Ending Abstract section
				
				
			}
			//Moved sqlQuery set outside loop for efficiency purposes, as it was
			//being overwritten each time in the for loop and string concatentation is expensive
			$sqlQuery = $sqlBeginningStr . " AND ((" . $tmpSqlTitle . ") OR (" . $tmpSqlAbs . ")) ORDER BY K.HITS_PORTAL DESC";  //Create SQL Query for Normal Search

		}
		//file_put_contents('C:\inetpub\logs\colin.txt', print_r($sqlQuery, true));		
	
//********************************
//END SEARCH BY ABSTRACT AND TITLE
//********************************

//***************
// SEARCH SERVICE
//****************

	if ($isNumericBool == False and $searchType == "kaService") 
	{
		$sqlQuery = $sqlBeginningStr . "AND A.CI_Asset_Ref LIKE '%" . $queryValue . "%' ORDER BY K.HITS_PORTAL DESC";

	}
//****************
//END SEARCH SERVICE
//****************

//*******************
// SEARCH CONFIG ITEM
//*******************
	//file_put_contents('C:\inetpub\logs\colin_kasearch.txt', print_r($sqlBeginningStr, true));
	if ($isNumericBool == False and $searchType == "kaConfig") 
	{
		$kaSql = searchConfig($queryValue, $sqlProfile);
		if(empty($kaSql))
		{
			$myData = null;
		} else {	
			$sqlQuery = $sqlBeginningStr . $kaSql;
		}
	}
	
//*******************
// SEARCH CONFIG ITEM
//********************

//************
//FULL SEARCH
//************
	if ($isNumericBool == False and $searchType == "kaFull") 
	{
		$tmpSqlFull = "";
		$tmpSqlTitle = "";
		$tmpSqlAbs = "";
		foreach ($searchArray as $key=>$word) 
		{
			
			if($key!=0)
			{
				$tmpSqlFull .= " AND ";
				$tmpSqlTitle .= " AND ";
				$tmpSqlAbs .= " AND ";
			}
			
			//Setting the Full search section
			$tmpSqlFull .= "(K.Solution Like '%" . $word . "%' ";
			if(isset($dictionaryArray[$word])){ //If this word has an entry in the search dictionary...
					
				foreach($dictionaryArray[$word] as $subArr => $keyword){ 
					//Add each associated keyword as an OR in the search
					$tmpSqlFull .= " OR K.Title Like '%" . $keyword . "%' ";// add an OR for this association in the SQL search
				}
					
			}
			$tmpSqlFull .= ")";
			
			//Setting Title search section
			//Updated by Tom Hucker on 10/8 - this now searches the Title AND Abstract for words
			$tmpSqlTitle .= "(K.Title Like '%" . $word . "%' OR K.Abstract Like '%" . $word . "%' ";
			if(isset($dictionaryArray[$word])){ //If this word has an entry in the search dictionary...
					
				foreach($dictionaryArray[$word] as $subArr => $keyword){ 
					//Add each associated keyword as an OR in the search
					$tmpSqlTitle .= " OR K.Title Like '%" . $keyword . "%' OR K.Abstract Like '%" . $keyword . "%' ";// add an OR for this association in the SQL search
				}
					
			}
			$tmpSqlTitle .= ")";
			
			//Setting the Abstract search section
			$tmpSqlAbs .= "(K.Abstract Like '%" . $word . "%' ";
			if(isset($dictionaryArray[$word])){ //If this word has an entry in the search dictionary...
					
				foreach($dictionaryArray[$word] as $subArr => $keyword){ 
					//Add each associated keyword as an OR in the search
					$tmpSqlAbs .= " OR K.Title Like '%" . $keyword . "%' ";// add an OR for this association in the SQL search
				}
					
			}
			$tmpSqlAbs .= ")";		
		} 
		$sqlQuery = $sqlBeginningStr . " AND ((" . $tmpSqlFull . ") OR (" . $tmpSqlTitle . ") OR (" . $tmpSqlAbs . ")) ORDER BY K.HITS_PORTAL DESC";
	}

//******************
//END FULL SEARCH
//****************

//Send Results 

if(!empty($sqlQuery)){
	$myData = infraConnection($sqlQuery);
	//file_put_contents('C:\inetpub\logs\colinEnd.txt', print_r($sqlQuery, true));
}
//$sqlQuery2 = $sqlQuery;
//$sqlQuery2 .= $sqlQuery2;
//file_put_contents('C:\inetpub\logs\colin_kasearch.txt', print_r($sqlQuery, true));
//file_put_contents('C:\inetpub\logs\colin_kasearch.txt', print_r($myData, true));

	
	
echo $myDE->respond($myData);





?>