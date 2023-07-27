<?php
$database->createTable('smartCARS3_OngoingFlights', 'timestamp TIMESTAMP DEFAULT NOW() NOT NULL, pilotID INT(10) NOT NULL, bidID INT(10) NOT NULL, heading FLOAT NOT NULL, latitude FLOAT NOT NULL, longitude FLOAT NOT NULL, PRIMARY KEY (timestamp, pilotID, bidID)');
$database->execute('DELETE FROM smartCARS3_OngoingFlights WHERE timestamp < DATE_SUB(NOW(), INTERVAL 18 HOUR)');

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
}
assertData($_POST, array('bidID' => 'integer', 'timeRemaining' => 'float', 'latitude' => 'latitude', 'longitude' => 'longitude', 'heading' => 'heading', 'altitude' => 'integer', 'groundSpeed' => 'integer', 'distanceRemaining' => 'float', 'route' => 'array', 'phase' => 'phase', 'network' => 'network', 'aircraft' => 'integer'));
if($_POST['distanceRemaining'] < 0)
{
    error(400, 'Distance remaining must be above 0');
}
if($_POST['groundSpeed'] < 0)
{
    error(400, 'Ground speed must be above 0');
}

function phaseToStatus(string $phase): string {
    switch(strtolower($phase)) {
        case 'boarding':
            return 'BST';
        case 'push_back':
            return 'PBT';
        case 'taxi':
            return 'TXI';
        case 'take_off':
            return 'TOF';
        case 'rejected_take_off':
            return 'TXI';
        case 'climb_out':
            return 'TKO';
        case 'climb':
            return 'TKO';
        case 'cruise':
            return 'ENR';
        case 'descent':
            return 'TEN';
        case 'approach':
            return 'APR';
        case 'final':
            return 'FIN';
        case 'landed':
            return 'LAN';
        case 'go_around':
            return 'APR';
        case 'taxi_to_gate':
            return 'LAN';
        case 'deboarding':
            return 'ONB';
        case 'diverted':
            return 'DV';
    }
}

$pirepID = $database->fetch('SELECT ' . dbPrefix . 'pireps.id FROM ' . dbPrefix . 'bids INNER JOIN ' . dbPrefix . 'pireps ON ' . dbPrefix . 'bids.flight_id = ' . dbPrefix . 'pireps.flight_id WHERE ' . dbPrefix . 'bids.id=? AND ' . dbPrefix . 'bids.user_id=?', array($_POST['bidID'], $pilotID));
if($pirepID === array())
{
    $pirepID = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16);

    $flightID = $database->fetch('SELECT flight_id FROM ' . dbPrefix . 'bids WHERE id=? AND user_id=?', array($_POST['bidID'], $pilotID));
    if($flightID === array())
    {
        error(404, 'There is no flight with this bid ID');
    }
    $flightID = $flightID[0]['flight_id'];

    $flightDetails = $database->fetch('SELECT ' .
    'id as flight_id, ' .
    'airline_id, ' .
    'flight_number, ' .
    'route_code, ' .
    'route_leg, ' .
    'flight_type, ' .
    'dpt_airport_id, ' .
    'arr_airport_id, ' .
    'alt_airport_id, ' .
    'level, ' .
    'distance as planned_distance, ' .
    'flight_time as planned_flight_time
    FROM ' . dbPrefix . 'flights WHERE id=?', array($flightID));

    if($flightDetails === array())
    {
        error(404, 'There is no flight with the specified bid ID');
    }
    $flightDetails = $flightDetails[0];

    $database->execute('INSERT INTO ' . dbPrefix . 'pireps
    (id, user_id, airline_id, flight_id, flight_number, route_code, route_leg, flight_type, dpt_airport_id, arr_airport_id, alt_airport_id, level, planned_distance, planned_flight_time, route, source, source_name, status, aircraft_id, created_at, updated_at)
    VALUES (:id, :user_id, :airline_id, :flight_id, :flight_number, :route_code, :route_leg, :flight_type, :dpt_airport_id, :arr_airport_id, :alt_airport_id, :level, :planned_distance, :planned_flight_time, :route, :source, :source_name, :status, :aircraft_id, NOW(), NOW())',
    array(
        'id' => $pirepID,
        'user_id' => $pilotID,
        'airline_id' => $flightDetails['airline_id'],
        'flight_id' => $flightDetails['flight_id'],
        'flight_number' => $flightDetails['flight_number'],
        'route_code' => $flightDetails['route_code'],
        'route_leg' => $flightDetails['route_leg'],
        'flight_type' => $flightDetails['flight_type'],
        'dpt_airport_id' => $flightDetails['dpt_airport_id'],
        'arr_airport_id' => $flightDetails['arr_airport_id'],
        'alt_airport_id' => $flightDetails['alt_airport_id'],
        'level' => $flightDetails['level'],
        'planned_distance' => $flightDetails['planned_distance'],
        'planned_flight_time' => $flightDetails['planned_flight_time'],
        'route' => implode(' ', $_POST['route']),
        'source' => 1,
        'source_name' => 'smartCARS 3',
        'status' => phaseToStatus($_POST['phase']),
        'aircraft_id' => $_POST['aircraft']
    ));
}
else {
    $pirepID = $pirepID[0]['id'];
}
$database->execute('UPDATE ' . dbPrefix . 'pireps SET status=?, updated_at=NOW() WHERE id=?', array(phaseToStatus($_POST['phase']), $pirepID));

$database->execute('INSERT INTO ' . dbPrefix . 'acars
(id, pirep_id, type, status, lat, lon, distance, heading, altitude, gs, created_at, updated_at)
VALUES (:id, :pirep_id, 0, :status, :lat, :lon, :distance, :heading, :altitude, :gs, NOW(), NOW())',
array(
    'id' => $pirepID,
    'pirep_id' => $pirepID,
    'status' => phaseToStatus($_POST['phase']),
    'lat' => $_POST['latitude'],
    'lon' => $_POST['longitude'],
    'distance' => $_POST['distanceRemaining'],
    'heading' => $_POST['heading'],
    'altitude' => $_POST['altitude'],
    'gs' => $_POST['groundSpeed']
));
$database->execute('INSERT INTO smartCARS3_OngoingFlights (pilotID, bidID, heading, latitude, longitude) VALUES (?, ?, ?, ?, ?)', array($pilotID, $_POST['bidID'], $_POST['heading'], $_POST['latitude'], $_POST['longitude']));
?>
