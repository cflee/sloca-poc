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
$numOfPlaces = 1;
// max number of location IDs generated per semantic place
$maxLocationIds = 1;
// time period that all updates will fall into
// suggest to include 24 min before your specified time
// and 15 min after
$startDateTime = new DateTime("2014-01-03 11:46:00");
$endDateTime = new DateTime("2014-01-03 11:46:01");
// multiplier for numOfUsers: to determine how many extra 'virtual' users
// to create
$extraUserFactor = 3;

// ========================================================================== //
// first let's generate demographics
$years = array(2010, 2011, 2012, 2013, 2014);
$schools = array('accountancy', 'business', 'economics', 'sis', 'socsc', 'law');
$genders = array('m', 'f');

echo "# demographics.csv\n";
echo "mac-address,name,password,email,gender\n";
/*
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
*/

$demographicCount = 1;
$numForEachBucketCounter = 1;
foreach ($years as $year) {
    foreach ($schools as $school) {
        foreach ($genders as $gender) {
            for ($num = 1;
                    $num <= $numForEachBucketCounter;
                    $num++) {

                $firstName = str_replace("'", "", $faker->firstName);
                $lastName = str_replace("'", "", $faker->lastName);

                echo sha1($demographicCount) . ','
                    . $firstName . ' ' . $lastName . ','
                    . '12qwaszx' . ','
                    . strtolower($firstName) . '.' . strtolower($lastName) . '.'
                    . $year . '@'
                    . $school . '.smu.edu.sg' . ','
                    . $gender
                    . "\n";
                $demographicCount++;
            }

            $numForEachBucketCounter++;
        }
    }
}
$demographicCount--;
echo "\n";

// ========================================================================== //
// second let's generate semantic places

echo "# location-lookup.csv\n";
echo "location-id,semantic-place\n";

$numOfLocations = 1;
// iterate through the number of semantic places
for ($i = 1; $i <= $numOfPlaces; $i++) {
    $placeName = 'SMUSISL' . $faker->numberBetween(1, 5)
        . 'Room' . $faker->unique()->numerify("###");

    // iterate through the number of location IDs
    for ($j = 1; $j <= $faker->numberBetween(1, $maxLocationIds); $j++) {
        echo $numOfLocations . ','
            . $placeName . "\n";
        $numOfLocations++;
    }
}
$numOfLocations--;
echo "\n";

// ========================================================================== //
// third let's generate location updates

echo "# location.csv\n";
echo "timestamp,mac-address,location-id\n";

// iterate through the demographic data
/*
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
*/

for ($userId = 1; $userId <= $demographicCount; $userId++) {
    $userStartDateTime = $startDateTime;
    $userEndDateTime = $endDateTime;

    // iterate through start time to end time
    for ($date = clone $userStartDateTime;
            $date < $userEndDateTime;
            $date->add(new DateInterval('PT' . $faker->numberBetween(2, 8) . 'M' . $faker->numberBetween(0, 60) . 'S'))) {
        echo $date->format('Y-m-d H:i:s') . ','
            . sha1($userId) . ','
            . $faker->numberBetween(1, $numOfLocations)
            . "\n";

    }
}
