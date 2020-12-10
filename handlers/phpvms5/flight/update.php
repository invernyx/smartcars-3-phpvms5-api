<?php
if(!defined('API'))
    exit;

require_once("../core/common/NavData.class.php"); 
require_once("../core/common/ACARSData.class.php");

assertData(
    $_POST,
    array('instance' => 'number', 'latitude' => 'string', 'longitude' => 'string', 'magneticheading' => 'number', 'trueheading' => 'number', 'altitude' => 'number', 'groundspeed' => 'number', 'phase' => 'string', 'departuretime' => 'string', 'arrivaltime' => 'string', 'distanceremaining' => 'number', 'timeremaining' => 'string')    
);

$pilot = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE pilotid = ?', array($dbID));

if(!empty($pilot))
{       
    $flight = $database->fetch('SELECT pilotid,flightnum,pilotname,aircraft,lat,lng,heading,alt,gs,depicao,arricao,deptime,arrtime,route,distremain,timeremaining,phasedetail,online,client FROM ' . dbPrefix . 'acarsdata WHERE id = ?', array($_POST['instance']));
    if(!empty($flight) && !empty($flight[0]))
    {
        $flight = $flight[0];
        $lat = str_replace(",", ".", $_POST['latitude']);
        $lon = str_replace(",", ".", $_POST['longitude']);
        
        $lat = doubleval($lat);
        $lon = doubleval($lon);
        
        if($lon < 0.005 && $lon > -0.005)
            $lon = 0;
            
        if($lat < 0.005 && $lat > -0.005)
            $lat = 0;  

        $flight['lat'] = $lat;
        $flight['lng'] = $lon;
        $flight['alt'] = $_POST['altitude'];
        $flight['gs'] = $_POST['groundspeed'];
        $flight['heading'] = $_POST['trueheading'];
        $flight['phasedetail'] = $_POST['phase'];
        $flight['deptime'] = $_POST['departuretime'];
        $flight['arrtime'] = $_POST['arrivaltime'];
        $flight['distremain'] = $_POST['distanceremaining'];
        $flight['timeremaining'] = $_POST['timeremaining'];

        if(!ACARSData::UpdateFlightData($dbID, $flight))
            errorOut(500, 'ACARSData::UpdateFlightData failed');    
    }
    else
        errorOut(404, 'Flight instance not found');
}
else
    errorOut(404, 'Pilot data not found');
?>