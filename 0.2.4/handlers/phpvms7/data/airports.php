<?php
echo(json_encode($database->fetch('SELECT id,icao as code,name,lat as latitude,lon as longitude FROM ' . dbPrefix . 'airports')));
?>