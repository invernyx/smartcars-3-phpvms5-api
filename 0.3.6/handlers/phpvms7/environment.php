<?php
// smartCARS 0.3.6 API
// phpVMS v7 handler
// Designed to be run on PHP 7+

$settings = file_get_contents('../../env.php');

define('webRoot', dirname(dirname(getcwd())));
define('dbName', explode('\'', explode('DB_DATABASE=\'', $settings)[1])[0]);
define('dbHost', explode('\'', explode('DB_HOST=\'', $settings)[1])[0]);
define('dbUsername', explode('\'', explode('DB_USERNAME=\'', $settings)[1])[0]);
define('dbPassword', explode('\'', explode('DB_PASSWORD=\'', $settings)[1])[0]);
define('dbPrefix', explode('\'', explode('DB_PREFIX=\'', $settings)[1])[0]);
?>