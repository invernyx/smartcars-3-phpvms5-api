<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!function_exists(http_response_code)) {
    function http_response_code($code) {
        header('X-PHP-Response-Code: ' . $code, true, $code);
    }
}

define('API', true);
header('Content-type: application/json');

$var = explode('?', $_SERVER['REQUEST_URI']);
$request = explode('/', $var[0]);

if($request[0] == '')
{
    array_splice($request, 0, 1);
}
while(count($request) > 0 && (strtolower($request[0]) == 'smartcars' || strtolower($request[0]) == 'api'))
{
    array_splice($request, 0, 1);
}
    
function assertData($source, $data)
{
    $invalid = array();

    foreach($data as $dataname => $datatype)
    {
        $valid = false;

        if(isset($source[$dataname]))
        {
            switch(strtolower($datatype))
            {
                case 'number':
                    if(is_numeric($source[$dataname]))
                        $valid = true;
                    break;
                default:
                    $valid = true;
            }
        }

        if($valid == false)
            array_push($invalid, $dataname);
    }

    if(count($invalid) > 0)
    {
        $msg = 'Invalid type(s) or missing data for: ';
        $first = true;
        foreach ($invalid as $invdata)
        {
            if ($first == true)
            {
                $msg .= $invdata;
                $first = false;
            }
            else
                $msg .= ', ' . $invdata;
        }
        http_response_code(400);
        echo(json_encode(array('message'=>$msg)));
        exit;
    }
}

function authenticate($headers) {
    global $database;
    $header = explode(':', $headers);
    if (sizeof($header) > 1) {
        http_response_code(400);
        echo(json_encode(array('message'=>'Bad headers provided')));
        exit;
    }
    $dbid = $header[0];
    $session = $header[1];
    if ($database->fetch('SELECT * FROM smartCARS3Sessions WHERE dbID=? AND sessionID=?', array($dbid, $session)) != array()) {
        return true;
    }
    http_response_code(401);
    echo(json_encode(array('message'=>'Login information incorrect')));
    exit;
}

if(count($request) > 0)
{
    $str = "";
    foreach($request as $req)
    {
        if($str != "")
            $str .= "/";
        $str .= $req;
    }
    
    require('resources/database.php');
    $database = null;
    require_once('handlers/' . $request[0] . '/environment.php');
    try
    {
        $database = new database(dbName, dbHost, dbUsername, dbPassword);
    }
    catch (Exception $e) {}

    if ($database == null)
    {
        http_response_code(400);
        echo(json_encode(array('message'=>'Database credentials were not able to be loaded')));
        exit;
    }

    if (count($request) == 1)
    {
        echo(json_encode(array('version'=>sC3Version)));
        exit;
    }
        
    if(count($request) < 2 || strtolower($request[0]) != "pilot" || strtolower($request[1]) != "login")
    {
        //authenticate($_SERVER['HTTP_AUTHORIZATION']); //uncomment to restrict access
    }

    require('handlers/' . $str . '.php');
}
else
    die('{}');

?>