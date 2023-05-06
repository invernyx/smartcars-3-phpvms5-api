<?php
$query = 'SELECT flights.id,
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
flights.flight_time as flightTime,
flights.days as daysOfWeek,
flights.notes FROM ' . dbPrefix . 'flights INNER JOIN ' . dbPrefix . 'airlines ON flights.airline_id = airlines.id';
$parameters = array();

if($_GET['departureAirport'] !== null) {
    assertData($_GET, array('departureAirport' => 'airport'));
    $query .= ' WHERE flights.dpt_airport_id = :departureAirport';
    $parameters[':departureAirport'] = $_GET['departureAirport'];
}
if($_GET['arrivalAirport'] !== null) {
    assertData($_GET, array('arrivalAirport' => 'airport'));
    if($parameters === array()) {
        $query .= ' WHERE ';
    } else {
        $query .= ' AND ';
    }
    $query .= 'flights.arr_airport_id = :arrivalAirport';
    $parameters[':arrivalAirport'] = $_GET['arrivalAirport'];
}
$aircraft;
if($_GET['aircraft'] !== null) {
    assertData($_GET, array('aircraft' => 'int'));
    $aircraft = $database->fetch('SELECT id FROM ' . dbPrefix . 'aircraft WHERE id=?', $_GET['aircraft']);
    if($aircraft === array()) {
        error(404, 'Aircraft not found');
    }
    $aircraft = $aircraft[0];
}
else {
    $aircraft = $database->fetch('SELECT id FROM ' . dbPrefix . 'aircraft WHERE status = "A"');
}
if($_GET['callsign'] !== null) {
    assertData($_GET, array('callsign' => 'string'));
    if($parameters === array()) {
        $query .= ' WHERE ';
    } else {
        $query .= ' AND ';
    }
    $query .= 'flights.callsign LIKE :callsign';
    $parameters[':callsign'] = $_GET['callsign'];
}
if($_GET['minimumFlightTime'] !== null) {
    assertData($_GET, array('minimumFlightTime' => 'int'));
    if($parameters === array()) {
        $query .= ' WHERE ';
    } else {
        $query .= ' AND ';
    }
    $query .= 'flights.flight_time >= :minimumFlightTime';
    $parameters[':minimumFlightTime'] = $_GET['minimumFlightTime'];
}
if($_GET['maximumFlightTime'] !== null) {
    assertData($_GET, array('maximumFlightTime' => 'int'));
    if($parameters === array()) {
        $query .= ' WHERE ';
    } else {
        $query .= ' AND ';
    }
    $query .= 'flights.flight_time <= :maximumFlightTime';
    $parameters[':maximumFlightTime'] = $_GET['maximumFlightTime'];
}
if($_GET['minimumDistance'] !== null) {
    assertData($_GET, array('minimumDistance' => 'int'));
    if($parameters === array()) {
        $query .= ' WHERE ';
    } else {
        $query .= ' AND ';
    }
    $query .= 'flights.distance >= :minimumDistance';
    $parameters[':minimumDistance'] = $_GET['minimumDistance'];
}
if($_GET['maximumDistance'] !== null) {
    assertData($_GET, array('maximumDistance' => 'int'));
    if($parameters === array()) {
        $query .= ' WHERE ';
    } else {
        $query .= ' AND ';
    }
    $query .= 'flights.distance <= :maximumDistance';
    $parameters[':maximumDistance'] = $_GET['maximumDistance'];
}

if($parameters === array()) {
    $query .= ' WHERE ';
} else {
    $query .= ' AND ';
}
$query .= ' flights.active = 1 AND flights.visible = 1 ORDER BY id DESC LIMIT 100';

$results = $database->fetch($query, $parameters);
$returns = array();

foreach($results as $index=>$result) {
    // Correct days of week to actual days
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
    // Correct route to be array
    if($result['route'] !== null && $result['route'] !== '') {
        $results[$index]['route'] = explode(' ', $result['route']);
    } else {
        unset($results[$index]['route']);
    }
    // Distance
    $results[$index]['distance'] = floatval($result['distance']);
    // Flight time
    $results[$index]['flightTime'] = floatval($result['flightTime']) / 60;

    // Flight type
    switch($result['type']) {
        case 'J':
        case 'E':
        case 'C':
        case 'G':
        case 'O':
            $results[$index]['type'] = 'P';
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
            $results[$index]['type'] = 'C';
            break;
    }

    // Clone result index and create a copy for each aircraft
    $aircraftResults = array();
    foreach($aircraft as $aircraftIndex=>$aircraftResult) {
        $aircraftResults[$aircraftIndex] = $results[$index];
        $aircraftResults[$aircraftIndex]['aircraft'] = (string)$aircraftResult['id'];
        $returns[] = $aircraftResults[$aircraftIndex];
    }
}
echo(json_encode($returns));
?>