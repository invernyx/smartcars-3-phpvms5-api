<?php
if(!defined('API'))
    exit;

$flights = $database->fetch('SELECT ' . dbPrefix . 'bids.bidid, ' . dbPrefix . 'schedules.id, ' . dbPrefix . 'schedules.code, ' . dbPrefix . 'schedules.flightnum, ' . dbPrefix . 'schedules.depicao, ' . dbPrefix . 'schedules.arricao, ' . dbPrefix . 'schedules.route, ' . dbPrefix . 'schedules.aircraft, ' . dbPrefix . 'schedules.flightlevel, ' . dbPrefix . 'schedules.distance, ' . dbPrefix . 'schedules.deptime, ' . dbPrefix . 'schedules.flighttime, ' . dbPrefix . 'schedules.arrtime,' . dbPrefix . 'schedules.daysofweek,' . dbPrefix . 'schedules.notes FROM ' . dbPrefix . 'bids LEFT JOIN ' . dbPrefix . 'schedules ON ' . dbPrefix . 'bids.routeid = ' . dbPrefix . 'schedules.id WHERE pilotid = ?', array($dbID));
if(!empty($flights))
{
    echo(json_encode($flights));
}
else
    errorOut(404, 'No flights found');

?>