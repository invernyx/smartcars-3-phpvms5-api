<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
if($_POST['flightID'] === null)
{
    error(400, 'flightID is a required field (type `int`)');
    exit;
}
assertData($_POST, array('flightID' => 'int'));

$schedule = $database->fetch('SELECT id FROM ' . dbPrefix . 'schedules WHERE id=?', array($_POST['flightID']));
if($schedule === array())
{
    error(404, 'A flight with this ID could not be found');
    exit;
}
$bids = $database->fetch('SELECT bidid FROM ' . dbPrefix . 'bids WHERE routeid=?', array($schedule['id']));
if($bids !== array())
{
    error(409, 'A different pilot has already booked this flight');
    exit;
}
// TODO: Rank/aircraft restriction applied here
$database->execute('INSERT INTO ' . dbPrefix . 'bids (pilotid, routeid, dateadded) VALUES (?, ?, NOW())', array($pilotID, $_POST['flightID']));

echo(json_encode(array('bidID'=>intval($database->getLastInsertID('bidid')))));
?>