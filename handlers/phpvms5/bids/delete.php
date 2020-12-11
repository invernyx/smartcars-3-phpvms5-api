<?php
if(!defined('API'))
    exit;

assertData($_POST, array("bidid" => "number"));

$database->execute('UPDATE ' . dbPrefix . 'schedules SET bidid = 0 WHERE bidid = ?', array($_POST['bidid']));
$database->execute('DELETE FROM ' . dbPrefix .'bids WHERE bidid = ?', array($_POST['bidid']));

$charter = $database->fetch('SELECT * FROM smartCARS3_CharterFlights WHERE bidid = ? AND dbid = ?', array($_POST['bidid'], $dbID));

if(!empty($charter))
{
    $database->execute('DELETE FROM smartCARS3_CharterFlights WHERE bidid = ? AND dbid = ?', array($_POST['bidid'], $dbID));
    $database->execute('DELETE FROM ' . dbPrefix . 'schedules WHERE id = ?', array($charter['id']));
}

?>