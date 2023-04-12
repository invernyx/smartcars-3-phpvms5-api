<?php
assertData($_GET, array('id'=>'int'));

$pirep = $database->fetch('SELECT log as flightLog FROM ' . dbPrefix . 'pireps WHERE pirepid=? AND pilotid=?', array($_GET['id'], $pilotID));
if($pirep === array())
{
    error(404, 'A PIREP with this ID was not found');
    exit;
}
$pirep = $pirep[0];
$pirep['flightLog'] = str_replace('*', '\n', $pirep['flightLog']);
$pirep['flightLog'] = explode('\n', $pirep['flightLog']);
echo(json_encode($pirep));
?>