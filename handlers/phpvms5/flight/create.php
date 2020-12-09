<?php
// Create schedule into schedules table
// Get ID and bid on it
// Get Bid ID and Schedule ID to charteredFlights table
if(!defined('API'))
    exit;

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
$database->execute('INSERT INTO ' . dbPrefix . 'schedules (id, code, flightnum, depicao, arricao, route, aircraft, flightlevel, distance, deptime, arrtime, flighttime, price, flighttype, enabled) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)',$params);
$scheduleID = $database->getLastInsertID();
$database->execute('INSERT INTO ' . dbPrefix . 'bids (bidid, pilotid, routeid, dateadded) VALUES (NULL, ?, ?, NOW())',array($dbID, $scheduleID));
$bidID = $database->getLastInsertID();
$query = $database->execute('INSERT INTO smartCARS3_CharterFlights (scheduleID, bidID, dbID) VALUES (?, ?, ?)',array($scheduleID, $bidID, $dbID));
echo(json_encode(array('success'=>$query)));
?>