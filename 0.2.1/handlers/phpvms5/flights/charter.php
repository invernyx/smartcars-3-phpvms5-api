<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
$missingFields = array();
if($_POST['number'] === null)
{
    array_push($missingFields, 'number (type `string`)');
}
if($_POST['departure'] === null)
{
    array_push($missingFields, 'departure (type `airport`)');
}
if($_POST['arrival'] === null)
{
    array_push($missingFields, 'arrival (type `airport`)');
}
if($_POST['route'] === null)
{
    array_push($missingFields, 'route (type `array`)');
}
if($_POST['aircraft'] === null)
{
    array_push($missingFields, 'aircraft (type `aircraft`)');
}
if($_POST['cruise'] === null)
{
    array_push($missingFields, 'cruise (type `int`)');
}
if($_POST['distance'] === null)
{
    array_push($missingFields, 'distance (type `float`)');
}
if($_POST['departureTime'] === null)
{
    array_push($missingFields, 'departureTime (type `time`)');
}
if($_POST['arrivalTime'] === null)
{
    array_push($missingFields, 'arrivalTime (type `time`)');
}

if(count($missingFields) !== 0)
{
    error(400, 'The following required fields were not present: ' . implode(', ', $missingFields));
    exit;
}
assertData($_POST, array(
    'number' => 'string',
    'departure' => 'airport',
    'arrival' => 'airport',
    'route' => 'array',
    'aircraft' => 'aircraft',
    'cruise' => 'int',
    'distance' => 'float',
    'departureTime' => 'string',
    'arrivalTime' => 'string')
);

$code = substr($_POST['number'], 0, 3);
$airlines = $database->fetch('SELECT code FROM ' . dbPrefix . 'airlines WHERE code=?', array($code));
if($airlines === array())
{
    $database->execute('INSERT INTO ' . dbPrefix . 'airlines (code, name, enabled) VALUES (?, "Charter", 0)', array($code));
}

?>