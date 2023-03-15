<?php
$bids = $database->fetch('SELECT bidid FROM ' . dbPrefix . 'bids');
$bookings = array();
foreach($bids as $bid)
{
    array_push($bookings, $bid['bidid']);
}
echo(json_encode(array('bookings' => $bookings)));
?>