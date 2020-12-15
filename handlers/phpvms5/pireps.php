<?php
if(!defined('API'))
    exit;

if ($request[1] == 'search')
    require_once('pireps/search.php');
else if ($request[1] == 'details')
    require_once('pireps/details.php');
?>