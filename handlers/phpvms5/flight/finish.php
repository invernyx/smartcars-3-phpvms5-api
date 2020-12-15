<?php
if(!defined('API'))
    exit;

require_once('../core/common/NavData.class.php');
require_once('../core/common/ACARSData.class.php');

assertData(
    $_POST,
    array('instanceID'=>'number','bidID'=>'number','load'=>'number','flightTime'=>'string','landingRate'=>'number','fuelUsed'=>'number','log'=>'string')    
);

$check = $database->fetch('SELECT id FROM ' . dbPrefix . 'acarsdata WHERE id=?',array($_POST['instanceID']));
if ($check == array())
    errorOut(404,'instanceID not found');
$check = $database->fetch('SELECT bidid FROM ' . dbPrefix . 'bids WHERE bidid=?',array($_POST['bidID']));
if ($check == array())
    errorOut(404,'bidID not found');
    
$flight = $database->fetch('SELECT * FROM ' . dbPrefix . 'acarsdata WHERE id=?',array($_POST['instanceID']));
if ($flight == null)
    errorOut(500, 'Unable to search for flight');

$data = array(
    'pilotid' => $dbID,
    'code' => $flight['code'],
    'flightnum' => $flight['flightnum'],
    'depicao' => $flight['depicao'],
    'arricao' => $flight['arricao'],
    'route' => $flight['route'],
    'aircraft' => $flight['aircraft'],
    'load' => $_POST['load'],
    'flighttime' => $_POST['flightTime'],
    'landingrate' => $_POST['landingRate'],
    'submitdate' => date('Y-m-d H:i:s'),
    'fuelused'=> $_POST['fuelUsed'],
    'source'=>'smartCARS 3',
    'log' => $_POST['log']
);
if (isset($_POST['comments']))
    $data['comment'] = $_POST['comments'];
else
    $data['comment'] = '';

$return = ACARSData::FilePirep($dbID, $data);

if ($return == false)
    errorOut(500,'PIREP filing failed');

$success = true;
$query = $database->fetch('SELECT * FROM smartCARS3_CharterFlights WHERE bidID=? AND dbID=?',array($_POST['bidID'],$dbID));
if ($query[0]['scheduleID'] != '') {
    $database->execute('DELETE FROM smartCARS3_CharterFlights WHERE bidID=? AND dbID=?',array($_POST['bidID'],$dbID));
    $database->execute('DELETE FROM ' . dbPrefix . 'schedules WHERE id=?',array($query['scheduleID']));
}
if (!$database->execute('UPDATE ' . dbPrefix . 'pilots SET retired=0 WHERE pilotid=?',array($dbID)))
    $success = 'Updating pilot';

if (!$database->execute('UPDATE ' . dbPrefix . 'acarsdata SET gs=0, distremain=0, timeremaining="0:00", phasedetail="Arrived",arrtime=CURRENT_TIMESTAMP WHERE pilotid=?',array($dbID)))
    $success = 'Updating ACARS data';

if (!$database->execute('DELETE FROM ' . dbPrefix . 'bids WHERE pilotid=? AND bidid=?',array($dbID, $_POST['bidID'])))
    $success = 'Removing pilot bid';

if ($success != true)
    errorOut(500, $success . ' failed');
?>