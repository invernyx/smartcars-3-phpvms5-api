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

$code = substr($_POST['number'], 0, 3);
$airlines = $database->fetch('SELECT code FROM ' . dbPrefix . 'airlines WHERE code=?', array($code));
if($airlines === array())
{
    $database->execute('INSERT INTO ' . dbPrefix . 'airlines (code, name, enabled) VALUES (?, "Charter", 0)', array($code));
}
$database->execute('INSERT INTO ' . dbPrefix . 'schedules
(code,
flightnum,
depicao,
arricao,
route,
route_details,
aircraft,
flightlevel,
deptime,
arrtime,
flighttime,
price,
flighttype,
timesflown,
notes,
enabled) VALUES
(?, ?, ?, ?, ?, "", ?, ?, ?, ?, ?, 0, "C", 0, "smartCARS Charter Flight", 0)',
array($code, substr($_POST['number'], 3), $_POST['departure'], $_POST['arrival'], implode(' ', $_POST['route']), $_POST['aircraft'], $_POST['cruise'], $_POST['departureTime'], $_POST['arrivalTime'], strtotime($_POST['arrivalTime']) - strtotime($_POST['departureTime'])));

$database->execute('INSERT INTO ' . dbPrefix . 'bids (pilotid, routeid, dateadded) VALUES (?, ?, NOW())', array($pilotID, $database->getLastInsertID('id')));
echo(json_encode(array('bidID'=>intval($database->getLastInsertID('bidid')))));
?>