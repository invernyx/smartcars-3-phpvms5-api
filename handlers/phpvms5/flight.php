<?php
if(!defined('API'))
    exit;

authenticate($_SERVER['HTTP_AUTHORIZATION']);

if ($request[1] == 'create')
    require_once('flight/create.php');
else if ($request[1] == 'update')
    require_once('flight/update.php');
else if ($request[1] == 'finish')
    require_once('flight/finish.php');
?>