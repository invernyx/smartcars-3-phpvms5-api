<?php
if(!defined('API'))
    exit;

$results = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE pilotid=?',array($_GET['uid']));
$results = $results[0];
echo(json_encode($results));
?>