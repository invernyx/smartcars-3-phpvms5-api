<?php
if(!defined('API'))
    exit;
    
$host = null;
function env($name, $value) {
    if ($name == 'DB_HOST') {
        $host = $value;
    }
    return $value;
}

$config = require_once('../config.php');
$config = $config['database']['connections']['mysql'];

define('dbName', $config['database']);
define('dbHost', $config['host']);
define('dbUsername', $config['username']);
define('dbPassword', $config['password']);
define('dbPrefix', $config['prefix']);
define('sC3Version', 'phpVMS 7 Handler 0.2.2');
?>