<?php

require_once('class.php');

$myDE = new DataExchange();
$data = $myDE->convertRequestDataToObject();

$sqlQuery = "SELECT [CI_Asset_Ref]
  FROM [InfraProd].[dbo].[RV_CONFIG_ITEM]
  WHERE [Entity_Ref] = '2';";



//******************
//Infra DB
//******************
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
//******************
//END Infra DB
//******************
$services = array();

foreach ($myData as $key=>$word) 
{
	array_push($services, $word[0]);
}


$services = json_encode($services);


file_put_contents('C:\inetpub\wwwroot\KA Search Tool DR\services.json', print_r($services, true));
?>