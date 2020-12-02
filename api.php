<?php
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

if(count($request) > 0)
{
    $str = "";
    foreach($request as $req)
    {
        if($str != "")
            $str .= "/";
        $str .= $req;
    }

    //eventually, validate the list
    require('handlers/' . $str . '.php');
}
else
    die('{}');

?>