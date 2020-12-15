<?php
if(!defined('API'))
    exit;
    
$query = 'SELECT pirepid as pirepID, code, submitdate as submitDate, flightnum as flightNum, depicao as departure, arricao as arrival, aircraft FROM ' . dbPrefix . 'pireps WHERE pilotid = :pilotid';
$args = array();
$args[':pilotid'] = $dbID;

if($_GET['departure'] != '' || $_GET['arrival'] != '' || $_GET['start'] != '' || $_GET['end'] != '') {
    if ($_GET['departure'] != '' && $_GET['arrival'] == '') {
        $query .= ' AND depicao = :departure';
        $args[':departure'] = $_GET['departure'];
    }
    else if ($_GET['arrival'] != '' && $_GET['departure'] == '') {
        $query .= ' AND arricao = :arrival';
        $args[':arrival'] = $_GET['arrival'];
    }
    else if ($_GET['arrival'] != '' && $_GET['departure'] != '') {
        $args[':departure'] = $_GET['departure'];
        $args[':arrival'] = $_GET['arrival'];
        $query .= ' AND depicao = :departure AND arricao = :arrival';
    }

    if ($_GET['start'] != '') {
        $query .= ' AND submitdate >= :date1';
        $args[':date1'] = $_GET['start'];
    }
    if ($_GET['end'] != '') {
        $query .= ' AND submitdate <= :date2';
        $args[':date2'] = $_GET['end'];
    }
}

if($_GET['status'] != "" && ($_GET['status'] == "1" || $_GET['status'] == "2" || $_GET['status'] == "3")) {
    $query .= " AND accepted = :status";
    if($_GET['status'] == 'accepted')
        $arg[':status'] = $_GET['status'];
    else if($_GET['status'] == 'pending')
        $arg[':status'] = "0";
    else if($_GET['status'] == 'rejected')
        $arg[':status'] = "2";
}

if (isset($_GET['aircraft'])) {
    $aircraftQuery = $database->fetch('SELECT id FROM ' . dbPrefix . 'aircraft WHERE fullname = ?', array($_GET['aircraft']));
    if (!is_array($aircraftQuery)) {
        errorOut(500, 'Unable to search aircraft');
    }
    $validAircraft = array();
    if ($aircraftQuery != array()) {
        foreach($aircraftQuery as $row) {
            array_push($validAircraft, $row['id']);
        }
        $first = true;
        $count = 0;
        foreach($validAircraft as $aircraft) {
            if ($first == true) {
                $query .= ' AND AIRCRAFT = :aircraft' . $count;
                $args[':aircraft' . $count] = $aircraft;
                $acc++;
                $first = false;
            } else {
                $query .= ' OR AIRCRAFT = :aircraft' . $count;
                $args[':aircraft' . $count] = $aircraft;
                $acc++;
            }
        }
    }
}

$query = $database->fetch($query, $args);
if (!is_array($query)) {
    errorOut(500, 'Unable to search PIREPs');
}
echo(json_encode($query));
?>