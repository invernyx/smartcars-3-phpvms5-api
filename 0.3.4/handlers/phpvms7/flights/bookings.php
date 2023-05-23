<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$schedules = $database->fetch(
'SELECT ' . dbPrefix . 'bids.id as bidID,
' . dbPrefix . 'airlines.icao as code,
' . dbPrefix . 'flights.flight_number as number,
' . dbPrefix . 'flights.flight_type as type,
' . dbPrefix . 'flights.dpt_airport_id as departureAirport,
' . dbPrefix . 'flights.arr_airport_id as arrivalAirport,
' . dbPrefix . 'flights.route,
' . dbPrefix . 'flights.level as flightLevel,
' . dbPrefix . 'flights.distance,
' . dbPrefix . 'flights.dpt_time as departureTime,
' . dbPrefix . 'flights.arr_time as arrivalTime,
' . dbPrefix . 'flights.flight_time as flightTime,
' . dbPrefix . 'flights.days as daysOfWeek,
' . dbPrefix . 'flights.id as flightID,
' . dbPrefix . 'flights.notes FROM ' . dbPrefix . 'bids INNER JOIN ' . dbPrefix . 'flights ON ' . dbPrefix . 'bids.flight_id = ' . dbPrefix . 'flights.id INNER JOIN ' . dbPrefix . 'airlines ON ' . dbPrefix . 'flights.airline_id = ' . dbPrefix . 'airlines.id WHERE ' . dbPrefix . 'bids.user_id=?',
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

    $subfleet = $database->fetch(
        'SELECT DISTINCT ' . dbPrefix . 'aircraft.id as id  FROM ' . dbPrefix . 'aircraft
        LEFT JOIN ' . dbPrefix . 'flight_subfleet fs ON ' . dbPrefix . 'aircraft.subfleet_id = fs.subfleet_id
        WHERE fs.flight_id = ?
        AND ' . dbPrefix . 'aircraft.status = ?',
    array($schedule['flightID'], 'A'));

    foreach($subfleet as $aircraft) {
        $schedules[$idx]['aircraft'][] = $aircraft['id'];
    }

    if (array_key_exists('aircraft', $schedules[$idx]) && is_iterable($schedules[$idx]['aircraft']) && sizeof($schedules[$idx]['aircraft']) === 1) {
        $schedules[$idx]['aircraft'] = $schedules[$idx]['aircraft'][0];
    }
    if (!array_key_exists('aircraft', $schedules[$idx])) {
        $schedules[$idx]['aircraft'] = array();
    }
}
echo(json_encode($schedules));
?>
