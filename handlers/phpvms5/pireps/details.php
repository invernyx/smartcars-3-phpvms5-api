<?php
assertData($_GET, array('id'=>'number'));

$PIREP = $database->fetch('SELECT flighttime as flightTime, landingrate as landingRate, fuelused as fuelUsed, accepted, log FROM ' . dbPrefix . 'pireps WHERE pirepid=?',array($_GET['id']));
if ($PIREP == array())
    errorOut(404,'PIREP not found');

$PIREP = $PIREP[0];
$PIREP['log'] = explode('*',$PIREP['log']);
switch ($PIREP['accepted']) {
    case 0:
        $PIREP['status'] = 'Pending';
        break;
    case 1:
        $PIREP['status'] = 'Accepted';
        break;
    case 2:
        $PIREP['status'] = 'Rejected';
        break;
}
unset($PIREP['accepted']);
echo(json_encode($PIREP));
?>