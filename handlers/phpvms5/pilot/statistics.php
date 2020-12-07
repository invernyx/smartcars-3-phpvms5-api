<?php
if(!defined('API'))
    exit;
    
$pilotResults = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE pilotid=?',array($_GET['uid']));
$pilotResults = $pilotResults[0];
$pirepResults = $database->fetch('SELECT landingRate FROM ' . dbPrefix . 'pireps WHERE pilotid=? AND accepted = 1 ORDER BY submitdate');
$totalLandingRate = 0;
if ($pirepResults != array())
{
    foreach($pirepResults as $pirep) {
        $totalLandingRate += $pirep['landingrate'];
    }
}
$totalPireps = sizeof($pirepResults) > 0 ? sizeof($pirepResults) : 1;
echo(json_encode(array('hours'=>$pilotResults['totalhours'], 'flights'=>$pilotResults['totalflights'], 'landingRate'=>round($totalLandingRate/$totalPireps), 'pirepsFiled'=>sizeof($pirepResults))));
?>