<?php
$database->createTable('smartCARS3_FlightData', 'pilotID int(10) NOT NULL, pirepID varchar(36) NOT NULL, locations blob NOT NULL, log blob NOT NULL, PRIMARY KEY (pilotID, pirepID)');

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
assertData($_POST, array('bidID' => 'int', 'aircraft' => 'int', 'remainingLoad' => 'int', 'flightTime' => 'double', 'landingRate' => 'int', 'fuelUsed' => 'float', 'flightLog' => 'string', 'flightData' => 'string'));
$_POST['flightLog'] = base64_decode($_POST['flightLog']);
if($_POST['flightLog'] === false)
{
    error(400, 'Invalid flight log');
    exit;
}
$_POST['flightLog'] = explode("\n", $_POST['flightLog']);

$_POST['flightData'] = base64_decode($_POST['flightData']);
if($_POST['flightData'] === false)
{
    error(400, 'Invalid flight data');
    exit;
}
$_POST['flightData'] = json_decode($_POST['flightData'], true);
if($_POST['flightData'] === null)
{
    error(400, 'Invalid flight data');
    exit;
}

$bids = $database->fetch('SELECT flight_id FROM ' . dbPrefix . 'bids WHERE id=? AND user_id=?', array($_POST['bidID'], $pilotID));
if($bids === array())
{
    error(404, 'There is no ongoing flight');
}
$flightID = $bids[0]['flight_id'];
$pireps = $database->fetch('SELECT id FROM ' . dbPrefix . 'pireps WHERE flight_id=? AND user_id=? AND status=0', array($flightID, $pilotID));
if($pireps === array())
{
    error(404, 'There is no ongoing flight');
}
$pirepID = $pireps[0]['id'];
$flights = $database->fetch('SELECT id FROM ' . dbPrefix . 'acars WHERE id=?', array($pirepID));
if($flights === array())
{
    error(404, 'There is no ongoing flight');
}
$aircraft = $database->fetch('SELECT id FROM ' . dbPrefix . 'aircraft WHERE id=?', array($_POST['aircraft']));
if($aircraft === array())
{
    error(404, 'The aircraft you selected does not exist');
}

$database->execute('UPDATE ' . dbPrefix . 'pireps SET aircraft_id=?, zfw=?, flight_time=?, landing_rate=?, fuel_used=?, status=0, submitted_at=NOW(), updated_at=NOW(), route=? WHERE id=? AND user_id=?', array($_POST['aircraft'], $_POST['remainingLoad'], $_POST['flightTime'] * 60, round($_POST['landingRate']), $_POST['fuelUsed'], implode(' ', $_POST['route']), $pirepID, $pilotID));

$database->execute('DELETE FROM ' . dbPrefix . 'bids WHERE flight_id=? AND user_id=?', array($flightID, $pilotID));

foreach($_POST['flightLog'] as $flightLogEntry)
{
    $flightLogEntry = explode('-', $flightLogEntry);
    $logTime = strtotime($flightLogEntry[0]);
    $log = $flightLogEntry[1];

    $database->execute('INSERT INTO ' . dbPrefix . 'acars (pirep_id, type, status, created_at, updated_at) VALUES (?, 2, "ONB", ?, ?)', array($pirepID, date('Y-m-d H:i:s', $logTime), date('Y-m-d H:i:s', $logTime)));
}

if($_POST['comments'] !== null) {
    $database->execute('INSERT INTO ' . dbPrefix . 'pirep_comments (pirep_id, user_id, comment, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', array($pirepID, $pilotID, $_POST['comments']));
}

$locationData = $database->fetch('SELECT heading, latitude, longitude FROM smartCARS3_OngoingFlights WHERE pilotID=? AND bidID=? ORDER BY timestamp DESC', array($pilotID, $_POST['bidID']));
if($locationData === array())
{
    error(500, 'Failed fetching flight data');
    exit;
}
$database->execute('INSERT INTO smartCARS3_FlightData (pilotID, pirepID, locations, log) VALUES (?, ?, ?, ?)', array($pilotID, $pirepID, gzencode(json_encode($locationData)), gzencode(json_encode($_POST['flightData']))));
$database->execute('DELETE FROM smartCARS3_OngoingFlights WHERE pilotID=? AND bidID=?', array($pilotID, $_POST['bidID']));

$flightAirline = $database->fetch('SELECT airline_id FROM ' . dbPrefix . 'flights WHERE id=?', array($flightID));
if($flightAirline !== array()) {
    $flightAirline = $flightAirline[0]['airline_id'];
    $database->execute('DELETE FROM ' . dbPrefix . 'airlines WHERE id=? AND name="Charter" AND active=0', array($flightAirline));
}
$database->execute('DELETE FROM ' . dbPrefix . 'flights WHERE id=? AND notes="smartCARS Charter Flight"', array($flightID));

echo(json_encode(array('pirepID' => $pirepID)));
?>
