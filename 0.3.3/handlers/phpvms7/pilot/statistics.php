<?php
$pilotStatistics = $database->fetch('SELECT flight_time as totalhours, flights as totalflights FROM ' . dbPrefix . 'users WHERE pilot_id=?', array($pilotID));
$pirepStatistics = $database->fetch('SELECT COALESCE(AVG(landing_rate), 0) AS landingRate from pireps WHERE user_id = ? AND state = 2', array($pilotID));
$pilotStatistics = $pilotStatistics[0];
echo(json_encode(array(
    'hoursFlown' => $pilotStatistics['totalhours'] / 60,
    'flightsFlown' => $pilotStatistics['totalflights'],
    'averageLandingRate' => $pirepStatistics[0]['landingRate'],
    'pirepsFiled' => count($pirepStatistics),
)));
?>
