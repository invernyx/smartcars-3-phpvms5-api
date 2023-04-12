<?php
$query = 'SELECT pirepid as id,
submitdate as submitDate,
code as airlineCode,
flightnum as number,
route,
distance,
flighttype as flightType,
depicao as departureAirport,
arricao as arrivalAirport,
(SELECT icao FROM ' . dbPrefix . 'aircraft WHERE id=aircraft) as aircraft,
CASE
    WHEN accepted=0 THEN "Pending"
    WHEN accepted=1 THEN "Accepted"
    WHEN accepted=2 THEN "Rejected"
END AS status,
flighttime as flightTime,
landingrate as landingRate,
fuelused as fuelUsed FROM ' . dbPrefix . 'pireps WHERE pilotid=:pilotid';
$parameters = array(':pilotid' => $pilotID);

if($_GET['departureAirport'] !== null)
{
    assertData($_GET, array('departureAirport' => 'airport'));
    $query .= ' AND depicao = :departureAirport';
    $parameters[':departureAirport'] = $_GET['departureAirport'];
}
if($_GET['arrivalAirport'] !== null)
{
    assertData($_GET, array('arrivalAirport' => 'airport'));
    $query .= ' AND arricao = :arrivalAirport';
    $parameters[':arrivalAirport'] = $_GET['arrivalAirport'];
}
if($_GET['startDate'] !== null)
{
    assertData($_GET, array('startDate' => 'date'));
    $query .= ' AND submitdate >= :startDate';
    $parameters[':startDate'] = $_GET['startDate'];
}
if($_GET['endDate'] !== null)
{
    assertData($_GET, array('endDate' => 'date'));
    $query .= ' AND submitdate <= :endDate';
    $parameters[':endDate'] = $_GET['endDate'];
}
if($_GET['status'] !== null)
{
    assertData($_GET, array('status' => 'status'));
    $query .= ' AND accepted = :status';
    switch (strtolower($_GET['status'])) {
        case 'accepted':
            $parameters[':status'] = 1;
            break;
        case 'pending':
            $parameters[':status'] = 0;
            break;
        case 'rejected':
            $parameters[':status'] = 2;
            break;
    }
}
if($_GET['aircraft'] !== null)
{
    assertData($_GET, array('aircraft' => 'aircraft'));
    $query .= ' AND aircraft = (SELECT id FROM ' . dbPrefix . 'aircraft WHERE icao=:aircraft)';
    $parameters[':aircraft'] = $_GET['aircraft'];
}

$results = $database->fetch($query, $parameters);
if(!is_array($results))
{
    error(500, 'Unable to search for PIREPs');
    exit;
}
foreach($results as $index=>$result)
{
    // Correct datetime to digit
    $flightTime = explode('.', $result['flightTime']);
    $flightTime = intval($flightTime[0]) + floatval(round($flightTime[1] / 60, 2));
    $results[$index]['flightTime'] = $flightTime;
    // Correct submission date format
    $results[$index]['submitDate'] = date(DATE_RFC3339, $result['submitDate']);
}
echo(json_encode($results));
?>