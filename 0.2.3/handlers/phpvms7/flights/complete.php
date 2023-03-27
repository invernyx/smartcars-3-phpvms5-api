<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
assertData($_POST, array('bidID' => 'int', 'remainingLoad' => 'int', 'flightTime' => 'double', 'landingRate' => 'int', 'fuelUsed' => 'float', 'flightLog' => 'array'));

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

$database->execute('DELETE FROM ' . dbPrefix . 'acars WHERE id=?', array($pirepID));
$database->execute('DELETE FROM ' . dbPrefix . 'bids WHERE flight_id=? AND user_id=?', array($flightID, $pilotID));
$database->execute('UPDATE ' . dbPrefix . 'pireps SET zfw=?, flight_time=?, landing_rate=?, fuel_used=?, notes=?, status=0, updated_at=NOW() WHERE id=? AND user_id=?', array($_POST['remainingLoad'], $_POST['flightTime'], $_POST['landingRate'], $_POST['fuelUsed'], $_POST['comments'], $pirepID, $pilotID));
foreach($_POST['flightLog'] as $flightLogEntry)
{
    $database->execute('INSERT INTO ' . dbPrefix . 'pirep_comments (pirep_id, user_id, comment, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', array($pirepID, $pilotID, $flightLogEntry));
}
?>