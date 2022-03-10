<?php


require_once('class.php');

$myDE = new DataExchange();
$data = $myDE->convertRequestDataToObject();

$sqlQuery = "  SELECT [CI_Asset_Ref], [Class]
  FROM [InfraProd].[dbo].[RV_CONFIG_ITEM]
  WHERE [Entity_Ref] = '1' 
	AND NOT Class = 'Server Hardware' 
	AND NOT Class = 'Network Hardware' 
	AND NOT Class = 'Workstation' 
	AND NOT CI_Asset_Ref like 'SGOF%' 
	AND NOT CI_Asset_Ref like'SA00%'
	AND NOT CI_Asset_Ref like'LGOF%'
	AND NOT CI_Asset_Ref like'LDRS%'
	AND NOT CI_Asset_Ref like'SDRS%'
	AND NOT CI_Asset_Ref like'WHGZ%'
	AND NOT CI_Asset_Ref like'[ALWS]%[0-9]';";



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
file_put_contents('C:\inetpub\logs\kasearch.txt', print_r($services, true));

file_put_contents('C:\inetpub\wwwroot\KA Search Tool DR\ci.json', print_r($services, true));
?>