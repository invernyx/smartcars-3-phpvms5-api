<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$database->createTable('smartCARS3_FlightData', 'pilotID int(11) NOT NULL, pirepID int(11) NOT NULL, locations blob NOT NULL, log blob NOT NULL, PRIMARY KEY (pilotID, pirepID)');

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
assertData($_POST, array('bidID' => 'int', 'remainingLoad' => 'int', 'flightTime' => 'double', 'landingRate' => 'int', 'fuelUsed' => 'float', 'flightLog' => 'array', 'flightData' => 'array'));

require_once('../core/common/NavData.class.php');
require_once('../core/common/ACARSData.class.php');

$flights = $database->fetch('SELECT id FROM ' . dbPrefix . 'acarsdata WHERE pilotid=?', array($pilotID));
if($flights === array())
{
    error(404, 'There is no ongoing flight');
    exit;
}
$bids = $database->fetch('SELECT routeid FROM ' . dbPrefix . 'bids WHERE bidid=? AND pilotid=?', array($_POST['bidID'], $pilotID));
if($bids === array())
{
    error(404, 'There is no ongoing flight');
    exit;
}
$route = $database->fetch('SELECT code, flightnum, depicao, arricao, route, aircraft FROM ' . dbPrefix . 'schedules WHERE id=?', array($bids[0]['routeid']));
$data = array(
    'pilotid' => $pilotID,
    'code' => $route[0]['code'],
    'flightnum' => $route[0]['flightnum'],
    'depicao' => $route[0]['depicao'],
    'arricao' => $route[0]['arricao'],
    'route' => $route[0]['route'],
    'aircraft' => $route[0]['aircraft'],
    'load' => $_POST['remainingLoad'],
    'flighttime' => sprintf('%02d:%02d', floor($_POST['flightTime']), round(($_POST['flightTime'] - floor($_POST['flightTime'])) * 60)),
    'landingrate' => $_POST['landingRate'],
    'submitdate' => date('Y-m-d H:i:s'),
    'fuelused' => $_POST['fuelUsed'],
    'source' => 'smartCARS 3',
    'log' => implode('*', $_POST['flightLog'])
);
if($_POST['comments'] !== null)
    $data['comment'] = $_POST['comments'];
else
    $data['comment'] = '';

$PIREPSubmission = ACARSData::FilePirep($pilotID, $data);
if($PIREPSubmission === false)
{
    error(500, 'Failed submitting PIREP');
    exit;
}

$pirepID = $database->fetch('SELECT pirepid FROM ' . dbPrefix . 'pireps WHERE pilotid=? ORDER BY submitdate DESC LIMIT 1', array($pilotID));
if($pirepID === array())
{
    error(500, 'Failed submitting PIREP');
    exit;
}
$pirepID = $pirepID[0]['pirepid'];

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