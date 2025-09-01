// Valid parameters
const VALID_VARIABLES = {
    'pressure': 'Pressure',
    'temperature': 'Temperature',
    'zonal_wind': 'Zonal Wind',
    'meridional_wind': 'Meridional Wind',
    'humidity': 'Humidity'
};

const VALID_PERIODS = {
    'six_hour': '6 Hours',
    'daily': 'Daily',
    'monthly': 'Monthly'
};

const VALID_TIME_PERIODS = {
    '00': '00:00',
    '06': '06:00',
    '12': '12:00',
    '18': '18:00'
};

// Debug mode - set to true to enable debug info
window.debugMode = false;

// Initialize control event listeners
function initializeControls() {
    // Initialize legend controls
    initializeLegendControls();
    
    // Initialize status filters
    initializeStatusFilters();
    
    // Region selector
    document.getElementById('regionSelect').addEventListener('change', function() {
        const territory = this.value;
        updateMapCenterForTerritory(territory);
        loadStationData();
    });

    // Period type selector
    const periodTypeSelect = document.getElementById('periodType');
    if (periodTypeSelect) {
        periodTypeSelect.addEventListener('change', function() {
            const value = this.value;
            const dateInput = document.getElementById('observationDate');
            // Show/hide time period buttons based on selected period
            const timePeriodContainer = document.querySelector('.time-periods').parentElement;
            if (timePeriodContainer) {
                timePeriodContainer.style.display = value === 'six-hour' ? 'block' : 'none';
            }
            // Change date input type and value based on period
            if (value === 'monthly') {
                dateInput.type = 'month';
                // Set date to previous month
                const previousMonth = new Date();
                previousMonth.setMonth(previousMonth.getMonth() - 1);
                const monthStr = previousMonth.toISOString().split('T')[0].substring(0, 7);
                dateInput.value = monthStr;
            } else {
                dateInput.type = 'date';
                // Set date to 2 days ago
                const twoDaysAgo = new Date();
                twoDaysAgo.setDate(twoDaysAgo.getDate() - 2);
                const dateStr = twoDaysAgo.toISOString().split('T')[0];
                dateInput.value = dateStr;
            }
            loadStationData();
        });
    }

    // Time period buttons
    const timeButtons = document.querySelectorAll('.time-periods .time-btn');
    if (timeButtons.length > 0) {
        timeButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                timeButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if (periodTypeSelect) {
                    periodTypeSelect.value = 'six-hour';
                }
                loadStationData();
            });
        });
    }

    // Variable type selector
    const variableSelect = document.getElementById('variableType');
    if (variableSelect) {
        variableSelect.addEventListener('change', function() {
            loadStationData();
        });
    }

    // Date control
    document.getElementById('observationDate').addEventListener('change', loadStationData);
}

// Set default values
function initializeDefaults() {
    // Set default date based on period type
    const dateInput = document.getElementById('observationDate');
    const periodType = document.getElementById('periodType');
    
    // Always set period type to six-hour
    periodType.value = 'six-hour';
    
    // Show time period buttons
    const timePeriodContainer = document.querySelector('.time-periods').parentElement;
    if (timePeriodContainer) {
        timePeriodContainer.style.display = 'block';
    }
    
    // Set default date to 2 days ago
    dateInput.type = 'date';
    const twoDaysAgo = new Date();
    twoDaysAgo.setDate(twoDaysAgo.getDate() - 2);
    const dateStr = twoDaysAgo.toISOString().split('T')[0];
    dateInput.value = dateStr;
    
    // Set default region
    document.getElementById('regionSelect').value = DEFAULT_REGION;
    
    // Set default time period button (00)
    const timeButtons = document.querySelectorAll('.time-periods .time-btn');
    if (timeButtons.length > 0) {
        timeButtons.forEach(btn => btn.classList.remove('active'));
        const defaultTimeBtn = document.querySelector('.time-periods .time-btn[data-period="00"]');
        if (defaultTimeBtn) {
            defaultTimeBtn.classList.add('active');
        }
    }
}

// Check if API is accessible
async function checkApiAccess() {
    try {
        const territory = document.getElementById('regionSelect').value;
        const date = document.getElementById('observationDate').value;
        const period = document.querySelector('.time-btn.active')?.dataset.period || 'six_hour';
        const time_period = document.querySelector('.time-period-btn.active')?.dataset.time_period || '00';
        const variable = document.getElementById('variableType').value;
        
        const response = await fetch(`${API_ENDPOINT}land_surface_stations.php?territory=${territory}&date=${date}&period=${period}&time_period=${time_period}&variable=${variable}`);
        return response.ok;
    } catch (error) {
        console.error('API access check failed:', error);
        return false;
    }
}

// Function to update URL parameters
function updateURLParameters(params) {
    const url = new URL(window.location.href);
    Object.entries(params).forEach(([key, value]) => {
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
    });
    window.history.replaceState({}, '', url);
}

// Function to get URL parameters
function getURLParameters() {
    const params = new URLSearchParams(window.location.search);
    return {
        territory: params.get('territory'),
        variable: params.get('variable'),
        date: params.get('date'),
        period: params.get('period'),
        time_period: params.get('time_period')
    };
}

// Initialize controls from URL parameters
function initializeFromURL() {
    const params = getURLParameters();
    
    // Set region
    if (params.territory) {
        const regionSelect = document.getElementById('regionSelect');
        if (regionSelect && regionSelect.querySelector(`option[value="${params.territory}"]`)) {
            regionSelect.value = params.territory;
        }
    }
    
    // Set variable
    if (params.variable) {
        const variableSelect = document.getElementById('variableType');
        if (variableSelect && variableSelect.querySelector(`option[value="${params.variable}"]`)) {
            variableSelect.value = params.variable;
        }
    }
    
    // Set date
    if (params.date) {
        const dateInput = document.getElementById('observationDate');
        if (dateInput) {
            dateInput.value = params.date;
        }
    }
    
    // Always set period type to six-hour and show time period buttons
    const periodSelect = document.getElementById('periodType');
    if (periodSelect) {
        periodSelect.value = 'six-hour';
        const timePeriodContainer = document.querySelector('.time-periods').parentElement;
        if (timePeriodContainer) {
            timePeriodContainer.style.display = 'block';
        }
    }
    
    // Set time period
    const timeButtons = document.querySelectorAll('.time-periods .time-btn');
    if (timeButtons.length > 0) {
        timeButtons.forEach(btn => btn.classList.remove('active'));
        const defaultTimeBtn = document.querySelector('.time-periods .time-btn[data-period="00"]');
        if (defaultTimeBtn) {
            defaultTimeBtn.classList.add('active');
        }
    }
}

// Update loadStationData to handle URL parameters
async function loadStationData() {
    if (isLoading) {
        if (window.debugMode) console.log('Skipping data load - already loading');
        return;
    }
    
    let territory = document.getElementById('regionSelect').value;
    const variable = document.getElementById('variableType').value;
    const date = document.getElementById('observationDate').value;
    const periodType = document.getElementById('periodType').value;
    const timePeriod = document.querySelector('.time-periods .time-btn.active')?.dataset.period;
    
    // Update URL parameters
    updateURLParameters({
        territory: territory,
        variable: variable,
        date: date,
        period: periodType,
        time_period: periodType === 'six-hour' ? timePeriod : null
    });
    
    if (window.debugMode) {
        console.log('Loading station data with params:', {
            region: territory,
            variable: variable,
            date: date,
            periodType: periodType,
            timePeriod: timePeriod
        });
    }
    
    isLoading = true;
    showLoading(true);
    
    try {
        // Validate required parameters
        if (!territory || !variable || !date || !periodType) {
            throw new Error('Missing required parameters');
        }
        
        // For six-hour period, ensure we have a time period selected
        if (periodType === 'six-hour' && !timePeriod) {
            // If no time period is selected, select the default (00)
            const defaultTimeBtn = document.querySelector('.time-periods .time-btn[data-period="00"]');
            if (defaultTimeBtn) {
                defaultTimeBtn.classList.add('active');
            }
            throw new Error('Please select a time period (00, 06, 12, or 18)');
        }
        
        // Build the API URL
        let apiUrl;
        if (periodType === 'monthly') {
            // For monthly, format the date as YYYY-MM
            const monthDate = date.substring(0, 7);
            apiUrl = `${API_ENDPOINT}land_surface_stations.php?territory=${territory}&variable=${variable}&date=${monthDate}&period=${periodType}`;
        } else {
            apiUrl = `${API_ENDPOINT}land_surface_stations.php?territory=${territory}&variable=${variable}&date=${date}&period=${periodType}`;
            // Only add time_period parameter for six-hour period
            if (periodType === 'six-hour' && timePeriod) {
                apiUrl += `&time_period=${timePeriod}`;
            }
        }
        
        if (window.debugMode) {
            console.log('Fetching data from:', apiUrl);
        }
        
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            console.error('JSON parsing error:', jsonError);
            throw new Error('Failed to parse API response as JSON. Please contact support.');
        }
        
        if (!data.stations || !Array.isArray(data.stations)) {
            throw new Error('Invalid data format received from API');
        }
        
        currentStations = data.stations;
        displayStations(currentStations);
        updateStatistics(data.metadata);
        
        if (window.debugMode) {
            console.log('Data load complete:', {
                stationCount: currentStations.length,
                metadata: data.metadata
            });
        }
        
        showLoading(false);
        isLoading = false;
        
    } catch (error) {
        console.error('Error loading station data:', error);
        showAlert('error', `Failed to load station data: ${error.message}`);
        showLoading(false);
        isLoading = false;
    }
}

function refresh() {
    loadStationData();
}

// Update station status visualization
function updateStationStatus() {
    const period = document.querySelector('.time-btn.active')?.dataset.period;
    const variable = document.getElementById('variableType').value;
    const date = document.getElementById('observationDate').value;

    // For major parameter changes, reload data from API
    if (document.getElementById('variableType').dataset.lastValue !== variable ||
        document.getElementById('observationDate').dataset.lastValue !== date) {
        
        // Store current values for comparison on next change
        document.getElementById('variableType').dataset.lastValue = variable;
        document.getElementById('observationDate').dataset.lastValue = date;
        
        // Reload data from API with updated parameters
        loadStationData();
        return;
    }
    
    // For minor changes (like time period), just update the visualization
    stationMarkers.forEach((marker, index) => {
        const station = currentStations[index];
        if (station) {
            const newStatus = determineStationStatus(station);
            const colors = {
                'complete': '#10b981',    // hijau
                'issues-high': '#ef4444', // merah
                'issues-low': '#f59e0b',  // oranye
                'not-received': '#374151' // abu-abu
            };
            marker.setStyle({ fillColor: colors[newStatus] });
            
            // Update popup content
            const popupContent = createPopupContent(station, newStatus);
            marker.getPopup()?.setContent(popupContent);
        }
    });

    updateStatistics();
}

// Update map center based on selected territory
function updateMapCenterForTerritory(territory) {
    // Get territory settings or use default
    const settings = territoryCenters[territory] || territoryCenters['IDN'];
    
    // Update map view - use bounds if available for better fit
    if (settings.bounds) {
        map.fitBounds(settings.bounds);
    } else {
        map.setView(settings.center, settings.zoom);
    }
}

// Calculate statistics from current stations
function calculateStatistics() {
    let total = 0;
    let issues = 0;

    // Count only visible markers
    stationMarkers.forEach(markerArray => {
        if (Array.isArray(markerArray)) {
            const station = currentStations[stationMarkers.indexOf(markerArray)];
            // Check if marker is visible (not hidden)
            if (station && markerArray[0] && map.hasLayer(markerArray[0])) {
                total++;
                const status = determineStationStatus(station);
                if (status === 'issues-high' || status === 'issues-low') {
                    issues++;
                }
            }
        }
    });

    return { total, issues };
}

// Update statistics panel
function updateStatistics() {
    const stats = calculateStatistics();
    
    // Update the statistics in the UI
    document.getElementById('totalStations').textContent = stats.total;
    document.getElementById('issuesReports').textContent = stats.issues;
    
    // Update percentage if there are stations
    const issuesPercentElement = document.getElementById('issuesPercent');
    if (stats.total > 0) {
        const percentage = Math.round((stats.issues / stats.total) * 100);
        issuesPercentElement.textContent = `${percentage}%`;
    } else {
        issuesPercentElement.textContent = '0%';
    }
}

// Determine station status based on data completeness
function determineStationStatus(station) {
    const priorityCenters = ['ECMWF', 'JMA', 'NCEP'];
    const expected = station.expected || 6; // Default expected value is 6

    // First check priority centers
    for (const center of priorityCenters) {
        if (station[center] !== undefined) {
            const coverage = (station[center] / expected) * 100;
            if (coverage >= 80) {
                return 'complete';
            }
        }
    }

    // If no priority center has ≥80% coverage, calculate total coverage
    let totalReceived = 0;
    let totalExpected = 0;
    const centers = ['DWD', 'ECMWF', 'JMA', 'NCEP'];

    centers.forEach(center => {
        if (station[center] !== undefined) {
            totalReceived += station[center];
            totalExpected += expected;
        }
    });

    // Calculate total coverage percentage
    const totalCoverage = totalExpected > 0 ? (totalReceived / totalExpected) * 100 : 0;

    // Determine status based on total coverage
    if (totalCoverage >= 80) return 'complete';
    if (totalCoverage === 0) return 'not-received'; // No data received
    
    // Check if all centers have 0/1/no data pattern for critical (red)
    let allZeroOrOne = true;
    centers.forEach(center => {
        if (station[center] !== undefined) {
            // If any center has received > 0, it's not the special trigger case
            if (station[center] > 0) {
                allZeroOrOne = false;
            }
        }
    });
    
    // If all centers have 0/1/no data, it's issues-high (critical)
    // Otherwise, it's issues-low (any other case below 80%)
    return allZeroOrOne ? 'issues-high' : 'issues-low';
}

// Create station marker
function createStationMarker(station, status) {
    const colors = {
        'complete': '#10b981',    // hijau
        'issues-high': '#ef4444', // merah
        'issues-low': '#f59e0b',  // oranye
        'not-received': '#374151' // abu-abu
    };

    // Ensure coordinates are valid numbers
    const lat = parseFloat(station.latitude);
    const lng = parseFloat(station.longitude);
    
    if (isNaN(lat) || isNaN(lng)) {
        console.error('Invalid coordinates for station:', station.name, station.latitude, station.longitude);
        return null;
    }

    try {
        // Create markers that wrap around the world
        const markers = [];
        // Create main marker
        const marker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: `station-marker status-${status}`,
                html: `<div style="background-color: ${colors[status]}; border: 2px solid white; width: 16px; height: 16px; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.5);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        });

        // Create wrapped markers for date line crossing
        const wrappedMarkerEast = L.marker([lat, lng + 360], {
            icon: L.divIcon({
                className: `station-marker status-${status}`,
                html: `<div style="background-color: ${colors[status]}; border: 2px solid white; width: 16px; height: 16px; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.5);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        });

        const wrappedMarkerWest = L.marker([lat, lng - 360], {
            icon: L.divIcon({
                className: `station-marker status-${status}`,
                html: `<div style="background-color: ${colors[status]}; border: 2px solid white; width: 16px; height: 16px; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.5);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        });

        // Create popup content
        const popupContent = createPopupContent(station, status);
        
        // Bind popup and tooltip to all markers
        [marker, wrappedMarkerEast, wrappedMarkerWest].forEach(m => {
            m.bindPopup(popupContent, {
            maxWidth: 400,
            className: 'custom-popup',
            autoPan: true,
            closeButton: true
        });
        
            m.bindTooltip(`${station.name} (${status})`, {
            direction: 'top',
            offset: [0, -10],
            opacity: 0.9,
            className: `status-tooltip status-${status}`
        });
        
            m.on('click', function() {
            updateInfoPanel(station, status);
            });
        });

        return [marker, wrappedMarkerEast, wrappedMarkerWest];
    } catch (error) {
        console.error('Error creating marker:', error);
        showAlert('error', 'Error creating marker: ' + error.message);
        return null;
    }
}

// Create popup content for station
function createPopupContent(station, status) {
    // Build center-specific data rows if available
    let centerDataHTML = '';
    const hasCenterData = station.DWD !== undefined || 
                          station.ECMWF !== undefined || 
                          station.JMA !== undefined || 
                          station.NCEP !== undefined;
                          
    if (hasCenterData) {
        const expected = station.expected || 6;
        let totalReceived = 0;
        
        centerDataHTML = `
            <div class="info-row center-data-header">
                <span class="info-label">Center Data:</span>
            </div>
            <div class="info-row center-data">
                <table class="center-table">
                    <tr>
                        <th>Center</th>
                        <th>Received</th>
                        <th>Expected</th>
                        <th>Coverage</th>
                    </tr>
                    <tr>
                        <td>DWD</td>
                        <td>${station.DWD !== undefined ? station.DWD : 'N/A'}</td>
                        <td>${expected}</td>
                        <td>${station.DWD !== undefined ? Math.round((station.DWD / expected) * 100) + '%' : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td>ECMWF</td>
                        <td>${station.ECMWF !== undefined ? station.ECMWF : 'N/A'}</td>
                        <td>${expected}</td>
                        <td>${station.ECMWF !== undefined ? Math.round((station.ECMWF / expected) * 100) + '%' : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td>JMA</td>
                        <td>${station.JMA !== undefined ? station.JMA : 'N/A'}</td>
                        <td>${expected}</td>
                        <td>${station.JMA !== undefined ? Math.round((station.JMA / expected) * 100) + '%' : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td>NCEP</td>
                        <td>${station.NCEP !== undefined ? station.NCEP : 'N/A'}</td>
                        <td>${expected}</td>
                        <td>${station.NCEP !== undefined ? Math.round((station.NCEP / expected) * 100) + '%' : 'N/A'}</td>
                    </tr>
                </table>
            </div>
            <div class="info-row">
                <span class="info-label">Total Data Received:</span>
                <span class="info-value">${calculateTotalCoverage(station)}%</span>
            </div>
        `;
    }

    return `
    <div class="popup">
        <div class="popup-header">
            <h3><i class="fas fa-broadcast-tower"></i> ${station.name}</h3>
        </div>
        <div class="popup-body">
            <div class="info-row">
                <span class="info-label">WIGOS ID:</span>
                <span class="info-value">${station.wigosId || 'N/A'}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Country:</span>
                <span class="info-value">${station.countryCode}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Coordinates:</span>
                <span class="info-value">${station.latitude.toFixed(4)}, ${station.longitude.toFixed(4)}</span>
            </div>
            ${centerDataHTML}
            ${station.variable ? `
            <div class="info-row">
                <span class="info-label">Variable:</span>
                <span class="info-value">${station.variable}</span>
            </div>
            ` : ''}
            ${station.inOSCAR ? `
            <div class="info-row">
                <span class="info-label">In OSCAR:</span>
                <span class="info-value">
                    <a href="https://oscar.wmo.int/surface/index.html#/search/station/stationReportDetails/${station.wigosId}" target="_blank" class="oscar-link">${station.inOSCAR} <i class="fas fa-external-link-alt"></i></a>
                </span>
            </div>
            ` : ''}
            <div class="info-row">
                <span class="info-label">Last Updated:</span>
                <span class="info-value">${station.lastUpdated || ''}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Baseline:</span>
                <span class="info-value">${station.baseline || 'oscar'}</span>
            </div>
            <div class="info-row"> 
                <span class="info-label">Summary:</span>
                <span class="info-value">
                <div class="form-group">
                <label for="exampleFormControlSelect1"></label>
                <option>${station.lastUpdated || ''}_${station.countryCode}_${station.name}_${calculateTotalCoverage(station)}%</option>
                </div>
                </span>
            </div>
        </div>
    </div>
    `;
}

// Calculate total coverage for a station
function calculateTotalCoverage(station) {
    const centers = ['DWD', 'ECMWF', 'JMA', 'NCEP'];
    const expected = station.expected || 6;
    let totalReceived = 0;
    let totalExpected = 0;

    centers.forEach(center => {
        if (station[center] !== undefined) {
            totalReceived += station[center];
            totalExpected += expected;
        }
    });

    return totalExpected > 0 ? Math.round((totalReceived / totalExpected) * 100) : 0;
}

// Display stations on map
function displayStations(stations) {
    try {
        // Clear existing markers
        stationMarkers.forEach(markerArray => {
            if (Array.isArray(markerArray)) {
                markerArray.forEach(marker => {
                    if (marker) marker.remove();
                });
            } else if (markerArray) {
                markerArray.remove();
            }
        });
        stationMarkers = [];

        // Add new markers
        stations.forEach(station => {
            const status = determineStationStatus(station);
            const markers = createStationMarker(station, status);
            if (markers) {
                // Always show markers initially since all statuses are active by default
                markers.forEach(marker => {
                    if (marker) marker.addTo(map);
                });
                stationMarkers.push(markers);
            }
        });

        // Store current stations for filtering
        currentStations = stations;

        // Update statistics after displaying stations
        updateStatistics({
            total: stations.length,
            stations: stations
        });
    } catch (error) {
        console.error('Error displaying stations:', error);
        showAlert('error', 'Error displaying stations: ' + error.message);
    }
}

 