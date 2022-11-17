<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
assertData($_POST, array('bidID' => 'int', 'remainingLoad' => 'int', 'flightTime' => 'double', 'landingRate' => 'int', 'fuelUsed' => 'float', 'flightLog' => 'array'));

require_once('../core/common/NavData.class.php');
require_once('../core/common/ACARSData.class.php');

$flights = $database->fetch('SELECT id FROM ' . dbPrefix . 'acarsdata WHERE pilotid=?', array($pilotID));
if($flights === array())
{
    error(404, 'There is no ongoing flight');
    exit;
}
$bids = $database->fetch('SELECT bidid FROM ' . dbPrefix . 'bids WHERE bidid=? AND pilotid=?', array($_POST['bidID'], $pilotID));
if($bids === array())
{
    error(404, 'There is no ongoing flight');
    exit;
}
$route = $database->fetch('SELECT code, flightnum, depicao, arricao, route, aircraft FROM ' . dbPrefix . 'schedules WHERE id=?', $bids[0]['routeid']);
$data = array(
    'pilotid' => $pilotID,
    'code' => $route[0]['code'],
    'flightnum' => $route[0]['flightnum'],
    'depicao' => $route[0]['depicao'],
    'arricao' => $route[0]['arricao'],
    'route' => $route[0]['route'],
    'aircraft' => $route[0]['aircraft'],
    'load' => $_POST['remainingLoad'],
    'flighttime' => $_POST['flightTime'],
    'landingrate' => $_POST['landingRate'],
    'submitdate' => date('Y-m-d H:i:s'),
    'fuelused' => $_POST['fuelUsed'],
    'source' => 'smartCARS 3',
    'log' => implode('*', $_POST['flightLog'])
);
if($_POST['comments'] !== null)
    $data['comment'] = $_POST['comment'];
else
    $data['comment'] = '';

$PIREPSubmission = ACARSData::FilePirep($pilotID, $data);
if($PIREPSubmission === false)
{
    error(500, 'Failed submitting PIREP');
    exit;
}
?>