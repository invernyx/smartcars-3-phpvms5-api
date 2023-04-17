<?php
$database->createTable('smartCARS3_FlightData', 'pilotID int(10) NOT NULL, pirepID varchar(36) NOT NULL, locations blob NOT NULL, log blob NOT NULL, PRIMARY KEY (pilotID, pirepID)');

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
assertData($_POST, array('bidID' => 'int', 'aircraft' => 'int', 'remainingLoad' => 'int', 'flightTime' => 'double', 'landingRate' => 'int', 'fuelUsed' => 'float', 'flightLog' => 'array'));

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

$database->execute('DELETE FROM ' . dbPrefix . 'acars WHERE id=?', array($pirepID));
$database->execute('DELETE FROM ' . dbPrefix . 'bids WHERE flight_id=? AND user_id=?', array($flightID, $pilotID));

$query = 'UPDATE ' . dbPrefix . 'pireps SET aircraft_id=?, zfw=?, flight_time=?, landing_rate=?, fuel_used=?, notes=?, status=0, updated_at=NOW()';
$parameters = array($_POST['aircraft'], $_POST['remainingLoad'], $_POST['flightTime'], $_POST['landingRate'], $_POST['fuelUsed'], $_POST['comments']);
if(isset($_POST['route']) && $_POST['route'] !== null) {
    $query .= ', route=?';
    array_push($parameters, $_POST['route']);
}
$query .= ' WHERE id=? AND user_id=?';
array_push($parameters, $pirepID, $pilotID);

$database->execute($query, $parameters);

foreach($_POST['flightLog'] as $flightLogEntry)
{
    $database->execute('INSERT INTO ' . dbPrefix . 'pirep_comments (pirep_id, user_id, comment, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', array($pirepID, $pilotID, $flightLogEntry));
}

$locationData = $database->fetch('SELECT heading, latitude, longitude FROM smartCARS3_OngoingFlights WHERE pilotID=? AND bidID=? ORDER BY timestamp DESC', array($pilotID, $_POST['bidID']));
if($locationData === array())
{
    error(500, 'Failed fetching flight data');
    exit;
}
$database->execute('INSERT INTO smartCARS3_FlightData (pilotID, pirepID, locations, log) VALUES (?, ?, ?, ?)', array($pilotID, $pirepID, gzencode(json_encode($locationData)), gzencode(json_encode($_POST['flightData']))));
$database->execute('DELETE FROM smartCARS3_OngoingFlights WHERE pilotID=? AND bidID=?', array($pilotID, $_POST['bidID']));

echo(json_encode(array('pirepID' => $pirepID)));
?>