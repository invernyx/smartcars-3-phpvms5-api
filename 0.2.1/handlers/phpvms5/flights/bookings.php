<?php
$bids = $database->fetch('SELECT bidid FROM ' . dbPrefix . 'bids' . ' WHERE pilotid=? LIMIT 1', array($pilotID));
if($bids === array()) {
    echo(json_encode(array('flight' => null)));
    exit;
}
$routes = $database->fetch('SELECT routeid FROM ' . dbPrefix . 'bids WHERE pilotid=? AND bidid=?', array($pilotID, $bids[0]['bidid']));
$schedule = $database->fetch('SELECT id,
code,
flightnum as number,
depicao as departureAirport,
arricao as arrivalAirport,
route,
(SELECT icao FROM ' . dbPrefix . 'aircraft WHERE id=aircraft) as aircraft,
flightlevel as flightLevel,
distance,
deptime as departureTime,
arrtime as arrivalTime,
CAST(flighttime AS DECIMAL(4,2)) as flightTime,
daysofweek as daysOfWeek,
notes FROM ' . dbPrefix . 'schedules WHERE id=?', array($routes[0]['routeid']));
if($schedule === array())
{
    error(404, 'No schedule could be found with the pilot bid');
    exit;
}
// Correct days of week to actual days
$daysOfWeek = array();
foreach(str_split($schedule[0]['daysOfWeek']) as $day)
{
    switch ($day) {
        case '0':
            array_push($daysOfWeek, 'Sunday');
            break;
        case '1':
            array_push($daysOfWeek, 'Monday');
            break;
        case '2':
            array_push($daysOfWeek, 'Tuesday');
            break;
        case '3':
            array_push($daysOfWeek, 'Wednesday');
            break;
        case '4':
            array_push($daysOfWeek, 'Thursday');
            break;
        case '5':
            array_push($daysOfWeek, 'Friday');
            break;
        case '6':
            array_push($daysOfWeek, 'Saturday');
            break;    
    }
}
$schedule[0]['daysOfWeek'] = $daysOfWeek;
// Correct route to be array
if($schedule[0]['route'] === '')
{
    unset($schedule[0]['route']);
}
else
{
    $schedule[0]['route'] = explode(' ', $schedule[0]['route']);
}
// Departure time
$departureTime = explode(':', $schedule[0]['departureTime']);
$schedule[0]['departureTime'] = $departureTime[0] . ':' . $departureTime[1];
// Arrival time
$arrivalTime = explode(':', $schedule[0]['arrivalTime']);
$schedule[0]['arrivalTime'] = $arrivalTime[0] . ':' . $arrivalTime[1];
// Flight Level
$schedule[0]['flightLevel'] = intval($schedule[0]['flightLevel']);
// Flight Time
$schedule[0]['flightTime'] = floatval($schedule[0]['flightTime']);
echo(json_encode($schedule[0]));
?>