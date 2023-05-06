<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$query = 'SELECT id,
created_at as submitDate,
(SELECT icao FROM ' . dbPrefix . 'airlines WHERE ' . dbPrefix . 'airlines.id=' . dbPrefix . 'pireps.airline_id) as code,
flight_number as number,
route,
distance,
flight_type as flightType,
dpt_airport_id as departureAirport,
arr_airport_id as arrivalAirport,
aircraft_id as aircraft,
state as status,
flight_time as flightTime,
landing_rate as landingRate,
fuel_used as fuelUsed FROM ' . dbPrefix . 'pireps WHERE user_id=:pilotid ORDER BY submitdate DESC';
$parameters = array(':pilotid' => $pilotID);

$results = $database->fetch($query, $parameters);
foreach ($results as $index => $result) {
    switch(intval($result['status'])) {
        case 1:
            $results[$index]['status'] = 'Pending';
            break;
        case 2:
            $results[$index]['status'] = 'Accepted';
            break;
        case 6:
            $results[$index]['status'] = 'Rejected';
            break;
        default:
            unset($results[$index]);
            continue;
    }
    
    switch($result['flightType']) {
        case 'J':
        case 'E':
        case 'C':
        case 'G':
        case 'O':
            $results[$index]['flightType'] = 'P';
            break;
        case 'A':
        case 'H':
        case 'I':
        case 'K':
        case 'M':
        case 'P':
        case 'T':
        case 'W':
        case 'X':
            $results[$index]['flightType'] = 'C';
            break;
    }

    if($result['flightTime'] !== null) {
        $results[$index]['flightTime'] = floatval($result['flightTime']) / 60;
    }

    if($result['distance'] !== null) {
        $results[$index]['distance'] = floatval($result['distance']);
    }
    if($result['aircraft'] !== null) {
        $results[$index]['aircraft'] = intval($result['aircraft']);
    }
    if($result['landingRate'] !== null) {
        $results[$index]['landingRate'] = floatval($result['landingRate']);
    }
    if($result['fuelUsed'] !== null) {
        $results[$index]['fuelUsed'] = floatval($result['fuelUsed']);
    }
}
echo(json_encode($results));
?>