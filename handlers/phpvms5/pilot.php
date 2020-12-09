<?php
if(!defined('API'))
    exit;

if ($request[1] == 'statistics')
    require_once('pilots/statistics.php');
else if ($request[1] == 'login')
    require_once('pilots/login.php');
else if ($request[1] == 'pireps')
    require_once('pilots/pireps.php');
?>