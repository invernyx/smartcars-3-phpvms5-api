<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}

// TODO: Rank/aircraft restriction applied here
assertData($_POST, array(
    'number' => 'string',
    'departure' => 'airport',
    'arrival' => 'airport',
    'route' => 'array',
    'aircraft' => 'int',
    'cruise' => 'int',
    'type' => 'string',
    'departureTime' => 'string',
    'arrivalTime' => 'string')
);

switch($_POST['type']) {
    case 'P':
    case 'C':
        break;
    default:
        error(400, 'Invalid type for type (expected `C` or `P` [Raw Type: `string`])');
        exit;
}

function coordinatesToNM($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 3443.92)
{
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
  
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
  
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

$code = substr($_POST['number'], 0, 3);
$airline = $database->fetch('SELECT id, icao FROM ' . dbPrefix . 'airlines WHERE icao=?', array($code));
if($airline === array())
{
    $database->execute('INSERT INTO ' . dbPrefix . 'airlines (icao, name, active, created_at, updated_at) VALUES (?, "Charter", 0, NOW(), NOW())', array($code));
    $airline = $database->getLastInsertID('id');
}
else {
    $airline = $airline[0]['id'];
}

$flightID = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16);

$departure = $database->fetch('SELECT lat, lon FROM ' . dbPrefix . 'airports WHERE icao=?', array($_POST['departure']));
$arrival = $database->fetch('SELECT lat, lon FROM ' . dbPrefix . 'airports WHERE icao=?', array($_POST['arrival']));
if($departure === array() || $arrival === array())
{
    error(400, 'Departure or arrival airport not found.');
    exit;
}
$departure = $departure[0];
$arrival = $arrival[0];

switch($_POST['type']) {
    case 'P':
        $_POST['type'] = 'C';
        break;
    case 'C':
        $_POST['type'] = 'H';
        break;
}

$database->execute('INSERT INTO ' . dbPrefix . 'flights
(id,
airline_id,
flight_number,
callsign,
dpt_airport_id,
arr_airport_id,
dpt_time,
arr_time,
level,
distance,
flight_time,
flight_type,
route,
notes,
days,
has_bid,
active,
visible,
created_at,
updated_at) VALUES (:id, :airline, :number, :callsign, :dpt, :arr, :dptTime, :arrTime, :level, :distance, :time, :type, :route, "smartCARS Charter Flight", 127, 1, 1, 0, NOW(), NOW())', array(
    'id' => $flightID,
    'airline' => $airline,
    'number' => substr($_POST['number'], 3),
    'callsign' => substr($_POST['number'], 3),
    'dpt' => $_POST['departure'],
    'arr' => $_POST['arrival'],
    'dptTime' => $_POST['departureTime'],
    'arrTime' => $_POST['arrivalTime'],
    'level' => $_POST['cruise'],
    'distance' => coordinatesToNM($departure['lat'], $departure['lon'], $arrival['lat'], $arrival['lon']),
    'time' => abs(strtotime($_POST['arrivalTime']) - strtotime($_POST['departureTime'])) / 60,
    'type' => $_POST['type'],
    'route' => implode(' ', $_POST['route'])
));
$fare = $database->select(dbPrefix . 'fares', 'id', 'WHERE active=1 AND name="smartCARS Charter Flight"');
if($fare !== array()) {
    $fare = $fare[0]['id'];
    $database->insert(dbPrefix . 'flight_fare', array('flight_id' => $flightID, 'fare_id' => $fare));
}

$database->execute('INSERT INTO ' . dbPrefix . 'bids (user_id, flight_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())', array($pilotID, $flightID));
echo(json_encode(array('bidID'=>intval($database->getLastInsertID('id')))));
?>