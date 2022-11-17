<?php
if(!defined('API'))
    exit;

assertData($_POST, array('bidID' => 'number'));

$success = true;
if ($database->execute('UPDATE ' . dbPrefix . 'schedules SET bidID = 0 WHERE bidID = ?', array($_POST['bidID'])) != true)
    errorOut(500,'Updating schedule failed');
if ($database->execute('DELETE FROM ' . dbPrefix .'bids WHERE bidID = ?', array($_POST['bidID'])) != true)
    errorOut(500,'Deleting bid failed');

$charter = $database->fetch('SELECT * FROM smartCARS3_CharterFlights WHERE bidID = ? AND dbid = ?', array($_POST['bidID'], $dbID));

if(!empty($charter))
{
    $database->execute('DELETE FROM smartCARS3_CharterFlights WHERE bidID = ? AND dbid = ?', array($_POST['bidID'], $dbID));
    $database->execute('DELETE FROM ' . dbPrefix . 'schedules WHERE id = ?', array($charter['id']));
}

?>