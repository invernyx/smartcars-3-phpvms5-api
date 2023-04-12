<?php
$aircrafts = $database->fetch('SELECT ' . dbPrefix . 'aircraft.id, ' . dbPrefix . 'subfleets.id as subfleet_id, ' . dbPrefix . 'aircraft.icao as code, ' . dbPrefix . 'aircraft.name, ' . dbPrefix . 'aircraft.registration, ' . dbPrefix . 'subfleets.cargo_capacity as maximumCargo FROM ' . dbPrefix . 'aircraft INNER JOIN ' . dbPrefix . 'subfleets WHERE ' . dbPrefix . 'aircraft.subfleet_id = ' . dbPrefix . 'subfleets.id');
$ranks = $database->fetch('SELECT ' . dbPrefix . 'ranks.name, ' . dbPrefix . 'subfleet_rank.subfleet_id FROM ' . dbPrefix . 'ranks INNER JOIN ' . dbPrefix . 'subfleet_rank ON ' . dbPrefix . 'subfleet_rank.rank_id = ' . dbPrefix . 'ranks.id');
$typeratings = $database->fetch('SELECT ' . dbPrefix . 'typeratings.name, ' . dbPrefix . 'typerating_subfleet.subfleet_id FROM ' . dbPrefix . 'typeratings INNER JOIN ' . dbPrefix . 'typerating_subfleet ON ' . dbPrefix . 'typeratings.active = 1 AND ' . dbPrefix . 'typeratings.id = ' . dbPrefix . 'typerating_subfleet.typerating_id');
foreach($aircrafts as $index => $aircraft) {
    $minimumRank = 'N/A';
    $aircraftRank = array_filter($ranks, function($rank) use ($aircraft) {
        return $rank['subfleet_id'] == $aircraft['subfleet_id'];
    });
    $aircraftTypeRating = array_filter($typeratings, function($typerating) use ($aircraft) {
        return $typerating['subfleet_id'] == $aircraft['subfleet_id'];
    });
    
    $minimumRank = array_merge($aircraftRank, $aircraftTypeRating);
    if($minimumRank === array()) {
        $minimumRank = 'N/A';
    }
    else {
        $minimumRank = implode(', ', array_map(function($entry) {
            return $entry['name'];
        }, $minimumRank));
    }
    $aircrafts[$index]['minimumRank'] = $minimumRank;
    $aircrafts[$index]['maximumPassengers'] = 0;
    if($aircraft['maximumCargo'] === null) {
        $aircrafts[$index]['maximumCargo'] = 0;
    }
    unset($aircrafts[$index]['subfleet_id']);
}
echo(json_encode($aircrafts));
?>