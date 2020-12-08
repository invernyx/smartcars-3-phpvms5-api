<?php
if(!defined('API'))
    exit;
    
echo(json_encode($database->fetch('SELECT id,icao,name,lat as latitude,lng as longitude FROM ' . dbPrefix . 'airports')));
?>