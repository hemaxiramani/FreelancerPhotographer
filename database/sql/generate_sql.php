<?php
/**
 * GeoNames Data → SQL Generator
 *
 * Reads GeoNames dump files and generates INSERT SQL files for:
 *   countries.sql (~250 rows)
 *   states.sql   (~3,800 rows)
 *   cities.sql   (~25,000+ rows)
 *
 * Usage: php generate_sql.php
 * Then import the 3 SQL files via phpMyAdmin.
 */

$countryFile = 'C:/Users/nilesh.n/AppData/Local/Temp/countryInfo.txt';
$statesFile  = 'D:/Workspace/flutter/photoapp/admin1CodesASCII.txt';
$citiesFile  = 'C:/Users/nilesh.n/AppData/Local/Temp/cities15000.txt';

$outputDir = __DIR__;

// ─────────────────────────────────────────────────
// 1. COUNTRIES
// ─────────────────────────────────────────────────
echo "Processing countries...\n";

$countries = [];     // iso2 => ['name' => ..., 'db_id' => ...]
$countryId = 1;

$lines = file($countryFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$sql = "-- GeoNames Countries Import\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Source: geonames.org/export/dump/countryInfo.txt\n\n";
$sql .= "INSERT INTO `countries` (`id`, `name`, `iso2`, `status`, `created_at`) VALUES\n";

$values = [];
foreach ($lines as $line) {
    if (str_starts_with($line, '#')) continue;

    $cols = explode("\t", $line);
    if (count($cols) < 5) continue;

    $iso2 = trim($cols[0]);
    $name = trim($cols[4]);

    if (empty($iso2) || empty($name) || strlen($iso2) !== 2) continue;

    // Skip dissolved countries
    if (in_array($iso2, ['CS', 'AN'])) continue;

    $countries[$iso2] = ['name' => $name, 'db_id' => $countryId];
    $safeName = addslashes($name);
    $values[] = "($countryId, '$safeName', '$iso2', 1, NOW())";
    $countryId++;
}

$sql .= implode(",\n", $values) . ";\n";
file_put_contents("$outputDir/countries.sql", $sql);
echo "  → " . count($countries) . " countries written to countries.sql\n";

// ─────────────────────────────────────────────────
// 2. STATES / PROVINCES
// ─────────────────────────────────────────────────
echo "Processing states...\n";

$states = [];        // "ISO2.ADMIN1" => db_id
$stateId = 1;

$lines = file($statesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$sql = "-- GeoNames States/Provinces Import\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Source: geonames.org/export/dump/admin1CodesASCII.txt\n\n";

// Process in batches for phpMyAdmin compatibility
$batchSize = 500;
$values = [];
$batchCount = 0;

foreach ($lines as $line) {
    $cols = explode("\t", $line);
    if (count($cols) < 3) continue;

    $code     = trim($cols[0]); // e.g., "IN.25"
    $nameUtf  = trim($cols[1]); // name with diacritics
    $nameAscii = trim($cols[2]); // ASCII name

    $parts = explode('.', $code, 2);
    if (count($parts) !== 2) continue;

    $countryIso = $parts[0];
    $adminCode  = $parts[1];

    // Skip if country not in our list
    if (!isset($countries[$countryIso])) continue;

    $countryDbId = $countries[$countryIso]['db_id'];
    $safeName = addslashes($nameAscii ?: $nameUtf);
    $safeCode = addslashes($adminCode);

    $states[$code] = $stateId;
    $values[] = "($stateId, $countryDbId, '$safeName', '$safeCode', 1, NOW())";
    $stateId++;

    if (count($values) >= $batchSize) {
        if ($batchCount === 0) {
            $sql .= "INSERT INTO `states` (`id`, `country_id`, `name`, `state_code`, `status`, `created_at`) VALUES\n";
        } else {
            $sql .= "\n\nINSERT INTO `states` (`id`, `country_id`, `name`, `state_code`, `status`, `created_at`) VALUES\n";
        }
        $sql .= implode(",\n", $values) . ";\n";
        $values = [];
        $batchCount++;
    }
}

if (!empty($values)) {
    if ($batchCount === 0) {
        $sql .= "INSERT INTO `states` (`id`, `country_id`, `name`, `state_code`, `status`, `created_at`) VALUES\n";
    } else {
        $sql .= "\n\nINSERT INTO `states` (`id`, `country_id`, `name`, `state_code`, `status`, `created_at`) VALUES\n";
    }
    $sql .= implode(",\n", $values) . ";\n";
}

file_put_contents("$outputDir/states.sql", $sql);
echo "  → " . (count($states)) . " states written to states.sql\n";

// ─────────────────────────────────────────────────
// 3. CITIES (population > 15,000)
// ─────────────────────────────────────────────────
echo "Processing cities...\n";

$lines = file($citiesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$sql = "-- GeoNames Cities Import (population > 15,000)\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Source: geonames.org/export/dump/cities15000.txt\n\n";

$cityId = 1;
$values = [];
$batchCount = 0;
$skippedNoState = 0;
$skippedNoCountry = 0;

foreach ($lines as $line) {
    $cols = explode("\t", $line);
    if (count($cols) < 15) continue;

    $name        = trim($cols[1]);  // city name
    $countryIso  = trim($cols[8]);  // country ISO2
    $admin1Code  = trim($cols[10]); // state/province admin1 code
    $population  = (int) trim($cols[14]);

    if (empty($name) || empty($countryIso)) continue;

    // Skip if country not in our list
    if (!isset($countries[$countryIso])) {
        $skippedNoCountry++;
        continue;
    }

    // Find the state using "ISO2.ADMIN1" key
    $stateKey = "$countryIso.$admin1Code";
    if (!isset($states[$stateKey])) {
        $skippedNoState++;
        continue;
    }

    $stateDbId = $states[$stateKey];
    $safeName = addslashes($name);

    $values[] = "($cityId, $stateDbId, '$safeName', 0, 1, NOW())";
    $cityId++;

    if (count($values) >= $batchSize) {
        if ($batchCount === 0) {
            $sql .= "INSERT INTO `cities` (`id`, `state_id`, `name`, `is_user_added`, `status`, `created_at`) VALUES\n";
        } else {
            $sql .= "\n\nINSERT INTO `cities` (`id`, `state_id`, `name`, `is_user_added`, `status`, `created_at`) VALUES\n";
        }
        $sql .= implode(",\n", $values) . ";\n";
        $values = [];
        $batchCount++;
    }
}

if (!empty($values)) {
    if ($batchCount === 0) {
        $sql .= "INSERT INTO `cities` (`id`, `state_id`, `name`, `is_user_added`, `status`, `created_at`) VALUES\n";
    } else {
        $sql .= "\n\nINSERT INTO `cities` (`id`, `state_id`, `name`, `is_user_added`, `status`, `created_at`) VALUES\n";
    }
    $sql .= implode(",\n", $values) . ";\n";
}

file_put_contents("$outputDir/cities.sql", $sql);
$totalCities = $cityId - 1;
echo "  → $totalCities cities written to cities.sql\n";
echo "  → Skipped: $skippedNoCountry (no matching country), $skippedNoState (no matching state)\n";

// ─────────────────────────────────────────────────
// SUMMARY
// ─────────────────────────────────────────────────
echo "\n=== DONE ===\n";
echo "Files generated in: $outputDir\n";
echo "  countries.sql → " . count($countries) . " rows\n";
echo "  states.sql    → " . (count($states)) . " rows\n";
echo "  cities.sql    → $totalCities rows\n";
echo "\nNext: Import these 3 files via phpMyAdmin (in order: countries → states → cities)\n";
