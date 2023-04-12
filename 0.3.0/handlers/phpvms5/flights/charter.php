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
    'departureTime' => 'string',
    'arrivalTime' => 'string')
);

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
$airlines = $database->fetch('SELECT code FROM ' . dbPrefix . 'airlines WHERE code=?', array($code));
if($airlines === array())
{
    $database->execute('INSERT INTO ' . dbPrefix . 'airlines (code, name, enabled) VALUES (?, "Charter", 0)', array($code));
}

$departure = $database->fetch('SELECT lat, lng FROM ' . dbPrefix . 'airports WHERE icao=?', array($_POST['departure']));
$arrival = $database->fetch('SELECT lat, lng FROM ' . dbPrefix . 'airports WHERE icao=?', array($_POST['arrival']));
if($departure === array() || $arrival === array())
{
    error(400, 'Departure or arrival airport not found.');
    exit;
}
$departure = $departure[0];
$arrival = $arrival[0];

$database->execute('INSERT INTO ' . dbPrefix . 'schedules
(code,
flightnum,
depicao,
arricao,
route,
route_details,
aircraft,
flightlevel,
distance,
deptime,
arrtime,
flighttime,
price,
flighttype,
timesflown,
notes,
enabled) VALUES
(:code, :number, :dep, :arr, :route, "", :aircraft, :level, :distance, :deptime, :arrtime, :flighttime, 0, "C", 0, "smartCARS Charter Flight", 0)',
array(
    'code' => $code,
    'number' => substr($_POST['number'], 3),
    'dep' => $_POST['departure'],
    'arr' => $_POST['arrival'],
    'route' => implode(' ', $_POST['route']),
    'aircraft' => $_POST['aircraft'],
    'level' => $_POST['cruise'],
    'distance' => coordinatesToNM($departure['lat'], $departure['lng'], $arrival['lat'], $arrival['lng']),
    'deptime' => $_POST['departureTime'],
    'arrtime' => $_POST['arrivalTime'],
    'flighttime' => abs(strtotime($_POST['arrivalTime']) - strtotime($_POST['departureTime'])) / 3600
));

$database->execute('INSERT INTO ' . dbPrefix . 'bids (pilotid, routeid, dateadded) VALUES (?, ?, NOW())', array($pilotID, $database->getLastInsertID('id')));
echo(json_encode(array('bidID'=>intval($database->getLastInsertID('bidid')))));
?>