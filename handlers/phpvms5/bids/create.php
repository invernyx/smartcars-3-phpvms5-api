<?php
if(!defined('API'))
    exit;

assertData($_POST, array('scheduleID' => 'number'));

$schedule = $database->fetch('SELECT * FROM ' . dbPrefix . 'schedules WHERE id = ?', array($_POST['scheduleID']));

if(!empty($schedule))
{
    if(oneBidPerFlight == true)
    {
        $bid = $database->fetch('SELECT * FROM ' . dbPrefix . 'bids WHERE routeid = ?', array($_POST['scheduleID']));
        if(!empty($bid))        
            errorOut(409, 'Flight bid already exists');        
    }

    $database->execute('INSERT INTO ' . dbPrefix . 'bids (pilotid, routeid, dateadded) VALUES (?, ?, NOW())', array($dbID, $_POST['scheduleID']));
    $bidID = $database->getLastInsertID();

    if(oneBidPerFlight == true)    
        $database->execute('UPDATE ' . dbPrefix . 'schedules SET bidid = ? WHERE id = ?', array($bidID, $_POST['scheduleID']));    

    echo(json_encode(array('bidID' => $bidID)));
}
else
    errorOut(404, 'Schedule not found');
?>