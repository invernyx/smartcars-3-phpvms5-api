<?php
// smartCARS 0.3.6 API
// phpVMS v7 handler
// Designed to be run on PHP 7+
$envFilepath = __DIR__ . "/../../../../../.env";

if (is_file($envFilepath)) {
    $file = new \SplFileObject($envFilepath);
    // Loop until we reach the end of the file.
    while (false === $file->eof()) {
        // Get the current line value, trim it and save by putenv.
        $line = trim($file->fgets());

        if (strpos($line, '#') === 0) {
            continue;
        }

        $str_array = explode('=', $line);
        if (count($str_array) < 2) {
            continue;
        }
        $key = $str_array[0];
        
        $value = implode('=', array_slice($str_array, 1));
        $value = str_replace('"', '', $value);
        $value = str_replace("'", '', $value);

        putenv($key . '=' . $value);
    }
} else {
    echo "No .env file found";
}

define('webRoot', dirname(dirname(getcwd())));
define('dbName', getenv('DB_DATABASE'));
define('dbHost', getenv('DB_HOST'));
define('dbUsername', getenv('DB_USERNAME'));
define('dbPassword', getenv('DB_PASSWORD'));
define('dbPrefix', getenv('DB_PREFIX'));
?>