<?php
$database->createTable('smartCARS3_OngoingFlights', 'timestamp TIMESTAMP DEFAULT NOW() NOT NULL, pilotID INT(11) NOT NULL, bidID INT(11) NOT NULL, heading FLOAT NOT NULL, latitude FLOAT NOT NULL, longitude FLOAT NOT NULL, PRIMARY KEY (timestamp, pilotID, bidID)');
$database->execute('DELETE FROM smartCARS3_OngoingFlights WHERE timestamp < DATE_SUB(NOW(), INTERVAL 18 HOUR)');

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
assertData($_POST, array('bidID' => 'integer'));

$bid = $database->fetch('SELECT flight_id FROM ' . dbPrefix . 'bids WHERE id=? AND user_id=?', array($_POST['bidID'], $pilotID));
if($bid === array())
{
    error(404, 'There is no ongoing flight');
}
$flightID = $bid[0]['flight_id'];
$pireps = $database->fetch('SELECT id FROM ' . dbPrefix . 'pireps WHERE flight_id=? AND user_id=? AND status=0', array($flightID, $pilotID));
if($pireps === array())
{
    error(404, 'There is no ongoing flight');
}
$pirepID = $pireps[0]['id'];

$database->execute('DELETE FROM smartCARS3_OngoingFlights WHERE pilotID = ? AND bidID = ?', array($pilotID, $_POST['bidID']));
$database->execute('UPDATE ' . dbPrefix . 'pireps SET state=4 WHERE id=?', array($pirepID));
?>