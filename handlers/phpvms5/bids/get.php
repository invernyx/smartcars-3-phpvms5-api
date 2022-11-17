<?php
if(!defined('API'))
    exit;

$flights = $database->fetch('SELECT ' . dbPrefix . 'bids.bidid as bidID, ' . dbPrefix . 'schedules.id, ' . dbPrefix . 'schedules.code, ' . dbPrefix . 'schedules.flightnum as flightNum, ' . dbPrefix . 'schedules.depicao as departure, ' . dbPrefix . 'schedules.arricao as arrival, ' . dbPrefix . 'schedules.route, ' . dbPrefix . 'schedules.aircraft, ' . dbPrefix . 'schedules.flightlevel as cruise, ' . dbPrefix . 'schedules.distance, ' . dbPrefix . 'schedules.deptime as departureTime, ' . dbPrefix . 'schedules.flighttime as flightTime, ' . dbPrefix . 'schedules.arrtime as arrivalTime,' . dbPrefix . 'schedules.daysofweek as daysOfWeek,' . dbPrefix . 'schedules.notes FROM ' . dbPrefix . 'bids LEFT JOIN ' . dbPrefix . 'schedules ON ' . dbPrefix . 'bids.routeid = ' . dbPrefix . 'schedules.id WHERE pilotid = ?', array($dbID));
if(!empty($flights))
{
    echo(json_encode($flights));
}
else
    errorOut(404, 'No flights found');

?>