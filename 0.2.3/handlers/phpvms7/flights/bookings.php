<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$schedules = $database->fetch(
'SELECT bids.id as bidID,
airlines.icao as code,
flights.flight_number as number,
flights.flight_type as type,
flights.dpt_airport_id as departureAirport,
flights.arr_airport_id as arrivalAirport,
flights.route,
flights.level as flightLevel,
flights.distance,
flights.dpt_time as departureTime,
flights.arr_time as arrivalTime,
CAST(flights.flight_time AS DECIMAL(4,2)) as flightTime,
flights.days as daysOfWeek,
flights.notes FROM ' . dbPrefix . 'bids INNER JOIN ' . dbPrefix . 'flights ON bids.flight_id = flights.id INNER JOIN ' . dbPrefix . 'airlines ON flights.airline_id = airlines.id WHERE ' . dbPrefix . 'bids.user_id=?',
array($pilotID)
);
$aircraft = $database->fetch(
    'SELECT id FROM ' . dbPrefix . 'aircraft WHERE status = "A"'
);

foreach($schedules as $idx=>$schedule) {
    $daysOfWeek = array();
    if($schedule['daysOfWeek'] & 1 << 0) {
        array_push($daysOfWeek, 'Monday');
    }
    if($schedule['daysOfWeek'] & 1 << 1) {
        array_push($daysOfWeek, 'Tuesday');
    }
    if($schedule['daysOfWeek'] & 1 << 2) {
        array_push($daysOfWeek, 'Wednesday');
    }
    if($schedule['daysOfWeek'] & 1 << 3) {
        array_push($daysOfWeek, 'Thursday');
    }
    if($schedule['daysOfWeek'] & 1 << 4) {
        array_push($daysOfWeek, 'Friday');
    }
    if($schedule['daysOfWeek'] & 1 << 5) {
        array_push($daysOfWeek, 'Saturday');
    }
    if($schedule['daysOfWeek'] & 1 << 6) {
        array_push($daysOfWeek, 'Sunday');
    }
    $schedules[$idx]['daysOfWeek'] = $daysOfWeek;
    // Correct route to be array
    if($schedule['route'] !== null && $schedule['route'] !== '') {
        $schedules[$idx]['route'] = explode(' ', $schedule['route']);
    } else {
        unset($schedules[$idx]['route']);
    }
    // Distance
    $schedules[$idx]['distance'] = floatval($schedule['distance']);
    // Flight time
    $schedules[$idx]['flightTime'] = floatval($schedule['flightTime']) / 60;
    // Flight type
    switch($schedule['type']) {
        case 'J':
        case 'E':
        case 'C':
        case 'G':
        case 'O':
            $schedules[$idx]['type'] = 'P';
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
            $schedules[$idx]['type'] = 'C';
            break;
    }

    foreach($aircraft as $aircraft) {
        $schedules[$idx]['aircraft'][] = $aircraft['id'];
    }
}
echo(json_encode($schedules));
?>