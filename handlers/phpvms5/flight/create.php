<?php
if(!defined('API'))
    exit;

assertData(
    array('number' => 'number', 'departure' => 'string', 'arrival' => 'string', 'route' => 'string', 'aircraft' => 'string', 'cruise' => 'number', 'distance' => 'number', 'departureTime' => 'string', 'departuretime' => 'string', 'arrivalTime' => 'string', 'flightTime' => 'string', 'ticketPrice' => 'number', 'lat' => 'string', 'long' => 'string', 'heading' => 'number', 'altitude' => 'number', 'network' => 'string'),
    $_POST
);

if (isset($_POST['flightType']) && $_POST['flightType'] == 'C')
    $type = 'C';
else
    $type = 'P';

if (isset($_POST['code']) && $_POST['code'] == '')
    $code = $_POST['code'];
else
    $code = 'SCC';

$airline = $database->fetch('SELECT * FROM ' . dbPrefix . 'airlines WHERE code=?',array($code));
if ($airline == array()) {
    $query = $database->execute('INSERT INTO ' . dbPrefix .'airlines (id, code, name, enabled) VALUES (NULL, ?, "Charter", 0)',array($code));
    if ($query == null)
        errorOut(500,'Unable to create charter airline');
}

$database->createTable('smartCARS3_CharterFlights','scheduleID int, bidID int, dbID int, PRIMARY KEY (scheduleID)');
$params = array(
    $code,
    $_POST['number'],
    $_POST['departure'],
    $_POST['arrival'],
    $_POST['route'],
    $_POST['aircraft'],
    $_POST['cruise'],
    $_POST['distance'],
    $_POST['departureTime'],
    $_POST['arrivalTime'],
    $_POST['flightTime'],
    $_POST['ticketPrice'],
    $type
);
if ($database->execute('INSERT INTO ' . dbPrefix . 'schedules (id, code, flightnum, depicao, arricao, route, aircraft, flightlevel, distance, deptime, arrtime, flighttime, price, flighttype, enabled) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)',$params) != true)
    errorOut(500,'Unable to create schedules');
$scheduleID = $database->getLastInsertID();
$pilotName = $database->fetch('SELECT firstname, lastname FROM ' . dbPrefix . 'pilots WHERE pilotid=?',array($dbID));
if ($pilotName == array())
    errorOut(404,'Pilot not found');
$pilotName = $pilotName[0];
$departureAirport = $database->fetch('SELECT name FROM ' . dbPrefix . 'airports WHERE icao=?',array($_POST['departure']));
if ($departureAirport == array())
    errorOut(404,'Departure airport not found');
$departureAirport = $departureAirport[0];
$arrivalAirport = $database->fetch('SELECT name FROM ' . dbPrefix . 'airports WHERE icao=?',array($_POST['arrival']));
if ($arrivalAirport == array())
    errorOut(404,'Arrival airport not found');
$arrivalAirport = $arrivalAirport[0];
$params = array(
    $dbID,
    $_POST['number'],
    $pilotName['firstname'] . ' ' . $pilotName['lastname'],
    $_POST['aircraft'],
    $_POST['lat'],
    $_POST['long'],
    $_POST['heading'],
    $_POST['altitude'],
    $_POST['departure'],
    $departureAirport['name'],
    $_POST['arrival'],
    $arrivalAirport['name'],
    $_POST['departureTime'],
    $_POST['flightTime'],
    $_POST['arrivalTime'],
    $_POST['route'],
    $_POST['distance'],
    $_POST['network'],
);
$acarsQuery = $database->execute('INSERT INTO ' . dbPrefix . 'acarsdata (pilotid, flightnum, pilotname, aircraft, lat, lng, heading, alt, gs, depicao, depapt, arricao, arrapt, deptime, timeremaining, arrtime, route, route_details, distremain, phasedetail, online, messagelog, lastupdate, client) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, "",?, "preflight", ?, "", NOW(), "smartCARS 3")',$params);
if ($acarsQuery != true)
    errorOut(500,'Unable to create data instance');
$instanceID = $database->getLastInsertID();
if ($database->execute('INSERT INTO ' . dbPrefix . 'bids (bidid, pilotid, routeid, dateadded) VALUES (NULL, ?, ?, NOW())',array($dbID, $scheduleID)) != true)
    errorOut(500,'Unable to create bid');
$bidID = $database->getLastInsertID();
$charterQuery = $database->execute('INSERT INTO smartCARS3_CharterFlights (scheduleID, bidID, dbID) VALUES (?, ?, ?)',array($scheduleID, $bidID, $dbID));
if ($charterQuery != true)
    errorOut(500,'Unable to create charter flight');
echo(json_encode(array('success'=>($charterQuery && $acarsQuery),'instanceID'=>$instanceID)));
?>