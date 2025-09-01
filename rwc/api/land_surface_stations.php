<?php
// Disable error display for production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Set up error handling
$log_file = __DIR__ . '/../logs/wmo_api.log';

try {
    // Valid parameters
    $valid_variables = [
        'pressure',
        'temperature',
        'zonal_wind',
        'meridional_wind',
        'humidity'
    ];

    $valid_periods = [
        'six_hour',
        'daily',
        'monthly'
    ];

    $valid_time_periods = ['00', '06', '12', '18'];

    // Get parameters from request with yesterday as default date
    $territory = isset($_GET['territory']) ? strtoupper(trim($_GET['territory'])) : 'SGP,IDN,BRN,PHL,TLS,PNG,MYS';
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d', strtotime('-2 day'));
    $period = isset($_GET['period']) ? $_GET['period'] : 'six_hour';
    $time_period = isset($_GET['time_period']) ? $_GET['time_period'] : '00';
    $variable = isset($_GET['variable']) ? strtolower($_GET['variable']) : 'pressure';

    // Special case for ALL_COMBINED to fetch both Region V and USA territories
    $fetch_usa_territories = false;
    if ($territory === 'ALL_COMBINED') {
        $territory = 'SGP,IDN,BRN,PHL,TLS,PNG,MYS';
        $fetch_usa_territories = true;
    }

    // Special case for USA_PACIFIC to fetch all USA Pacific stations
    if ($territory === 'USA_PACIFIC') {
        $territory = 'LIH,HNL,OGG,ITO,GUM,PPG';
        $is_usa_territory = true;
    }

    // Check if we're requesting USA stations
    $usa_territories = ['LIH', 'HNL', 'OGG', 'ITO', 'GUM', 'PPG', 'USA', 'USA_PACIFIC'];
    $is_usa_territory = false;
    $territories = explode(',', $territory);
    foreach ($territories as $t) {
        if (in_array($t, $usa_territories)) {
            $is_usa_territory = true;
            break;
        }
    }

    // If ALL_COMBINED was requested, force USA territory fetch
    if ($fetch_usa_territories) {
        $is_usa_territory = true;
    }

    // Validate and correct parameters
    if (!in_array($variable, $valid_variables)) {
        $variable = 'pressure'; // Default to pressure if invalid
    }

    if (!in_array($period, $valid_periods)) {
        $period = 'six_hour'; // Default to six_hour if invalid
    }

    if (!in_array($time_period, $valid_time_periods)) {
        $time_period = '00'; // Default to 00 if invalid
    }

    // Log file setup
    $log_message = date('Y-m-d H:i:s') . " - Request started\n";
    $log_message .= "Territory: $territory\n";
    $log_message .= "Date: $date\n";
    $log_message .= "Period: $period\n";
    $log_message .= "Time Period: $time_period\n";
    $log_message .= "Variable: $variable\n";

    // Initialize response variables
    $stations = [];
    $usa_stations = []; // Store USA stations separately
    $apiStatus = '';
    $stationDataByWigosId = [];

    // If requesting USA territory, fetch from WMO API with Pacific region filter
    if ($is_usa_territory) {
        $usa_api_url = "https://wdqms.wmo.int/wdqmsapi/v1/download/gbon/synop/$period/availability/?" . 
                       "date=$date&period=$time_period&variable=$variable&centers=DWD,ECMWF,JMA,NCEP&countries=USA&region=pacific";
        
        $log_message .= "USA API URL: $usa_api_url\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        
        // Initialize cURL for USA data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $usa_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!$error && $httpCode == 200) {
            // Process CSV response
            $lines = explode("\n", trim($response));
            if (count($lines) > 1) {
                $headers = str_getcsv($lines[0]);
                
                // Filter for specific Hawaii and Guam stations
                $target_stations = [
                    'LIHUE, KAUAI, HAWAII',
                    'HONOLULU, OAHU, HAWAII',
                    'KAHULUI AIRPORT, MAUI, HAWAII',
                    'HILO HI, HAWAII',
                    'WEATHER FORECAST OFFICE, GUAM, MARIANA IS.',
                    'PAGO PAGO/INT.AIRP. AMERICAN SAMOA'
                ];
                
                // Process all rows
                for ($i = 1; $i < count($lines); $i++) {
                    if (!empty(trim($lines[$i]))) {
                        $row = str_getcsv($lines[$i]);
                        if (count($row) >= count($headers)) {
                            $stationData = array_combine($headers, $row);
                            
                            // Only process target stations
                            if (in_array($stationData['name'], $target_stations)) {
                                $wigosId = $stationData['wigosid'];
                                $center = $stationData['center'] ?? 'Unknown';
                                
                                // Initialize station data if first time seeing this station
                                if (!isset($stationDataByWigosId[$wigosId])) {
                                    // Map station name to territory code
                                    $territoryCode = '';
                                    switch ($stationData['name']) {
                                        case 'LIHUE, KAUAI, HAWAII':
                                            $territoryCode = 'LIH';
                                            break;
                                        case 'HONOLULU, OAHU, HAWAII':
                                            $territoryCode = 'HNL';
                                            break;
                                        case 'KAHULUI AIRPORT, MAUI, HAWAII':
                                            $territoryCode = 'OGG';
                                            break;
                                        case 'HILO HI, HAWAII':
                                            $territoryCode = 'ITO';
                                            break;
                                        case 'WEATHER FORECAST OFFICE, GUAM, MARIANA IS.':
                                            $territoryCode = 'GUM';
                                            break;
                                        case 'PAGO PAGO/INT.AIRP. AMERICAN SAMOA':
                                            $territoryCode = 'PPG';
                                            break;
                                    }
                                    
                                    $stationDataByWigosId[$wigosId] = [
                                        'name' => $stationData['name'],
                                        'wigosId' => $wigosId,
                                        'countryCode' => $stationData['country code'],
                                        'inOSCAR' => $stationData['in OSCAR'],
                                        'latitude' => (float)($stationData['latitude']),
                                        'longitude' => (float)($stationData['longitude']),
                                        'territory' => $territoryCode,
                                        'territoryCode' => $territoryCode,
                                        'stationTypeName' => 'SYNOP',
                                        'variable' => $variable,
                                        'date' => $date,
                                        'expected' => intval($stationData['#expected (GBON)']),
                                        'centers' => [
                                            'DWD' => ['received' => 0, 'expected' => 0, 'status' => 'not_received'],
                                            'ECMWF' => ['received' => 0, 'expected' => 0, 'status' => 'not_received'],
                                            'JMA' => ['received' => 0, 'expected' => 0, 'status' => 'not_received'],
                                            'NCEP' => ['received' => 0, 'expected' => 0, 'status' => 'not_received']
                                        ],
                                        'lastUpdated' => $date . ' ' . $time_period
                                    ];
                                }
                                
                                // Add center-specific data
                                if (isset($stationDataByWigosId[$wigosId]['centers'][$center])) {
                                    $received = intval($stationData['#received']);
                                    $expected = intval($stationData['#expected (GBON)']);
                                    $color_code = strtolower($stationData['color code']);
                                    
                                    // Map color code to status
                                    $status = 'not_received';
                                    switch ($color_code) {
                                        case 'green':
                                            $status = 'operational';
                                            break;
                                        case 'orange':
                                            $status = 'issues';
                                            break;
                                        case 'red':
                                            // Set critical ONLY if received is 0 and expected is 0 or 1
                                            if ($received == 0 && ($expected == 0 || $expected == 1)) {
                                                $status = 'critical';
                                            } else {
                                                $status = 'issues';
                                            }
                                            break;
                                    }
                                    
                                    $stationDataByWigosId[$wigosId]['centers'][$center] = [
                                        'received' => $received,
                                        'expected' => $expected,
                                        'status' => $status,
                                        'color_code' => $color_code,
                                        'description' => $stationData['description']
                                    ];
                                }
                            }
                        }
                    }
                }
                
                // Convert collected data to stations array
                foreach ($stationDataByWigosId as $wigosId => $stationData) {
                    if (in_array($stationData['territoryCode'], $territories) || $fetch_usa_territories) {
                        // Calculate overall stats
                        $total_received = 0;
                        $total_expected = 0;
                        
                        foreach ($stationData['centers'] as $center => $centerData) {
                            $total_received += $centerData['received'];
                            // Don't multiply expected by number of centers
                            if ($centerData['expected'] > 0) {
                                $total_expected = $centerData['expected'];
                            }
                        }
                        
                        // Calculate overall availability
                        $availability = $total_expected > 0 ? ($total_received / $total_expected) * 100 : 0;
                        
                        // Determine overall status
                        $status = 'not_received';
                        if ($availability >= 80) {
                            $status = 'operational';
                        } elseif ($availability > 0) {
                            // Check if all centers have 0/1/no data pattern
                            $all_zero_or_one = true;
                            foreach ($stationData['centers'] as $center => $centerData) {
                                // Only consider critical if received is 0 and expected is 0 or 1
                                // Or if both received and expected are 0 (no data)
                                if (!($centerData['received'] == 0 && ($centerData['expected'] == 0 || $centerData['expected'] == 1))) {
                                    $all_zero_or_one = false;
                                    break;
                                }
                            }
                            $status = $all_zero_or_one ? 'critical' : 'issues';
                        } else {
                            // When availability is 0
                            $status = 'not_received';
                        }
                        
                        // Create station entry
                        $station = [
                            'id' => $wigosId,
                            'name' => $stationData['name'],
                            'wigosId' => $wigosId,
                            'countryCode' => $stationData['countryCode'],
                            'inOSCAR' => $stationData['inOSCAR'],
                            'latitude' => $stationData['latitude'],
                            'longitude' => $stationData['longitude'],
                            'territory' => $stationData['territory'],
                            'territoryCode' => $stationData['territoryCode'],
                            'stationTypeName' => $stationData['stationTypeName'],
                            'stationStatusCode' => $status,
                            'dataCompleteness' => $availability,
                            'received' => $total_received,
                            'expected' => $total_expected,
                            'variable' => $stationData['variable'],
                            'date' => $stationData['date'],
                            'lastUpdated' => $stationData['lastUpdated'],
                            'DWD' => $stationData['centers']['DWD']['received'],
                            'ECMWF' => $stationData['centers']['ECMWF']['received'],
                            'JMA' => $stationData['centers']['JMA']['received'],
                            'NCEP' => $stationData['centers']['NCEP']['received'],
                            'centers' => $stationData['centers']
                        ];
                        
                        $usa_stations[] = $station;
                    }
                }
                
                // If only USA territories were requested, return the data now
                if (!$fetch_usa_territories && $is_usa_territory) {
                    echo json_encode([
                        'stations' => $usa_stations,
                        'metadata' => [
                            'total' => count($usa_stations),
                            'operational' => count(array_filter($usa_stations, function($s) { return $s['stationStatusCode'] === 'operational'; })),
                            'issues' => count(array_filter($usa_stations, function($s) { return $s['stationStatusCode'] === 'issues'; })),
                            'critical' => count(array_filter($usa_stations, function($s) { return $s['stationStatusCode'] === 'critical'; })),
                            'not_received' => count(array_filter($usa_stations, function($s) { return $s['stationStatusCode'] === 'not_received'; }))
                        ]
                    ]);
                    exit;
                }
            }
        }
    }

    // If not USA territory or USA data not found, or if we need to combine with Region V data
    // Build WDQMS API URL
    $url = "https://wdqms.wmo.int/wdqmsapi/v1/download/gbon/synop/$period/availability/?" . 
           "date=$date&period=$time_period&variable=$variable&centers=DWD,ECMWF,JMA,NCEP&countries=$territory";

    $log_message .= "API URL: $url\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Log response info
    $log_message = date('Y-m-d H:i:s') . " - Response received\n";
    $log_message .= "HTTP Code: $httpCode\n";
    if ($error) {
        $log_message .= "CURL Error: $error\n";
    }
    $log_message .= "Response (first 1000 chars): " . substr($response, 0, 1000) . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Process response
    if ($error) {
        $apiStatus = "cURL Error: " . $error;
        echo json_encode(['error' => true, 'message' => $apiStatus]);
    } elseif ($httpCode == 200) {
        $apiStatus = "Data berhasil diambil (HTTP $httpCode)";
        
        // Try to parse JSON response
        $jsonData = json_decode($response, true);
        $jsonError = json_last_error();
        
        // Log JSON parsing result
        $log_message = date('Y-m-d H:i:s') . " - JSON Parsing\n";
        $log_message .= "JSON Error: " . json_last_error_msg() . "\n";
        if ($jsonData) {
            $log_message .= "JSON Structure: " . print_r($jsonData, true) . "\n";
        }
        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Handle different response formats
        if ($jsonError === JSON_ERROR_NONE && $jsonData) {
            // Check various possible data structures
            if (isset($jsonData['data'])) {
                $stationData = $jsonData['data'];
            } elseif (isset($jsonData['stations'])) {
                $stationData = $jsonData['stations'];
            } elseif (is_array($jsonData) && !empty($jsonData)) {
                $stationData = $jsonData;
            } else {
                $stationData = [];
            }

            // Process station data
            foreach ($stationData as $station) {
                // Calculate status based on availability
                $availability = isset($station['availability']) ? floatval($station['availability']) : 0;
                
                if ($availability >= 80) {
                    $status = 'operational';
                } elseif ($availability >= 50) {
                    $status = 'issues';
                } elseif ($availability > 0) {
                    $status = 'critical';
                } else {
                    $status = 'not_received';
                }
                
                $stations[] = [
                    'id' => $station['stationId'] ?? $station['id'] ?? uniqid(),
                    'name' => $station['stationName'] ?? $station['name'] ?? 'Unknown Station',
                    'latitude' => (float)($station['latitude'] ?? $station['lat'] ?? 0),
                    'longitude' => (float)($station['longitude'] ?? $station['lon'] ?? 0),
                    'elevation' => (int)($station['elevation'] ?? $station['height'] ?? 0),
                    'territory' => $territory,
                    'territoryCode' => $territory,
                    'stationTypeName' => 'SYNOP',
                    'wigosId' => $station['wigosId'] ?? $station['wigos_id'] ?? 'N/A',
                    'stationStatusCode' => $status,
                    'dataCompleteness' => $availability,
                    'lastUpdated' => $date . ' ' . $time_period . ''
                ];
            }
        }
        
        // If no stations found from JSON, try CSV
        if (empty($stations)) {
            $log_message = date('Y-m-d H:i:s') . " - Trying CSV parsing\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);

            $lines = explode("\n", trim($response));
            if (count($lines) > 1) {
                $headers = str_getcsv($lines[0]);
                
                // Process all rows to collect data by station and center
                for ($i = 1; $i < count($lines); $i++) {
                    if (!empty(trim($lines[$i]))) {
                        $row = str_getcsv($lines[$i]);
                        if (count($row) >= count($headers)) {
                            $stationData = array_combine($headers, $row);
                            
                            // Skip if no wigosid
                            if (empty($stationData['wigosid'])) {
                                continue;
                            }
                            
                            $wigosId = $stationData['wigosid'];
                            $center = $stationData['center'] ?? 'Unknown';
                            
                            // Initialize station data if first time seeing this station
                            if (!isset($stationDataByWigosId[$wigosId])) {
                                $stationDataByWigosId[$wigosId] = [
                                    'name' => $stationData['name'] ?? 'Unknown Station',
                                    'wigosId' => $wigosId,
                                    'countryCode' => $stationData['country code'] ?? $territory,
                                    'inOSCAR' => $stationData['in OSCAR'] ?? 'False',
                                    'latitude' => (float)($stationData['latitude'] ?? 0),
                                    'longitude' => (float)($stationData['longitude'] ?? 0),
                                    'territory' => $territory,
                                    'territoryCode' => $territory,
                                    'stationTypeName' => 'SYNOP',
                                    'variable' => $stationData['variable'] ?? $variable,
                                    'date' => $stationData['date'] ?? $date,
                                    'expected' => intval($stationData['#expected (GBON)'] ?? 0),
                                    'centers' => [
                                        'DWD' => ['received' => 0, 'expected' => 0, 'status' => 'not_received'],
                                        'ECMWF' => ['received' => 0, 'expected' => 0, 'status' => 'not_received'],
                                        'JMA' => ['received' => 0, 'expected' => 0, 'status' => 'not_received'],
                                        'NCEP' => ['received' => 0, 'expected' => 0, 'status' => 'not_received']
                                    ],
                                    'lastUpdated' => $date . ' ' . $time_period . ''
                                ];
                            }
                            
                            // Add center-specific data
                            if (isset($stationDataByWigosId[$wigosId]['centers'][$center])) {
                                $received = intval($stationData['#received'] ?? 0);
                                $expected = intval($stationData['#expected (GBON)'] ?? 0);
                                $color_code = strtolower($stationData['color code'] ?? 'black');
                            
                            // Map color code to status
                            $status = 'not_received';
                            switch ($color_code) {
                                case 'green':
                                    $status = 'operational';
                                    break;
                                case 'orange':
                                    $status = 'issues';
                                    break;
                                case 'red':
                                    // Set critical ONLY if received is 0 and expected is 0 or 1
                                    if ($received == 0 && ($expected == 0 || $expected == 1)) {
                                        $status = 'critical';
                                    } else {
                                        $status = 'issues';
                                    }
                                    break;
                                case 'black':
                                default:
                                    $status = 'not_received';
                                    break;
                            }

                                $stationDataByWigosId[$wigosId]['centers'][$center] = [
                                'received' => $received,
                                'expected' => $expected,
                                    'status' => $status,
                                    'color_code' => $color_code,
                                    'description' => $stationData['description'] ?? ''
                                ];
                            }
                        }
                    }
                }
                
                // Convert collected data to stations array
                foreach ($stationDataByWigosId as $wigosId => $stationData) {
                    // Calculate overall stats
                    $total_received = 0;
                    $total_expected = 0;
                    
                    foreach ($stationData['centers'] as $center => $centerData) {
                        $total_received += $centerData['received'];
                        // Don't multiply expected by number of centers
                        if ($centerData['expected'] > 0) {
                            $total_expected = $centerData['expected'];
                        }
                    }
                    
                    // Calculate overall availability
                    $availability = $total_expected > 0 ? ($total_received / $total_expected) * 100 : 0;
                    
                    // Determine overall status
                    $status = 'not_received';
                    if ($availability >= 80) {
                        $status = 'operational';
                    } elseif ($availability > 0) {
                        // Check if all centers have 0/1/no data pattern
                        $all_zero_or_one = true;
                        foreach ($stationData['centers'] as $center => $centerData) {
                            // Only consider critical if received is 0 and expected is 0 or 1
                            // Or if both received and expected are 0 (no data)
                            if (!($centerData['received'] == 0 && ($centerData['expected'] == 0 || $centerData['expected'] == 1))) {
                                $all_zero_or_one = false;
                                break;
                            }
                        }
                        $status = $all_zero_or_one ? 'critical' : 'issues';
                    } else {
                        // When availability is 0
                        $status = 'not_received';
                    }
                    
                    // Determine overall color code
                    $color_code = 'black';
                    if ($availability >= 80) {
                        $color_code = 'green';
                    } elseif ($availability >= 50) {
                        $color_code = 'orange';
                    } elseif ($availability > 0) {
                        $color_code = 'red';
                    }
                    
                    // Create station entry
                    $station = [
                        'id' => $wigosId,
                        'name' => $stationData['name'],
                        'wigosId' => $wigosId,
                        'countryCode' => $stationData['countryCode'],
                        'inOSCAR' => $stationData['inOSCAR'],
                        'latitude' => $stationData['latitude'],
                        'longitude' => $stationData['longitude'],
                        'territory' => $stationData['territory'],
                        'territoryCode' => $stationData['territoryCode'],
                        'stationTypeName' => $stationData['stationTypeName'],
                        'stationStatusCode' => $status,
                        'dataCompleteness' => $availability,
                        'received' => $total_received,
                        'expected' => $total_expected,
                        'colorCode' => $color_code,
                        'variable' => $stationData['variable'],
                        'date' => $stationData['date'],
                        'lastUpdated' => $stationData['lastUpdated'],
                        'DWD' => $stationData['centers']['DWD']['received'],
                        'ECMWF' => $stationData['centers']['ECMWF']['received'],
                        'JMA' => $stationData['centers']['JMA']['received'],
                        'NCEP' => $stationData['centers']['NCEP']['received'],
                        'centers' => $stationData['centers']
                    ];
                    
                    $stations[] = $station;
                }
            }
        }

        // Count stations by status
        $total = count($stations);
        $operational = 0;
        $issues = 0;
        $critical = 0;
        $not_received = 0;

        foreach ($stations as $station) {
            switch ($station['stationStatusCode']) {
                case 'operational':
                    $operational++;
                    break;
                case 'issues':
                    $issues++;
                    break;
                case 'critical':
                    $critical++;
                    break;
                case 'not_received':
                    $not_received++;
                    break;
            }
        }

        // Log final station count
        $log_message = date('Y-m-d H:i:s') . " - Final Results\n";
        $log_message .= "Total stations: $total\n";
        $log_message .= "Operational: $operational\n";
        $log_message .= "Issues: $issues\n";
        $log_message .= "Critical: $critical\n";
        $log_message .= "Not received: $not_received\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Output success response
        echo json_encode([
            'stations' => $stations,
            'metadata' => [
                'total_stations' => $total,
                'operational_count' => $operational,
                'issues_count' => $issues,
                'critical_count' => $critical,
                'not_received_count' => $not_received,
                'territory' => $territory,
                'territory_name' => $territory,
                'api_url' => $url,
                'http_code' => $httpCode,
                'api_status' => $apiStatus
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        $apiStatus = "HTTP Error: $httpCode";
        
        // Log error details
        $log_message = date('Y-m-d H:i:s') . " - Error Response\n";
        $log_message .= "Status: $apiStatus\n";
        $log_message .= "Response: " . substr($response, 0, 1000) . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        echo json_encode([
            'error' => true, 
            'message' => $apiStatus,
            'debug_info' => [
                'url' => $url,
                'http_code' => $httpCode,
                'response' => substr($response, 0, 1000)
            ]
        ]);
    }
} catch (Exception $e) {
    // Log the error
    $error_message = date('Y-m-d H:i:s') . " - Uncaught Exception\n";
    $error_message .= "Message: " . $e->getMessage() . "\n";
    $error_message .= "File: " . $e->getFile() . "\n";
    $error_message .= "Line: " . $e->getLine() . "\n";
    file_put_contents($log_file, $error_message, FILE_APPEND);

    // Return a clean error response
    echo json_encode([
        'error' => true,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?> 