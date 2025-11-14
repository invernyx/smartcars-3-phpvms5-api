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
aircraft,
CASE
    WHEN accepted=0 THEN "Pending"
    WHEN accepted=1 THEN "Accepted"
    WHEN accepted=2 THEN "Rejected"
END AS status,
flighttime as flightTime,
landingrate as landingRate,
fuelused as fuelUsed FROM ' . dbPrefix . 'pireps WHERE pilotid=:pilotid';
$parameters = array(':pilotid' => $pilotID);

$departureAirport = $_GET['departureAirport'] ?? null;
$arrivalAirport   = $_GET['arrivalAirport']   ?? null;
$startDate        = $_GET['startDate']        ?? null;
$endDate          = $_GET['endDate']          ?? null;
$status           = $_GET['status']           ?? null;
$aircraftFilter   = $_GET['aircraft']         ?? null;

if ($departureAirport !== null && $departureAirport !== '') {
    // hier lieber mit einem eigenen Array prüfen, nicht mit ganz $_GET
    assertData(['departureAirport' => $departureAirport], ['departureAirport' => 'airport']);
    $query .= ' AND depicao = :departureAirport';
    $parameters[':departureAirport'] = $departureAirport;
}

if ($arrivalAirport !== null && $arrivalAirport !== '') {
    assertData(['arrivalAirport' => $arrivalAirport], ['arrivalAirport' => 'airport']);
    $query .= ' AND arricao = :arrivalAirport';
    $parameters[':arrivalAirport'] = $arrivalAirport;
}

if ($startDate !== null && $startDate !== '') {
    assertData(['startDate' => $startDate], ['startDate' => 'date']);
    $query .= ' AND submitdate >= :startDate';
    $parameters[':startDate'] = $startDate;
}

if ($endDate !== null && $endDate !== '') {
    assertData(['endDate' => $endDate], ['endDate' => 'date']);
    $query .= ' AND submitdate <= DATE_ADD(:endDate, INTERVAL 1 DAY)';
    $parameters[':endDate'] = $endDate;
}

if ($status !== null && $status !== '') {
    assertData(['status' => $status], ['status' => 'status']);
    $query .= ' AND accepted = :status';
    switch (strtolower($status)) {
        case 'accepted':
            $parameters[':status'] = 1;
            break;
        case 'pending':
            $parameters[':status'] = 0;
            break;
        case 'rejected':
            $parameters[':status'] = 2;
            break;
        default:
            error(400, 'Invalid status value');
    }
}

if ($aircraftFilter !== null && $aircraftFilter !== '') {
    assertData(['aircraft' => $aircraftFilter], ['aircraft' => 'int']);
    $query .= ' AND aircraft = :aircraft';
    $parameters[':aircraft'] = $aircraftFilter;
}

$query .= ' ORDER BY submitdate DESC LIMIT 100';

$results = $database->fetch($query, $parameters);
foreach($results as $index=>$result)
{
    // Correct datetime to digit
    $ft = (string)$result['flightTime'];
// if no decimal add ".00"
    if (!str_contains($ft, '.')) {
        $ft .= '.00';
    }
// Split
    list($hours, $minutesRaw) = explode('.', $ft);
// calculate Minutes
    $minutes = floatval(round($minutesRaw / 60, 2));
// save result
    $results[$index]['flightTime'] = intval($hours) + $minutes;
    
    // Correct submission date format
    $results[$index]['submitDate'] = date(DATE_RFC3339, strtotime($result['submitDate']));

    if(is_numeric($result['aircraft'])) {
        $results[$index]['aircraft'] = intval($result['aircraft']);
    }
}
echo(json_encode($results));
?>
