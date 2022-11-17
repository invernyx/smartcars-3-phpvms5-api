<?php
if(!defined('API'))
    exit;

echo(json_encode($database->fetch('SELECT id,fullname as name,cruise as serviceCeiling,registration,maxpax as maxPassengers,maxcargo,minrank as minRank FROM ' . dbPrefix . 'aircraft WHERE enabled = 1')));
?>