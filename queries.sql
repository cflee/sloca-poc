/* find the latest update timestamp for all mac-address in this timeframe */
SELECT `mac-address`, max(`timestamp`) AS `latest-update`
FROM location
WHERE `TIMESTAMP` > '2014-09-01 11:51:00'
AND `TIMESTAMP` <= '2014-09-01 12:00:00'
GROUP BY `mac-address`
;

/* find the latest location ID for all mac-address present in this timeframe */
SELECT location.*
FROM location
INNER JOIN (
    SELECT `mac-address`, max(`timestamp`) AS `latest-update`
    FROM location
    WHERE `TIMESTAMP` > '2014-09-01 11:51:00'
    AND `TIMESTAMP` <= '2014-09-01 12:00:00'
    GROUP BY `mac-address`
) AS latest
ON location.`mac-address` = latest.`mac-address`
AND location.`TIMESTAMP` = latest.`latest-UPDATE`
ORDER BY `mac-address` ASC
;

/* find the number of mac-address in semantic places (that have occupants) on this floor in this timeframe */
/* Need a separate lookup to find out the full list of semantic places on the floor */
SELECT location_names.`semantic-place`, count(*) AS `num`
FROM location
INNER JOIN (
    SELECT `mac-address`, max(`TIMESTAMP`) AS `latest-UPDATE`
    FROM location
    WHERE `TIMESTAMP` > '2014-09-01 11:51:00'
    AND `TIMESTAMP` <= '2014-09-01 12:00:00'
    GROUP BY `mac-address`
) AS latest
ON location.`mac-address` = latest.`mac-address`
AND location.`TIMESTAMP` = latest.`latest-update`
INNER JOIN (
    SELECT `location-id`, `semantic-place`
    FROM `location-lookup`
    WHERE `semantic-place` LIKE 'SIS Level 3%'
) AS location_names
ON location.`location-id` = location_names.`location-id`
GROUP BY location_names.`semantic-place`
;

/* All semantic places on a certain level */
/* Use this in conjunction with above query, because that only reports
   on the occupied semantic places. */
SELECT `semantic-place`
FROM `location-lookup`
WHERE `semantic-place` LIKE 'SIS Level 3%'
GROUP BY `semantic-place`;

/* Breakdown of current users by year */
/*SELECT location.*, demographics.`email` */
SELECT substring(demographics.`email`, locate('@', demographics.`email`) - 4, 4) AS `year`, count(*) AS `num`
FROM location
INNER JOIN (
    SELECT `mac-address`, max(`TIMESTAMP`) AS `latest-UPDATE`
    FROM location
    WHERE `TIMESTAMP` > '2014-09-01 11:51:00'
    AND `TIMESTAMP` <= '2014-09-01 12:00:00'
    GROUP BY `mac-address`
) AS latest ON location.`mac-address` = latest.`mac-address`
INNER JOIN (
    SELECT `mac-address`, `email`
    FROM `demographics`
) AS demographics ON location.`mac-address` = `demographics`.`mac-address`
AND location.`TIMESTAMP` = latest.`latest-UPDATE`
GROUP BY `YEAR`
ORDER BY `YEAR` ASC
;

/* Breakdown of current users by year and gender */
SELECT substring(demographics.`email`, locate('@', demographics.`email`) - 4, 4) AS `YEAR`, demographics.`gender`, count(*) AS `num`
FROM location
INNER JOIN (
    SELECT `mac-address`, max(`TIMESTAMP`) AS `latest-UPDATE`
    FROM location
    WHERE `TIMESTAMP` > '2014-09-01 11:51:00'
    AND `TIMESTAMP` <= '2014-09-01 12:00:00'
    GROUP BY `mac-address`
) AS latest ON location.`mac-address` = latest.`mac-address`
INNER JOIN (
    SELECT `mac-address`, `email`, `gender`
    FROM `demographics`
) AS demographics ON location.`mac-address` = `demographics`.`mac-address`
AND location.`TIMESTAMP` = latest.`latest-UPDATE`
GROUP BY `YEAR`, `gender`
ORDER BY `year` ASC, `gender` ASC
;

/* Breakdown of current users by year, gender, and school */
SELECT substring(demographics.`email`, locate('@', demographics.`email`) - 4, 4) AS `year`, demographics.`gender`, substring(demographics.`email`, locate('@', demographics.`email`) + 1, locate('.', demographics.`email`, locate('@', demographics.`email`)) - locate('@', demographics.`email`) - 1) AS `school`, count(*) AS `num`
FROM location
INNER JOIN (
    SELECT `mac-address`, max(`TIMESTAMP`) AS `latest-UPDATE`
    FROM location
    WHERE `TIMESTAMP` > '2014-09-01 11:51:00'
    AND `TIMESTAMP` <= '2014-09-01 12:00:00'
    GROUP BY `mac-address`
) AS latest ON location.`mac-address` = latest.`mac-address`
INNER JOIN (
    SELECT `mac-address`, `email`, `gender`
    FROM `demographics`
) AS demographics ON location.`mac-address` = `demographics`.`mac-address`
AND location.`TIMESTAMP` = latest.`latest-UPDATE`
GROUP BY `YEAR`, `gender`, `school`
ORDER BY `YEAR` ASC, `school` ASC, `gender` ASC
;

/*
HOWEVER, note that it is probably a better idea to have a single DAO method
that allows you to specify up to 3 of the parameters, then just call when
each is required.
This creates more flexibility in determining which variable to 'drill down'
into, and also less of a hassle in doing aggregation application side. This
way it will be still doing the various level of aggregation in SQL.
*/
/* But building hideously complicated individual queries is better to troll with.. */

/* Find occupancy of all semantic places in building */
/* Note that this does not include unoccupied semantic places..
   Will we hit a situation when all the semantic places share one of the ranks? */
SELECT location_names.`semantic-place`, count(*) AS `num`
FROM location
INNER JOIN (
    SELECT `mac-address`, max(`TIMESTAMP`) AS `latest-UPDATE`
    FROM location
    WHERE `TIMESTAMP` > '2014-09-01 11:51:00'
    AND `TIMESTAMP` <= '2014-09-01 12:00:00'
    GROUP BY `mac-address`
) AS latest
ON location.`mac-address` = latest.`mac-address`
AND location.`TIMESTAMP` = latest.`latest-update`
INNER JOIN (
    SELECT `location-id`, `semantic-place`
    FROM `location-lookup`
) AS location_names
ON location.`location-id` = location_names.`location-id`
GROUP BY location_names.`semantic-place`
ORDER BY `num` DESC
;

/* See the days that are included in data set */
SELECT DATE(`timestamp`) AS `dateOnly`, count(*)
FROM location2
GROUP BY `dateOnly`;

/* see the number of location updates in this period */
SELECT *
FROM location2
WHERE `timestamp` > '2014-03-25 11:36:00'
AND `timestamp` <= '2014-03-25 12:00:00'
;

/* See the number of unique devices in this period */
SELECT location.`timestamp`, location.`mac-address`, location.`location-id`, places.`semantic-place`
FROM location2 AS location
INNER JOIN (
    SELECT `mac-address`, max(`TIMESTAMP`) AS `latest-UPDATE`
    FROM location2
    WHERE `TIMESTAMP` > '2014-03-25 15:51:00'
    AND `TIMESTAMP` <= '2014-03-25 16:00:00'
    GROUP BY `mac-address`
) AS latest
ON location.`mac-address` = latest.`mac-address` AND location.`TIMESTAMP` = latest.`latest-UPDATE`
INNER JOIN (
    SELECT `location-id`, `semantic-place`
    FROM `location-lookup2`
    WHERE `semantic-place` LIKE 'SMUSISL%'
) AS places ON location.`location-id` = places.`location-id`
ORDER BY `semantic-place` ASC
;

/* Query the row numbers, data size, index size, total size of all databases */
SELECT
        count(*) tables,
        table_schema,concat(round(sum(table_rows)/1000000,2),'M') rows,
        concat(round(sum(data_length)/(1024*1024*1024),2),'G') data,
        concat(round(sum(index_length)/(1024*1024*1024),2),'G') idx,
        concat(round(sum(data_length+index_length)/(1024*1024*1024),2),'G') total_size,
        round(sum(index_length)/sum(data_length),2) idxfrac
        FROM information_schema.TABLES
        GROUP BY table_schema
        ORDER BY sum(data_length+index_length) DESC LIMIT 10;
