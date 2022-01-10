<?php
// smartCARS 0.2.0 API
// This file must be processable by both PHP 5 and PHP 7

header('Content-type: application/json');
if(!function_exists("http_response_code"))
{
    function http_response_code($code)
    {
        header('X-PHP-Response-Code: ' . $code, true, $code);
    }
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS, HEAD');  
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
header('Access-Control-Allow-Origin: *');

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS' || $_SERVER['REQUEST_METHOD'] === 'HEAD')
    exit;

$urlNoQuery = explode('?', $_SERVER['REQUEST_URI']);
$requestURL = explode('/', $urlNoQuery[0]);
if($requestURL[0] === '')
    array_splice($requestURL, 0, 1);
while(count($requestURL) > 0 && (strtolower($requestURL[0]) === 'smartcars' || strtolower($requestURL[0]) === 'newsc' || strtolower($requestURL[0]) === 'api' || strtolower($requestURL[0]) === 'api.php'))
{
    array_splice($requestURL, 0, 1);
}

function error($httpCode, $message, $exit = true)
{
    http_response_code($httpCode);
    echo(json_encode(array('message' => $message)));
    if($exit)
        exit;
}

function assertData($source, $data)
{
    $invalidData = array();
    foreach($data as $name => $type)
    {
        $valid = false;
        if(isset($source[$name]))
        {
            switch(strtolower($type))
            {
                case 'integer':
                case 'int':
                    if(is_int($source[$name]))
                        $valid = true;
                    break;
                case 'float':
                    if(is_float($source[$name]))
                        $valid = true;
                    break;
                case 'string':
                    if(is_string($source[$name]))
                        $valid = true;
                    break;
                case 'email':
                    if(filter_var($source[$name], FILTER_VALIDATE_EMAIL))
                        $valid = true;
                    break;
                case 'flight':
                case 'flightNumber':
                    if(preg_match('^[A-Z]{3}[A-Z0-9]{1,}$', $source[$name]))
                        $valid = true;
                    break;
                case 'airport':
                    if(preg_match('^[A-Z]{4}$', $source[$name]))
                        $valid = true;
                    break;
                case 'aircraft':
                    if(preg_match('^[A-Z]{1}[A-Z0-9]{1,3}$', $source[$name]))
                        $valid = true;
                    break;
                case 'airline':
                    if(preg_match('^[A-Z]{3}$', $source[$name]))
                        $valid = true;
                    break;
                case 'route':
                case 'routePoint':
                case 'waypoint':
                    if(preg_match('^((0?[1-9]|[1-2]\\d|3[0-6])[LCR]?)|([A-Z]{5})|([A-Z]{3})|([A-Z]{1-3})$', $source[$name]))
                        $valid = true;
                    break;
                case 'phase':
                    if(preg_match('^BOARDING|PUSH_BACK|TAXI|TAKE_OFF|REJECTED_TAKE_OFF|CLIMB_OUT|CLIMB|CRUISE|DESCENT|APPROACH|FINAL|LANDED|GO_AROUND|TAXI_TO_GATE|DEBOARDING|DIVERTED$', $source[$name]))
                        $valid = true;
                    break;
                case 'network':
                    if(preg_match('^Offline|VATSIM|PilotEdge|IVAO|POSCON$', $source[$name]))
                        $valid = true;
                    break;
            }
        }
        if(!$valid)
            array_push($invalidData, $name . ' (expected `' . $type . '`)');
    }

    if(count($invalidData) > 0)
    {
        $message = 'Invalid ';
        if(count($invalidData) > 1)
            $message .= 'types for ';
        else
            $message .= 'type for ';

        $firstItem = true;
        foreach($invalidData as $data)
        {
            if($firstItem)
            {
                $message .= $data;
                $firstItem = false;
            }
            else
                $message .= ', ' . $data;
        }
        error(400, $message);
    }
}

if(count($requestURL) > 0)
{
    $defaultVersion = '0.2.0';
    $apiVersion = $defaultVersion;
    if($_GET['v'] !== null)
    {
        $apiVersion = $_GET['v'];
    }
    if(!file_exists($apiVersion . '/handlers/' . $requestURL[0] . '/environment.php')) {
        $apiVersion = $defaultVersion;
    }
    require_once($apiVersion . '/handlers/' . $requestURL[0] . '/environment.php');

    if(count($requestURL) === 1)
    {
        echo(json_encode(array('apiVersion' => $apiVersion, 'handler' => $requestURL[0])));
        exit;
    }

    require_once($apiVersion . '/handlers/' . $requestURL[0] . '/assets/database.php'); // Database import
    try
    {
        // I really don't like how these are constants and not variables
        $database = new database(dbName, dbHost, dbUsername, dbPassword);
    }
    catch (Exception $e)
    {
        error(500, 'Database credentials could not be loaded');
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        try
        {
            $json = json_decode(file_get_contents('php://input'), true);
            if($json !== null)
                $_POST = $json;
        } catch (Exception $e) {}
    }

    $authenticate = true;
    if(strtolower($requestURL[1] === 'pilot'))
    {
        if(strtolower($requestURL[2]) === 'login' || strtolower($requestURL[2] === 'resume'))
        {
            $authenticate = false;
        }
    }
    if($authenticate)
    {
        $jwtPayload = explode('.', $_SERVER['HTTP_AUTHORIZATION']);
        $jwt = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',$jwtPayload[1]))), true);
        $pilotID = $jwt['sub'];
        $sessionID = explode('Bearer ', $_SERVER['HTTP_AUTHORIZATION']);
        $sessions = $database->fetch('SELECT sessionID FROM smartCARS3_newSessions WHERE pilotID=? AND sessionID=?', array($jwt['sub'], $sessionID[1]));
        if($sessions === array())
        {
            error(401, 'The session provided is not valid');
            exit;
        }
    }

    $requiredFile = '';
    foreach($requestURL as $fileLocation)
    {
        if($requiredFile !== '')
            $requiredFile .= '/';
        $requiredFile .= $fileLocation;
    }
    if(file_exists($apiVersion . '/handlers/' . $requiredFile . '.php'))
        require_once($apiVersion . '/handlers/' . $requiredFile . '.php');
    else
        error(404, 'The handler provided could not be found');
}
?>