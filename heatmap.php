<?php
// invoke composer autoloader
require 'vendor/autoload.php';

// connect to DB
$pdo = new PDO('mysql:host=localhost;dbname=sloca_poc', '', '');

// create array to store output. this will end up in json_encode() later
$output = array();

// ========================================================================== //
// perform data validation
if (!isset($_GET['floor']) || $_GET['floor'] < 0 || $_GET['floor'] > 5) {
    $output['status'] = 'error';
    $output['message'][] = 'invalid floor';
}

if (!isset($_GET['date'])) {
    $parsedDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $_GET['date']);

    // if date doesn't fit this format, then it will be false
    // after that, check if it is a canonical date (all numbers are within limits)
    // by formatting it and then comparing it to the input
    if ($parsedDate === false || $parsedDate->format('Y-m-d\TH:i:s') !== $_GET['date']) {
        $output['status'] = 'error';
        $output['message'][] = 'invalid date';
    }
}

// if there are data validation errors, output and exit
if (isset($output['status']) && $output['status'] === 'error') {
    echo json_encode($output);
    exit();
} else {
    $output['status'] = 'success';
}

// ========================================================================== //
// retrieve and parse the parameters that we want
$specifiedFloor = (int) $_GET['floor'];
$specifiedDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $_GET['date']);

// retrieve all semantic places on the level
$semanticPlacesStatement = $pdo->prepare(
    "
    SELECT `semantic-place` FROM `location-lookup`
    WHERE `semantic-place` LIKE :level
    GROUP BY `semantic-place`
    "
);
$semanticPlacesStatement->execute(array('level' => 'SIS Level ' . $specifiedFloor . '%'));
$semanticPlaces = $semanticPlacesStatement->fetchAll(PDO::FETCH_COLUMN);

// retrieve number of mac-addresses in occupied semantic places
$semanticPlacesOccupiedStatement = $pdo->prepare(
    "
    SELECT location_names.`semantic-place`, count(*) as 'num'
    FROM location
    INNER JOIN (
        SELECT `mac-address`, max(`timestamp`) AS `latest-update`
        FROM location
        WHERE `TIMESTAMP` > :startTimestamp
        AND `TIMESTAMP` <= :endTimestamp
        GROUP BY `mac-address`
    ) AS latest
    ON location.`mac-address` = latest.`mac-address`
    AND location.`TIMESTAMP` = latest.`latest-update`
    INNER JOIN (
        SELECT `location-id`, `semantic-place`
        FROM `location-lookup`
        WHERE `semantic-place` LIKE :level
    ) AS location_names
    ON location.`location-id` = location_names.`location-id`
    GROUP BY location_names.`semantic-place`
    ;
    "
);
$windowStart = clone $specifiedDate;
$windowStart->sub(new DateInterval("PT9M"));
$semanticPlacesOccupiedStatement->execute(array(
    'startTimestamp' => $windowStart->format('Y-m-d H:i:s'),
    'endTimestamp' => $specifiedDate->format('Y-m-d H:i:s'),
    'level' => 'SIS Level ' . $specifiedFloor . '%'
));
$semanticPlacesOccupied = $semanticPlacesOccupiedStatement->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE);

// ========================================================================== //
// generate heatmap response

function numPeopleToCrowdDensity($num) {
    if ($num <= 0) {
        return 0;
    } else if ($num <= 2) {
        return 1;
    } else if ($num <= 5) {
        return 2;
    } else if ($num <= 10) {
        return 3;
    } else if ($num <= 20) {
        return 4;
    } else if ($num <= 30) {
        return 5;
    } else {
        return 6;
    }
}

// add output to queue for encoding later
foreach ($semanticPlaces as $key) {
    $semanticPlaceName = $key;
    $num_people = isset($semanticPlacesOccupied[$semanticPlaceName]) ? (int) $semanticPlacesOccupied[$semanticPlaceName] : 0;

    $output['heatmap'][] = array(
        'semantic-place' => $semanticPlaceName,
        'num-people' => $num_people,
        'crowd-density' => numPeopleToCrowdDensity($num_people)
    );
}

echo json_encode($output);
