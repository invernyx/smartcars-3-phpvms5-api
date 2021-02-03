<?php
if (!function_exists("http_response_code")) {
    function http_response_code($code) {
        header('X-PHP-Response-Code: ' . $code, true, $code);
    }
}

define('API', true);
header('Content-type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');  
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
header('Access-Control-Allow-Origin: *');

$var = explode('?', $_SERVER['REQUEST_URI']);
$request = explode('/', $var[0]);

if($request[0] == '')
{
    array_splice($request, 0, 1);
}
while(count($request) > 0 && (strtolower($request[0]) == 'smartcars' || strtolower($request[0]) == 'api' || strtolower($request[0]) == 'api.php'))
{
    array_splice($request, 0, 1);
}

function errorOut($code, $msg, $shouldExit = true)
{
    http_response_code($code);
    echo(json_encode(array('message'=>$msg)));
    if($shouldExit == true)
        exit;
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
        
        errorOut(400, $msg);
    }
}

function authenticate($headers) {
    global $database;
    $header = explode(':', $headers);
    if (sizeof($header) > 1)    
        errorOut(400, 'Bad headers provided');        
    
    $dbid = $header[0];
    $session = $header[1];
    if ($database->fetch('SELECT * FROM smartCARS3Sessions WHERE dbID=? AND sessionID=?', array($dbid, $session)) != array()) {
        return true;
    }

    errorOut(401, 'Invalid session');    
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
        errorOut(400, 'Database credentials were not able to be loaded');        
    }

    if (count($request) == 1)
    {
        echo(json_encode(array('version'=>sC3Version)));
        exit;
    }
        
    $dbID = '';
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json != null)
                $_POST = $json;
        } catch (Exception $e) {}
    }
    
    if(count($request) < 3 || strtolower($request[1]) != "pilot" || strtolower($request[2]) != "login")
    {
        authenticate($_SERVER['HTTP_AUTHORIZATION']); //comment to unrestrict access
        $dbIDspl = explode(':', $_SERVER['HTTP_AUTHORIZATION']);
        $dbID = $dbIDspl[0];
    }

    if(file_exists('handlers/' . $str . '.php'))
        require('handlers/' . $str . '.php');
    else
        errorOut(404, 'Handler file not found');
}
else
    die('{}');

?>