<?php

declare(strict_types=1);

/* ------------------------------------------------------------
FORECAST REPORT PROXY : forecast-proxy.php

given a query string  station=[station]  (e.g. station=klan), 
fetch the JSON file from the MAWN rawdata site.  Optionally use
a cache system to avoid repeated hits to the site. 
------------------------------------------------------------ */

/* ------------------------------------------------------------
   CONFIG
------------------------------------------------------------ */

$CACHE_ENABLED = false;
$CACHE_DIR = __DIR__ . '/cache';
$CACHE_TTL = 600; // 10 minutes, WHICH CAN BE A PROBLEM IF CACHED 1 MINUTE BEFORE

$BASE_URL = "https://mawn.geo.msu.edu/rawdata/legacy/fcsts/nbmnbs";
$DEFAULT_CONTENT_TYPE = "application/json";

/* ------------------------------------------------------------
   HELPERS
------------------------------------------------------------ */


// exits if station code is invalid (change to boolean )
function validStation(string $station): string 
{
    // EXAMPLE STATION ID = klan
    $station = strtolower($station);

    if(!preg_match('/^k[a-z]{3}$/', $station)) {
        http_response_code(404);
        exit('Invalid station ID : ' . $station);
    }
        return($station);    
}


/*
construct forecast report namne like fous16kacb or fous16kacb.json   
*/
function fcstReportName(string $station): string {

    // validate the station name, or exit
    $station = validStation($station);
    $reportName = "fous16" . $station .'.json';
    return($reportName);

}

/** 
 * construct full url using the BASE in config 
 */
function fcstReportURL(string $reportName): string 
{
    global $BASE_URL;
    // I will probably forget and add a trailing slash
    $baseURL = rtrim($BASE_URL, '/');
    return ( $baseURL . "/data/" . $reportName );

}



/* ------------------------------------------------------------
   CACHE WITH OPTIONAL CACHE CLEAR
------------------------------------------------------------ */
function cacheFilePath(string $reportName):string {
    
    global $CACHE_DIR;
    $cacheFile = $CACHE_DIR . '/' . $reportName ;
    return ($cacheFile);
}


function fetchFromCache(string $reportName): string {
    // see config above
    global $CACHE_DIR;
    global $CACHE_TTL;

    if (!is_dir($CACHE_DIR)) {
        mkdir($CACHE_DIR, 0755, true);
    }

    $cacheFile = cacheFilePath($reportName);

    if (
        isset($_GET['cache']) &&
        $_GET['cache'] === 'clear'
    ) {
        @unlink($cacheFile);
    }

    $reportText = '';
    if (
        file_exists($cacheFile) &&
        (time() - filemtime($cacheFile) < $CACHE_TTL)
    ) {
        # need exception handling here
        $reportText = file_get_contents($cacheFile);

        // previously just save it 
        // header('Content-Type: ' . $DEFAULT_CONTENT_TYPE . '; charset=utf-8');
        // header('X-Cache: HIT');
        // readfile($cacheFile);
        // exit;
    }

    return($reportText);
}

function saveToCache(string $reportName, string $reportText): bool {
    global $CACHE_DIR;

    if (!is_dir($CACHE_DIR)) {
        mkdir($CACHE_DIR, 0755, true);
    }

    $cacheFile = cacheFilePath($reportName);
    $writeResults = file_put_contents($cacheFile, $reportText);

    if ($writeResults == false){
        return false;
    } 
    return(true);
}



/* ------------------------------------------------------------
   FETCH FROM MAWN WEBSITE
------------------------------------------------------------ */

function fetchReport(string $reportName): string|NULL {
    
    $url = fcstReportURL($reportName);
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'New AgWeather Site'
    ]);

    $reportText = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        http_response_code(502);
        exit('Error contacting MAWN: ' . curl_error($ch));
    }

    // curl_close($ch);

    if ($statusCode == 404) {
        http_response_code(404);
        exit('Not Found: ' . $url);
    }

    if ($statusCode !== 200 || !$reportText) {
        http_response_code(502);
        exit('Unable to retrieve forecast from ' . $url);
    }

    return($reportText);

}




/* ------------------------------------------------------------
   INPUT
------------------------------------------------------------ */
$station = $_GET['station'] ?? '';

/* ------------------------------------------------------------
   SETUP AND FETCH
------------------------------------------------------------ */

$reportName = fcstReportName($station, 'JSON'); // exit if invalid station name
$cacheHeader = "";

if ($CACHE_ENABLED == true){
    $reportText = fetchFromCache($reportName);
    $cacheHeader = "HIT";
    if(!$reportText){
        $cacheHeader = "MISS";
        // right now, this function will exit with error codes or return some text
        $reportText = fetchReport($reportName);
        $ignore = saveToCache($reportName, $reportText);    
    } 

} else {
    // right now, this function will exit with error codes or return some text
    $reportText = fetchReport($reportName);

} 

/* ------------------------------------------------------------
   RETURN
------------------------------------------------------------ */

header('Content-Type: ' . $DEFAULT_CONTENT_TYPE .'; charset=utf-8');
if ($cacheHeader) { header('X-Cache: '. $cacheHeader); }
echo $reportText;
