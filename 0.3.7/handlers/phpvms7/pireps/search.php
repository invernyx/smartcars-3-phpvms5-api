<?php
$query = 'SELECT id,
created_at as submitDate,
(SELECT icao FROM ' . dbPrefix . 'airlines WHERE ' . dbPrefix . 'airlines.id=' . dbPrefix . 'pireps.airline_id) as airlineCode,
flight_number as number,
route,
planned_distance as distance,
flight_type as flightType,
dpt_airport_id as departureAirport,
arr_airport_id as arrivalAirport,
aircraft_id as aircraft,
state as status,
flight_time as flightTime,
landing_rate as landingRate,
fuel_used as fuelUsed FROM ' . dbPrefix . 'pireps WHERE user_id=:pilotid AND state != 4';
$parameters = array(':pilotid' => $pilotID);

if($_GET['departureAirport'] !== null)
{
    assertData($_GET, array('departureAirport' => 'airport'));
    $query .= ' AND dpt_airport_id = :departureAirport';
    $parameters[':departureAirport'] = $_GET['departureAirport'];
}
if($_GET['arrivalAirport'] !== null)
{
    assertData($_GET, array('arrivalAirport' => 'airport'));
    $query .= ' AND arr_airport_id = :arrivalAirport';
    $parameters[':arrivalAirport'] = $_GET['arrivalAirport'];
}
if($_GET['startDate'] !== null)
{
    assertData($_GET, array('startDate' => 'date'));
    $query .= ' AND created_at >= :startDate';
    $parameters[':startDate'] = $_GET['startDate'];
}
if($_GET['endDate'] !== null)
{
    assertData($_GET, array('endDate' => 'date'));
    $query .= ' AND created_at <= DATE_ADD(:endDate, INTERVAL 1 DAY)';
    $parameters[':endDate'] = $_GET['endDate'];
}
if($_GET['status'] !== null)
{
    assertData($_GET, array('status' => 'status'));
    $query .= ' AND state = :status';
    switch (strtolower($_GET['status'])) {
        case 'accepted':
            $parameters[':status'] = 2;
            break;
        case 'pending':
            $parameters[':status'] = 1;
            break;
        case 'rejected':
            $parameters[':status'] = 6;
            break;
    }
}
if($_GET['aircraft'] !== null)
{
    assertData($_GET, array('aircraft' => 'int'));
    $query .= ' AND aircraft_id = :aircraft';
    $parameters[':aircraft'] = $_GET['aircraft'];
}

$query .= ' ORDER BY submitdate DESC LIMIT 100';

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
