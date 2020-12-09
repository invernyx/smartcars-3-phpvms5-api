<?php
if(!defined('API'))
    exit;

errorOut(503, 'Work in progress');

assertData(
    array('instance' => 'number', 'latitude' => 'number', 'longitude' => 'number', 'magneticheading' => 'number', 'trueheading' => 'number', 'altitude' => 'number', 'groundspeed' => 'number', 'phase' => 'string', 'departuretime' => 'string', 'arrivaltime' => 'string', 'distanceremaining' => 'number', 'timeremaining' => 'string'),
    $_POST
);

$pilot = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE pilotid = ?', array($dbID));

if(!empty($pilot))
{    
    //WIP
    $lat = str_replace(",", ".", $latitude);
    $lon = str_replace(",", ".", $longitude);
    
    $lat = doubleval($lat);
    $lon = doubleval($lon);
    
    if($lon < 0.005 && $lon > -0.005)
        $lon = 0;
        
    if($lat < 0.005 && $lat > -0.005)
        $lat = 0;        
    
    $fields = array(
        'pilotid' =>$dbid,
        'flightnum' =>$flightnumber,
        'pilotname' => $pilot['firstname'] . " " . $pilot['lastname'],
        'aircraft' =>$aircraft,
        'lat' =>$lat,
        'lng' =>$lon,
        'heading' =>$magneticheading,
        'alt' =>$altitude,
        'gs' =>$groundspeed,
        'depicao' =>$departureicao,
        'arricao' =>$arrivalicao,
        'deptime' =>$departuretime,
        'arrtime' =>$arrivaltime,
        'route' =>$route,
        'distremain' =>$distanceremaining,
        'timeremaining' =>$timeremaining,
        'phasedetail' =>$phases[$phase],
        'online' => $onlinenetwork,
        'client' =>'smartCARS',
    );
    
    return(ACARSData::UpdateFlightData($dbid, $fields));
}
else
    errorOut(404, 'Pilot data not found');
?>