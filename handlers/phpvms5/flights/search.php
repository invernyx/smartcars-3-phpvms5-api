<?php
if(!defined('API'))
    exit;

$departureicao = isset($_GET['departure']) ? $_GET['departure'] : "";
$arrivalicao = isset($_GET['arrival']) ? $_GET['arrival'] : "";
$mintime = isset($_GET['mintime']) ? $_GET['mintime'] : "";
$maxtime = isset($_GET['maxtime']) ? $_GET['maxtime'] : "";
$aircraft = isset($_GET['aircraft']) ? $_GET['aircraft'] : "";

$arg = array();

$param = "SELECT id,code,flightnum,depicao,arricao,route,aircraft,flightlevel,distance,deptime,flighttime,arrtime,daysofweek,notes FROM " . dbPrefix . "schedules";
if ($departureicao != "" || $arrivalicao != "" || $mintime != "" || $maxtime != "")
{    
    $arg = array();
    if ($departureicao != "" && $arrivalicao == "")
    {
        $param = $param . " WHERE depicao = :departure";
        $arg[':departure'] = $departureicao;
    }
    else if ($arrivalicao != "" && $departureicao == "")
    {
        $param = $param . " WHERE arricao = :arrival";
        $arg[':arrival'] = $arrivalicao;
    }
    else if ($arrivalicao != "" && $departureicao != "")
    {
        $arg[':departure'] = $departureicao;
        $arg[':arrival'] = $arrivalicao;
        $param = $param . " WHERE depicao = :departure AND arricao = :arrival";
    }
    else
        $param = $param . " WHERE";
    if ($mintime != "")
    {
        if ($departureicao != "" || $arrivalicao != "")
            $param = $param . " AND";
        $param = $param . " CAST(flighttime AS DECIMAL(4,2)) >= :time1";
        $arg[':time1'] = $mintime;
    }
    if ($maxtime != "")
    {
        if ($mintime != "" || $departureicao != "" || $arrivalicao != "")
            $param = $param . " AND";
        $param = $param . " CAST(flighttime AS DECIMAL(4,2)) <= :time2";
        $arg[':time2'] = $maxtime;
    }
    $param .= " AND enabled != 0";
}
else
    $param .= " WHERE enabled != 0";		

$valid_aircraft = array();
if($aircraft != "")
{	
    $acdatar = $database->fetch("SELECT * FROM " . dbPrefix . "aircraft WHERE fullname = ?", array($aircraft));    
    if(sizeof($acdatar) > 0)
    {
		foreach($acdatar as $row) {
			array_push($valid_aircraft, $row['id']);
        }  
    }
    
    if(sizeof($valid_aircraft) > 0)
    {
        $first = true;
        $acc = 0;
        foreach($valid_aircraft as $ac) {
            if($first == true) {
                $param .= " AND (aircraft = :ac" . $acc;
                $arg[':ac' . $acc] = $ac;
                $acc++;
            }
            else {
                $param .= " OR aircraft = :ac" . $acc;
                $arg[':ac' . $acc] = $ac;
                $acc++;
            }					
            $first = false;
        }
        if($acc > 0)
            $param .= ")";
    }
    else
    {
        http_response_code(404);
        echo(json_encode(array('message'=>'No flights found')));
        exit;
    }
}

$param .= " ORDER BY code, flightnum LIMIT 1001";

$flights = $database->fetch($param, $arg);
if(sizeof($flights) > 0)
{
    echo(json_encode($flights));
}
else
{
    http_response_code(404);
    echo(json_encode(array('message'=>'No flights found')));
    exit;
}
?>