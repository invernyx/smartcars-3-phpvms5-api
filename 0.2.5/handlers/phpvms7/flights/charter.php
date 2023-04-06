<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
error(501, 'This feature is not yet implemented.');

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

$code = substr($_POST['number'], 0, 3);
$airlines = $database->fetch('SELECT icao FROM ' . dbPrefix . 'airlines WHERE icao=?', array($code));
if($airlines === array())
{
    $database->execute('INSERT INTO ' . dbPrefix . 'airlines (icao, name, active, created_at, updated_at) VALUES (?, "Charter", 0, NOW(), NOW())', array($code));
}

// Active but not visible flight
?>