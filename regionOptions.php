<?php
//regionOptions.php
//Purpose: The purpose of this page is to display region options to anyone with the SD profile selected.
//Author: Evie Vanderveer

require_once('class.php');
require_once('userSettings.php');
require_once('infraProfiles.php');
$usersActualProfile = getUserSettings();



//Region display for Global SD
$GBLdisplayRegion = '<div class="row"><div class="col-md-2"><h5>SD Regional Prefixes:</h5></div>';					
$GBLdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="nac" name="region" type="checkbox" /> NAC </h5></div></div>';
$GBLdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="anz" name="region" type="checkbox" /> ANZ </h5></div></div>';
$GBLdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="shk" name="region" type="checkbox" /> SHK </h5></div></div>';
$GBLdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="cn" name="region" type="checkbox" /> CN </h5></div></div>';
$GBLdisplayRegion .= '</div><div class="row"><div class="col-md-2"></div>';
$GBLdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="eur" name="region" type="checkbox" /> EUR </h5></div></div>';
$GBLdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="brs" name="region" type="checkbox" /> BRS </h5></div></div>';
$GBLdisplayRegion .= '</div>';
$isSD = "False";

//Region Display for Capgemini(EUR) SD

$EURdisplayRegion = '<div class="row"><div class="col-md-2"><h5>SD Regional Prefixes:</h5></div>';
$EURdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="all" name="region" type="checkbox" /> All Regions</h5></div></div>'; //this will not regiester as a region to it will search will all regions					
$EURdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="eur sh" name="region" type="checkbox" /> EUR SH </h5></div></div>';


//Region Display for Tiba SD

$TIBAdisplayRegion = '<div class="row"><div class="col-md-2"><h5>SD Regional Prefixes:</h5></div>';
$TIBAdisplayRegion .= '<div class="col-md-2"><div class="form-group"><h5><input value="all" name="region" type="checkbox" /> All Regions</h5></div></div>'; 					

if (in_array("kaServiceDeskPitt", $usersActualProfile))
{
	echo $GBLdisplayRegion;
} else if (in_array("kaServiceDeskCapgemini", $usersActualProfile))
{
	echo $EURdisplayRegion;
} else if (in_array("kaServiceDeskTiba", $usersActualProfile))
{
	echo $TIBAdisplayRegion;
}

?>