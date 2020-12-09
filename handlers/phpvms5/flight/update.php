<?php
if(!defined('API'))
    exit;

assertData(
    array('latitude' => 'number', 'longitude' => 'number', 'magneticheading' => 'number', 'trueheading' => 'number', 'altitude' => 'number', 'groundspeed' => 'number', 'phase' => 'string', 'departuretime' => 'string', 'arrivaltime' => 'string', 'distanceremaining' => 'number', 'timeremaining' => 'string'),
    $_POST
);

$pilot = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE pilotid = ?', array($dbID));

if(!empty($pilot))
{

}
?>