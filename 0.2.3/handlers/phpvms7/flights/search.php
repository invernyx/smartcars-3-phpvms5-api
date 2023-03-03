<?php
$query = 'SELECT id,
airlines.icao as code,
flight_number as number,
dpt_airport_id as departureAirport,
arr_airport_id as arrivalAirport,
route,
level as flightLevel,
distance,
dpt_time as departureTime,
arr_time as arrivalTime,
CAST(flight_time AS DECIMAL(4,2)) as flightTime,
days as daysOfWeek,
notes FROM ' . dbPrefix . 'flights';

$parameters = array();
$results = $database->fetch($query, $parameters);

foreach($results as $index=>$result) {
    $daysOfWeek = array();
    if($result['daysOfWeek'] & 1 << 0) {
        array_push($daysOfWeek, 'Monday');
    }
    if($result['daysOfWeek'] & 1 << 1) {
        array_push($daysOfWeek, 'Tuesday');
    }
    if($result['daysOfWeek'] & 1 << 2) {
        array_push($daysOfWeek, 'Wednesday');
    }
    if($result['daysOfWeek'] & 1 << 3) {
        array_push($daysOfWeek, 'Thursday');
    }
    if($result['daysOfWeek'] & 1 << 4) {
        array_push($daysOfWeek, 'Friday');
    }
    if($result['daysOfWeek'] & 1 << 5) {
        array_push($daysOfWeek, 'Saturday');
    }
    if($result['daysOfWeek'] & 1 << 6) {
        array_push($daysOfWeek, 'Sunday');
    }
    $results[$index]['daysOfWeek'] = $daysOfWeek;
}
?>