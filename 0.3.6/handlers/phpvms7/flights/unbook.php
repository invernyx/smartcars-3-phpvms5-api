<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
assertData($_POST, array('bidID'=>'int'));
if($database->fetch('SELECT id FROM ' . dbPrefix . 'bids WHERE user_id=? AND id=?', array($pilotID, $_POST['bidID'])) !== array())
{
    $database->execute('DELETE FROM ' . dbPrefix . 'bids WHERE user_id=? AND id=?', array($pilotID, $_POST['bidID']));
}
else
{
    error(404, 'No bids with the given ID were found');
    exit;
}
?>