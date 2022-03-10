<?php 
//searchConfig.php
//Purpose: This file will query infra to determine all KAs matching a specific
//configuration item. This query will return all matching KA numbers. A new SQL
//query will be created to query for final KA results.
//Authors: Evie Vanderveer

function searchConfig($queryValue, $sqlProfile){
	
	//Remove "APL" if at the end of the config value
	$queryValue = preg_replace('/APL$/', '(APL)', $queryValue);
	
	$sqlQuery = "SELECT K.KNOWLEDGE_REF, K.HITS_PORTAL
	FROM  RV_CONFIG_ITEM A INNER JOIN (RV_KNOWLEDGE K INNER JOIN  KB_ASSET_LINK KAL with (nolock) 
	ON  K.KNOWLEDGE_REF = KAL.KNOWLEDGE_REF) 
	ON  A.CI_Item_Ref =  KAL.ITEM_REF 
	WHERE A.ENTITY_REF = 1 AND KAL.STATUS='A' AND K.Status='active' AND K.Entry_Status_Ref <> 0 " . $sqlProfile . 
	" AND A.CI_Asset_Ref = '" . $queryValue . "' ORDER BY 1";

	//Query infra for KAs that have specific config item
try 
{
	$myDB = new DatabaseConnection("../kasdgo_infraDR.ini");
} catch(Exception $e) {
	$myDE->setError($e->getMessage());
}

if (isset($myDB)) 
{
    $counter = 0; 
	$result = $myDB->connection->query($sqlQuery);
	$myData = $result->fetchall();
    $myDB = NULL;
}

	$kaStr = "";
	$i=0;
	
//Set SQL query to select by KA number
	foreach ($myData as list( $word)) 
	{
		if($i == 0) {
			$kaStr = $word;
			$i++;
		} else 
		{
			$kaStr .= ", " . $word;
			$i++;
		}
	}
	 
	if(empty($myData))
	{
		$kaSql = null;
	} else {
	$kaSql = " AND K.Knowledge_Ref IN (" . $kaStr . ") ORDER BY K.HITS_PORTAL DESC";
	}
	
	return $kaSql;
	
}
?>