<?php
if(!function_exists('gzdecode'))
{
    function gzdecode($data)
    {
        return gzinflate(substr($data, 10, -8));
    }
}

$database->createTable('smartCARS3_FlightData', 'pilotID int(11) NOT NULL, pirepID varchar(36) NOT NULL, locations blob NOT NULL, log blob NOT NULL, PRIMARY KEY (pilotID, pirepID)');

assertData($_GET, array('id'=>'string'));

$comments = $database->fetch('SELECT comment FROM ' . dbPrefix . 'pirep_comments WHERE pirep_id=? AND user_id=? ORDER BY created_at DESC', array($_GET['id'], $pilotID));
if($pirep === array())
{
    error(404, 'A PIREP with this ID was not found');
    exit;
}
$pirep = array('flightLog' => array_map(function($comment) { return $comment['comment']; }, $comments));

$flightData = $database->fetch('SELECT locations, log FROM smartCARS3_FlightData WHERE pilotID=? AND pirepID=?', array($pilotID, $_GET['id']));
if($flightData === array()) {
    $pirep['locationData'] = null;
    $pirep['flightData'] = null;
}
else {
    $pirep['locationData'] = json_decode(gzdecode($flightData[0]['locations']));
    $pirep['flightData'] = json_decode(gzdecode($flightData[0]['log']));
}
echo(json_encode($pirep));
?>
