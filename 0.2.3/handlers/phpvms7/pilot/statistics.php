<?php
$pilotStatistics = $database->fetch('SELECT flight_time as totalhours, flights as totalflights FROM ' . dbPrefix . 'users WHERE pilot_id=?', array($pilotID));
$pirepStatistics = $database->fetch('SELECT landing_rate as landingRate FROM ' . dbPrefix . 'pireps WHERE user_id=? and status = 2', array($pilotID));
$pilotStatistics = $pilotStatistics[0];
$totalLandingRate = 0;
if($pirepStatistics !== array())
{
    foreach($pirepStatistics as $pirep)
    {
        $totalLandingRate += $pirep['landingRate'];
    }
}
echo(json_encode(array(
    'hoursFlown' => $pilotStatistics['totalhours'],
    'flightsFlown' => $pilotStatistics['totalflights'],
    'averageLandingRate' => count($pirepStatistics) > 0 ? round($totalLandingRate/count($pirepStatistics)) : 0,
    'pirepsFiled' => count($pirepStatistics),
)));
?>