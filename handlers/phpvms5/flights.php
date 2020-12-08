<?php
if(!defined('API'))
    exit;

authenticate($_SERVER['HTTP_AUTHORIZATION']);

if ($request[1] == 'create')
    require_once('flights/create.php');
else if ($request[1] == 'update')
    require_once('flights/update.php');
else if ($request[1] == 'finish')
    require_once('flights/finish.php');
?>