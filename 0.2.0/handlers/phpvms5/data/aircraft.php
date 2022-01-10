<?php
// Correction will be required to make minimum rank a string instead of int
echo(json_encode($database->fetch('SELECT id,icao as code,fullname as name,cruise as serviceCeiling,registration,maxpax as maximumPassengers,maxcargo as maximumCargo,minrank as minimumRank FROM ' . dbPrefix . 'aircraft WHERE enabled = 1')));
?>