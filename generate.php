<?php
// invoke composer autoloader
require 'vendor/autoload.php';

// utilities setup
$faker = Faker\Factory::create();
$faker->seed(1);

// ========================================================================== //
// configuration

// number of users with demographics
$numOfUsers = 50;
// number of semantic places
$numOfPlaces = 20;
// max number of location IDs generated per semantic place
$maxLocationIds = 3;
// time period that all updates will fall into
// suggest to include 24 min before your specified time
// and 15 min after
$startDateTime = new DateTime("2014-09-01 11:36:00");
$endDateTime = new DateTime("2014-09-01 12:06:00");
// multiplier for numOfUsers: to determine how many extra 'virtual' users
// to create
$extraUserFactor = 3;

// ========================================================================== //
// first let's generate demographics
$schools = array('business', 'accountancy', 'sis', 'economics', 'law', 'socsc');
$genders = array('m', 'f');

echo "# demographics.csv\n";
echo "mac-address,name,password,email,gender\n";

for ($i = 1; $i <= $numOfUsers; $i++) {
    $firstName = $faker->firstName;
    $lastName = $faker->lastName;
    echo sha1($i) . ','
        . $firstName . ' ' . $lastName . ','
        . $faker->lexify($string = '????????') . ','
        . strtolower($firstName) . '.' . strtolower($lastName) . '.'
        . $faker->numberBetween(2010, 2014) . '@'
        . $faker->randomElement($schools) . '.smu.edu.sg' . ','
        . $faker->randomElement($genders)
        . "\n";
}

echo "\n";

// ========================================================================== //
// second let's generate semantic places

echo "# location-lookup.csv\n";
echo "location-id,semantic-place\n";

$numOfLocations = 1;
// iterate through the number of semantic places
for ($i = 1; $i <= $numOfPlaces; $i++) {
    $placeName = 'SIS Level ' . $faker->numberBetween(0, 5)
        . ' Room ' . $faker->unique()->numerify("###");

    // iterate through the number of location IDs
    for ($j = 1; $j <= $faker->numberBetween(1, $maxLocationIds); $j++) {
        echo $numOfLocations . ','
            . $placeName . "\n";
        $numOfLocations++;
    }
}

echo "\n";

// ========================================================================== //
// third let's generate location updates

echo "# location.csv\n";
echo "timestamp,mac-address,location-id\n";

// iterate through the demographic data
for ($userId = 1; $userId <= $numOfUsers * $extraUserFactor; $userId++) {
    $userStartDateTime = $faker->dateTimeBetween($startDateTime, $endDateTime);
    $userEndDateTime = $faker->dateTimeBetween($userStartDateTime, $endDateTime);

    // iterate through start time to end time
    for ($date = $userStartDateTime; $date < $userEndDateTime;
            $date->add(new DateInterval('PT' . $faker->numberBetween(2, 8) . 'M' . $faker->numberBetween(0, 60) . 'S'))) {
        echo $date->format('Y-m-d\TH:i:s') . ','
            . sha1($userId) . ','
            . $faker->numberBetween(1, $numOfLocations)
            . "\n";

    }
}
