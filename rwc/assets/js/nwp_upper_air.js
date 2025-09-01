// Dummy data for NWP land surface monitoring
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing NWP Land Surface monitoring with mock data...');
    
    // Define global variables if they don't exist
    if (typeof window.map === 'undefined') window.map = null;
    if (typeof window.stationMarkers === 'undefined') window.stationMarkers = [];
    if (typeof window.currentStations === 'undefined') window.currentStations = [];
    if (typeof window.isLoading === 'undefined') window.isLoading = false;
    if (typeof window.API_ENDPOINT === 'undefined') window.API_ENDPOINT = window.location.origin + '/api/';
    if (typeof window.DEFAULT_REGION === 'undefined') window.DEFAULT_REGION = 'ALL_COMBINED';
    if (typeof window.legendsVisible === 'undefined') window.legendsVisible = true;
    
    // Add utility functions that might be missing
    window.showLoading = function(show) {
        const loadingElement = document.getElementById('loading');
        if (loadingElement) {
            loadingElement.style.display = show ? 'flex' : 'none';
        }
    };
    
    window.showAlert = function(type, message) {
        const alert = document.createElement('div'); 
        alert.className = `alert ${type}`; 
        alert.textContent = message; 
        document.body.appendChild(alert); 

        setTimeout(() => alert.classList.add('show'), 100); 
        setTimeout(() => { 
            alert.classList.remove('show'); 
            setTimeout(() => document.body.removeChild(alert), 300); 
        }, 3000);
    };
    
    window.updateURLParameters = function(params) {
        const url = new URL(window.location.href);
        Object.entries(params).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.replaceState({}, '', url);
    };

    window.calculateTotalCoverage = function(station) {
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
    };

    window.resetView = function() { 
        if (window.stationMarkers.length > 0) { 
            // Flatten the array of marker arrays and filter out any null/undefined values
            const allMarkers = window.stationMarkers
                .flat()
                .filter(marker => marker && window.map.hasLayer(marker));
                
            if (allMarkers.length > 0) {
                const group = new L.featureGroup(allMarkers);
                window.map.fitBounds(group.getBounds().pad(0.1));
            } else {
                window.map.setView([-2.5, 118], 5);
            }
        } else { 
            window.map.setView([-2.5, 118], 5); 
        } 
    };
    
    window.refresh = function() {
        window.loadStationData();
    };
    
    window.saveData = function() {
        if (window.currentStations.length === 0) {
            window.showAlert('warning', 'No data to save');
            return;
        }
        
        try {
            // Define CSV headers to match the API format
            const headers = [
                'name',
                'wigosid',
                'country code',
                'longitude',
                'latitude',
                'in OSCAR',
                '#received',
                '#expected (GBON)',
                'color code',
                'description',
                'variable',
                'date',
                'center',
                'baseline'
            ];
            
            // Convert stations data to CSV rows
            const csvRows = [headers.join(',')];
            
            // Centers to include in export
            const centers = ['DWD', 'ECMWF', 'JMA', 'NCEP'];
            
            window.currentStations.forEach(station => {
                // Format latitude and longitude with proper decimal places
                const formattedLatitude = typeof station.latitude === 'number' ? station.latitude.toFixed(4) : station.latitude;
                const formattedLongitude = typeof station.longitude === 'number' ? station.longitude.toFixed(4) : station.longitude;
                
                // For each center, create a separate row
                centers.forEach(center => {
                    // Get center-specific data
                    const received = station[center] || 0;
                    const expected = station.expected || 0;
                    
                    // Determine color code based on coverage
                    let colorCode = 'black';
                    if (expected > 0) {
                        const coverage = (received / expected) * 100;
                        if (coverage >= 80) {
                            colorCode = 'green';
                        } else if (coverage >= 30) {
                            colorCode = 'orange';
                        } else if (coverage > 0) {
                            colorCode = 'red';
                        }
                    }
                    
                    // Get description from center data if available
                    let description = '';
                    if (station.centers && station.centers[center] && station.centers[center].description) {
                        description = station.centers[center].description;
                    }
                    
                    const row = [
                        `"${station.name || ''}"`,
                        `"${station.wigosId || ''}"`,
                        `"${station.countryCode || ''}"`,
                        formattedLongitude,
                        formattedLatitude,
                        `"${station.inOSCAR || 'False'}"`,
                        received,
                        expected,
                        `"${colorCode}"`,
                        `"${(description || '').replace(/"/g, '""')}"`,
                        `"${station.variable || ''}"`,
                        `"${station.date || ''}"`,
                        `"${center}"`,
                        `"${station.baseline || 'oscar'}"`
                    ].join(',');
                    csvRows.push(row);
                });
            });
            
            // Create CSV content
            const csvContent = csvRows.join('\n');
            
            // Create a Blob containing the CSV data
            const blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8;'});
            
            // Get the selected date and territory
            const selectedDate = document.getElementById('observationDate').value;
            const territory = document.getElementById('regionSelect').value;
            const variable = document.getElementById('variableType').value;
            const baseline = document.querySelector('.baseline .baseline-btn.active')?.dataset.baseline || 'oscar';
            
            // Get period type
            const period = document.getElementById('periodType').value || 'six-hour';
            
            // For six-hour period, include time period in filename
            let filename;
            if (period === 'six-hour') {
                const time_period = document.querySelector('.time-periods .time-btn.active')?.dataset.period || '00';
                filename = `land_surface_${territory}_${variable}_${period}_${time_period}_${baseline}_${selectedDate}.csv`;
            } else {
                filename = `land_surface_${territory}_${variable}_${period}_${baseline}_${selectedDate}.csv`;
            }
            
            // Create a link element to trigger the download
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            
            // Append link to body, click it, then remove it
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            window.showAlert('success', `Land surface station data saved as CSV successfully`);
        } catch (error) {
            console.error('Error saving data:', error);
            window.showAlert('error', 'Failed to save data: ' + error.message);
        }
    };
    
    window.toggleLegends = function() { 
        const legendPanels = document.querySelectorAll('.panel'); 
        const toggleButton = document.querySelector('button[onclick="toggleLegends()"] i'); 
        
        window.legendsVisible = !window.legendsVisible; 
        
        legendPanels.forEach(panel => { 
            if (window.legendsVisible) { 
                panel.classList.remove('hidden'); 
                toggleButton.classList.remove('fa-eye-slash'); 
                toggleButton.classList.add('fa-eye'); 
            } else { 
                panel.classList.add('hidden'); 
                toggleButton.classList.remove('fa-eye'); 
                toggleButton.classList.add('fa-eye-slash'); 
            } 
        }); 
    };
    
    window.updateInfoPanel = function(station, status) {
        console.log('Station clicked:', station, 'Status:', status);
    };

    // Initialize the map first before doing anything else
    const mapInitialized = initializeMap();
    
    if (!mapInitialized) {
        console.error('Map initialization failed. Cannot continue.');
        return;
    }
    
    // Connect button functions to utility.js functions
    window.resetView = function() { 
        if (window.stationMarkers.length > 0) { 
            // Flatten the array of marker arrays and filter out any null/undefined values
            const allMarkers = window.stationMarkers
                .flat()
                .filter(marker => marker && window.map.hasLayer(marker));
                
            if (allMarkers.length > 0) {
                const group = new L.featureGroup(allMarkers);
                window.map.fitBounds(group.getBounds().pad(0.1));
            } else {
                window.map.setView([-2.5, 118], 5);
            }
        } else { 
            window.map.setView([-2.5, 118], 5); 
        } 
    };
    
    window.refresh = function() {
        window.loadStationData();
    };
    
    window.toggleLegends = function() { 
        const legendPanels = document.querySelectorAll('.panel'); 
        const toggleButton = document.querySelector('button[onclick="toggleLegends()"] i'); 
        
        window.legendsVisible = !window.legendsVisible; 
        
        legendPanels.forEach(panel => { 
            if (window.legendsVisible) { 
                panel.classList.remove('hidden'); 
                toggleButton.classList.remove('fa-eye-slash'); 
                toggleButton.classList.add('fa-eye'); 
            } else { 
                panel.classList.add('hidden'); 
                toggleButton.classList.remove('fa-eye'); 
                toggleButton.classList.add('fa-eye-slash'); 
            } 
        }); 
    };
    
    // Initialize legend controls
    window.initializeLegendControls = function() {
        document.querySelectorAll('.legend-item.clickable').forEach(item => {
            // Set initial state to active and visible
            item.style.cursor = 'pointer';
            item.classList.add('active');
            item.style.opacity = '1';
            
            item.addEventListener('click', function() {
                const status = this.dataset.status;
                this.classList.toggle('active');
                
                // Update opacity based on active state
                this.style.opacity = this.classList.contains('active') ? '1' : '0.5';
                
                // Filter markers based on new status
                filterMarkersByStatus();
                
                // Show status change alert
                const isActive = this.classList.contains('active');
                const statusName = this.querySelector('span').textContent;
                window.showAlert(
                    isActive ? 'info' : 'warning',
                    isActive ? `Showing ${statusName} stations` : `Hiding ${statusName} stations`
                );
            });
        });
    };
    
    // Filter markers based on active status filters
    window.filterMarkersByStatus = function() {
        // Get active status filters
        const activeStatuses = [];
        document.querySelectorAll('.legend-item.clickable.active').forEach(item => {
            activeStatuses.push(item.dataset.status);
        });
        
        console.log('Active status filters:', activeStatuses);
        
        // Filter markers
        window.stationMarkers.forEach(markerArray => {
            if (Array.isArray(markerArray)) {
                const station = window.currentStations[window.stationMarkers.indexOf(markerArray)];
                if (station) {
                    const status = window.determineStationStatus(station);
                    
                    // Show or hide all markers in the array based on status
                    markerArray.forEach(marker => {
                        if (marker) {
                            if (activeStatuses.includes(status)) {
                                // Show marker
                                if (!window.map.hasLayer(marker)) {
                                    window.map.addLayer(marker);
                                }
                            } else {
                                // Hide marker
                                if (window.map.hasLayer(marker)) {
                                    marker.remove();
                                }
                            }
                        }
                    });
                }
            }
        });
        
        // Update statistics to reflect visible markers only
        window.updateStatistics();
    };
    
    // Initialize the page
    window.initializeControls();
    window.initializeDefaults();
    
    // Wait a bit for the map to fully render before loading data
    setTimeout(() => {
        window.loadStationData();
    
        // Add event listener for monitoring centre dropdown
        document.getElementById('monitoringCentre').addEventListener('change', window.loadStationData);
    }, 500);
});

// Generate dummy data based on parameters
function generateDummyData(territory, variable, periodType, timePeriod, monitoringCentre, baseline) {
    // Base stations for different territories
    const baseStations = {
        'IDN': [
            { name: 'Jakarta', latitude: -6.2088, longitude: 106.8456, countryCode: 'IDN', wigosId: '0-20000-0-10980' },
            { name: 'Surabaya', latitude: -7.2575, longitude: 112.7521, countryCode: 'IDN', wigosId: '0-20000-0-10950' },
            { name: 'Medan', latitude: 3.5952, longitude: 98.6722, countryCode: 'IDN', wigosId: '0-20000-0-10920' },
            { name: 'Makassar', latitude: -5.1477, longitude: 119.4327, countryCode: 'IDN', wigosId: '0-20000-0-10910' },
            { name: 'Bandung', latitude: -6.9175, longitude: 107.6191, countryCode: 'IDN', wigosId: '0-20000-0-10900' },
            { name: 'Bali', latitude: -8.3405, longitude: 115.0920, countryCode: 'IDN', wigosId: '0-20000-0-10890' },
            { name: 'Palembang', latitude: -2.9761, longitude: 104.7754, countryCode: 'IDN', wigosId: '0-20000-0-10870' },
            { name: 'Jayapura', latitude: -2.5333, longitude: 140.7167, countryCode: 'IDN', wigosId: '0-20000-0-10860' },
            { name: 'Ambon', latitude: -3.6954, longitude: 128.1814, countryCode: 'IDN', wigosId: '0-20000-0-10850' },
            { name: 'Manado', latitude: 1.4748, longitude: 124.8421, countryCode: 'IDN', wigosId: '0-20000-0-10840' }
        ],
        'MYS': [
            { name: 'Kuala Lumpur', latitude: 3.1390, longitude: 101.6869, countryCode: 'MYS', wigosId: '0-20001-0-48647' },
            { name: 'Penang', latitude: 5.4141, longitude: 100.3288, countryCode: 'MYS', wigosId: '0-20001-0-48649' },
            { name: 'Johor Bahru', latitude: 1.4927, longitude: 103.7414, countryCode: 'MYS', wigosId: '0-20001-0-48650' },
            { name: 'Kuching', latitude: 1.5497, longitude: 110.3626, countryCode: 'MYS', wigosId: '0-20001-0-48651' },
            { name: 'Kota Kinabalu', latitude: 5.9804, longitude: 116.0735, countryCode: 'MYS', wigosId: '0-20001-0-48652' },
            { name: 'Malacca', latitude: 2.1896, longitude: 102.2501, countryCode: 'MYS', wigosId: '0-20001-0-48653' }
        ],
        'SGP': [
            { name: 'Singapore Changi', latitude: 1.3644, longitude: 103.9915, countryCode: 'SGP', wigosId: '0-20002-0-48698' },
            { name: 'Singapore Central', latitude: 1.3521, longitude: 103.8198, countryCode: 'SGP', wigosId: '0-20002-0-48699' },
            { name: 'Singapore West', latitude: 1.3135, longitude: 103.7038, countryCode: 'SGP', wigosId: '0-20002-0-48700' }
        ],
        'PHL': [
            { name: 'Manila', latitude: 14.5995, longitude: 120.9842, countryCode: 'PHL', wigosId: '0-20003-0-98429' },
            { name: 'Cebu', latitude: 10.3157, longitude: 123.8854, countryCode: 'PHL', wigosId: '0-20003-0-98430' },
            { name: 'Davao', latitude: 7.1907, longitude: 125.4553, countryCode: 'PHL', wigosId: '0-20003-0-98431' },
            { name: 'Quezon City', latitude: 14.6760, longitude: 121.0437, countryCode: 'PHL', wigosId: '0-20003-0-98432' },
            { name: 'Baguio', latitude: 16.4023, longitude: 120.5960, countryCode: 'PHL', wigosId: '0-20003-0-98433' },
            { name: 'Zamboanga', latitude: 6.9214, longitude: 122.0790, countryCode: 'PHL', wigosId: '0-20003-0-98434' }
        ],
        'BRN': [
            { name: 'Bandar Seri Begawan', latitude: 4.9031, longitude: 114.9398, countryCode: 'BRN', wigosId: '0-20004-0-96315' },
            { name: 'Kuala Belait', latitude: 4.5829, longitude: 114.1829, countryCode: 'BRN', wigosId: '0-20004-0-96316' },
            { name: 'Tutong', latitude: 4.8057, longitude: 114.6536, countryCode: 'BRN', wigosId: '0-20004-0-96317' }
        ],
        'TLS': [
            { name: 'Dili', latitude: -8.5586, longitude: 125.5736, countryCode: 'TLS', wigosId: '0-20005-0-97390' },
            { name: 'Baucau', latitude: -8.4719, longitude: 126.4580, countryCode: 'TLS', wigosId: '0-20005-0-97391' },
            { name: 'Suai', latitude: -9.3167, longitude: 125.2500, countryCode: 'TLS', wigosId: '0-20005-0-97392' }
        ],
        'PNG': [
            { name: 'Port Moresby', latitude: -9.4438, longitude: 147.1803, countryCode: 'PNG', wigosId: '0-20006-0-92001' },
            { name: 'Lae', latitude: -6.7330, longitude: 146.9990, countryCode: 'PNG', wigosId: '0-20006-0-92002' },
            { name: 'Mount Hagen', latitude: -5.8580, longitude: 144.2377, countryCode: 'PNG', wigosId: '0-20006-0-92003' },
            { name: 'Madang', latitude: -5.2246, longitude: 145.7866, countryCode: 'PNG', wigosId: '0-20006-0-92004' },
            { name: 'Goroka', latitude: -6.0831, longitude: 145.3889, countryCode: 'PNG', wigosId: '0-20006-0-92005' }
        ],
        'USA_PACIFIC': [
            { name: 'Honolulu', latitude: 21.3069, longitude: -157.8583, countryCode: 'USA', wigosId: '0-20007-0-91182' },
            { name: 'Hilo', latitude: 19.7241, longitude: -155.0868, countryCode: 'USA', wigosId: '0-20007-0-91183' },
            { name: 'Kahului', latitude: 20.8893, longitude: -156.4303, countryCode: 'USA', wigosId: '0-20007-0-91184' },
            { name: 'Lihue', latitude: 21.9811, longitude: -159.3711, countryCode: 'USA', wigosId: '0-20007-0-91185' },
            { name: 'Guam', latitude: 13.4443, longitude: 144.7937, countryCode: 'USA', wigosId: '0-20007-0-91186' },
            { name: 'Saipan', latitude: 15.1185, longitude: 145.7293, countryCode: 'USA', wigosId: '0-20007-0-91187' },
            { name: 'American Samoa', latitude: -14.2756, longitude: -170.7020, countryCode: 'USA', wigosId: '0-20007-0-91188' }
        ],
        'ALL_COMBINED': [] // Will be filled with all stations
    };
    
    // Combine all stations for ALL_COMBINED
    Object.keys(baseStations).forEach(key => {
        if (key !== 'ALL_COMBINED') {
            baseStations['ALL_COMBINED'] = baseStations['ALL_COMBINED'].concat(baseStations[key]);
        }
    });
    
    // Get stations for selected territory
    let stations = baseStations[territory] || baseStations['IDN'];
    
    // Generate random data for each station based on parameters
    stations = stations.map((station, index) => {
        // Generate different statuses based on monitoring centre
        const stationData = { ...station };
        
        // Add variable info
        stationData.variable = variable;
        stationData.expected = 6; // Default expected value
        stationData.inOSCAR = 'Yes';
        stationData.lastUpdated = new Date().toISOString().split('T')[0];
        stationData.baseline = baseline; // Add baseline info
        
        // Handle alert period type differently
        if (periodType === 'alert') {
            // Generate alert-specific data
            const alertTypes = ['greater-than-10', 'between-5-and-10', 'between-1-and-5', 'between-05-and-1', 'less-than-05', 'less-than-5-values'];
            const randomIndex = Math.floor(Math.random() * alertTypes.length);
            const alertType = alertTypes[randomIndex];
            
            stationData.alertType = alertType;
            
            // Set data based on alert type
            switch(alertType) {
                case 'greater-than-10':
                    stationData.difference = Math.random() * 5 + 10; // 10-15
                    break;
                case 'between-5-and-10':
                    stationData.difference = Math.random() * 5 + 5; // 5-10
                    break;
                case 'between-1-and-5':
                    stationData.difference = Math.random() * 4 + 1; // 1-5
                    break;
                case 'between-05-and-1':
                    stationData.difference = Math.random() * 0.5 + 0.5; // 0.5-1
                    break;
                case 'less-than-05':
                    stationData.difference = Math.random() * 0.5; // 0-0.5
                    break;
                case 'less-than-5-values':
                    stationData.valueCount = Math.floor(Math.random() * 5); // 0-4
                    break;
            }
            
            return stationData;
        }
        
        // Generate different statuses with weighted distribution for non-alert period types
        // More normal and issues-low, fewer special cases
        let status;
        const randomValue = Math.random() * 100;
        
        if (randomValue < 40) {
            // 40% chance for normal status
            status = 'normal';
        } else if (randomValue < 65) {
            // 25% chance for issues-low
            status = 'issues-low';
        } else if (randomValue < 80) {
            // 15% chance for issues-high
            status = 'issues-high';
        } else if (randomValue < 90) {
            // 10% chance for not-received
            status = 'not-received';
        } else if (randomValue < 94) {
            // 4% chance for more-than-100
            status = 'more-than-100';
        } else if (randomValue < 97) {
            // 3% chance for oscar-issue
            status = 'oscar-issue';
        } else {
            // 3% chance for no-match
            status = 'no-match';
        }
        
        // For monthly period, add less-than-10-days status
        if (periodType === 'monthly' && Math.random() < 0.15) { // 15% chance
            status = 'less-than-10-days';
            stationData.daysWithData = Math.floor(Math.random() * 10); // 0-9 days
        }
        
        // Set data based on status
        switch(status) {
            case 'more-than-100':
                stationData.DWD = Math.floor(Math.random() * 2) + 6; // 6-7
                stationData.ECMWF = Math.floor(Math.random() * 2) + 6; // 6-7
                stationData.JMA = Math.floor(Math.random() * 2) + 6; // 6-7
                stationData.NCEP = Math.floor(Math.random() * 2) + 6; // 6-7
                break;
            case 'normal':
                stationData.DWD = Math.floor(Math.random() * 2) + 5; // 5-6
                stationData.ECMWF = Math.floor(Math.random() * 2) + 5; // 5-6
                stationData.JMA = Math.floor(Math.random() * 2) + 5; // 5-6
                stationData.NCEP = Math.floor(Math.random() * 2) + 5; // 5-6
                break;
            case 'issues-low':
                stationData.DWD = Math.floor(Math.random() * 2) + 2; // 2-3
                stationData.ECMWF = Math.floor(Math.random() * 3) + 2; // 2-4
                stationData.JMA = Math.floor(Math.random() * 2) + 2; // 2-3
                stationData.NCEP = Math.floor(Math.random() * 3) + 2; // 2-4
                break;
            case 'issues-high':
                stationData.DWD = Math.floor(Math.random() * 2); // 0-1
                stationData.ECMWF = Math.floor(Math.random() * 2); // 0-1
                stationData.JMA = Math.floor(Math.random() * 2); // 0-1
                stationData.NCEP = Math.floor(Math.random() * 2); // 0-1
                break;
            case 'not-received':
                stationData.DWD = 0;
                stationData.ECMWF = 0;
                stationData.JMA = 0;
                stationData.NCEP = 0;
                break;
            case 'oscar-issue':
                stationData.DWD = 0;
                stationData.ECMWF = 0;
                stationData.JMA = 0;
                stationData.NCEP = 0;
                stationData.oscarIssue = true;
                break;
            case 'no-match':
                stationData.DWD = 0;
                stationData.ECMWF = 0;
                stationData.JMA = 0;
                stationData.NCEP = 0;
                stationData.noMatch = true;
                break;
        }
        
        // If a specific monitoring centre is selected, only show data for that centre
        if (monitoringCentre !== 'ALL') {
            const centres = ['DWD', 'ECMWF', 'JMA', 'NCEP'];
            centres.forEach(centre => {
                if (centre !== monitoringCentre) {
                    delete stationData[centre];
                }
            });
        }
        
        return stationData;
    });
    
    // Calculate metadata
    const metadata = {
        total: stations.length,
        date: new Date().toISOString().split('T')[0],
        variable: variable,
        period: periodType,
        time_period: timePeriod,
        baseline: baseline
    };
    
    return { stations, metadata };
}

// Override determineStationStatus to handle new status types
window.determineStationStatus = function(station) {
    // Handle alert period types
    if (station.alertType) {
        return station.alertType;
    }
    
    // Handle special cases first
    if (station.oscarIssue) return 'oscar-issue';
    if (station.noMatch) return 'no-match';
    if (station.daysWithData !== undefined && station.daysWithData < 10) return 'less-than-10-days';
    
    const expected = station.expected || 6;
    
    // Check for more than 100%
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
    if (totalCoverage > 100) return 'more-than-100';
    if (totalCoverage >= 80) return 'normal';
    if (totalCoverage >= 30) return 'issues-low';
    if (totalCoverage > 0) return 'issues-high';
    return 'not-received';
}

// Override createStationMarker to handle new status types
window.createStationMarker = function(station, status) {
    const colors = {
        // Regular status colors
        'more-than-100': '#e83e8c', // Pink
        'normal': '#28a745',        // Green
        'issues-low': '#ffc107',    // Yellow/Orange
        'issues-high': '#dc3545',   // Red
        'not-received': '#6c757d',  // Dark Gray
        'oscar-issue': '#adb5bd',   // Light Gray
        'no-match': '#ffff00',      // Bright Yellow
        'less-than-10-days': '#ccc', // Light Gray
        
        // Alert status colors
        'greater-than-10': '#ff4500',   // Red/Orange
        'between-5-and-10': '#ffa500',  // Orange
        'between-1-and-5': '#ffff00',   // Yellow
        'between-05-and-1': '#00ff00',  // Green
        'less-than-05': '#008000',      // Dark Green
        'less-than-5-values': '#808080' // Gray
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
        
        // Create circle marker instead of L.marker for better performance
        const createCircleMarker = function(lat, lng) {
            return L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: colors[status],
                color: '#fff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            });
        };
        
        // Create main marker
        const marker = createCircleMarker(lat, lng);
        
        // Create wrapped markers for date line crossing
        const wrappedMarkerEast = createCircleMarker(lat, lng + 360);
        const wrappedMarkerWest = createCircleMarker(lat, lng - 360);

        // Create popup content
        const popupContent = window.createPopupContent(station, status);
        
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
                window.updateInfoPanel(station, status);
            });
        });

        return [marker, wrappedMarkerEast, wrappedMarkerWest];
    } catch (error) {
        console.error('Error creating marker:', error);
        window.showAlert('error', 'Error creating marker: ' + error.message);
        return null;
    }
}

// Initialize legend controls to handle new status types
window.initializeLegendControls = function() {
    const legendItems = document.querySelectorAll('.legend-item.clickable');
    legendItems.forEach(item => {
        item.addEventListener('click', function() {
            const status = this.dataset.status;
            this.classList.toggle('inactive');
            
            // Toggle visibility of markers with this status
            stationMarkers.forEach((markerArray, index) => {
                if (Array.isArray(markerArray) && markerArray.length > 0) {
                    const station = currentStations[index];
                    if (station) {
                        const stationStatus = determineStationStatus(station);
                        if (stationStatus === status) {
                            markerArray.forEach(marker => {
                                if (this.classList.contains('inactive')) {
                                    map.removeLayer(marker);
                                } else {
                                    map.addLayer(marker);
                                }
                            });
                        }
                    }
                }
            });
            
            // Update statistics after filtering
            updateStatistics();
        });
    });
}

// Update popup content to handle special cases
window.createPopupContent = function(station, status) {
    // Handle alert status types
    if (status === 'greater-than-10' || status === 'between-5-and-10' || 
        status === 'between-1-and-5' || status === 'between-05-and-1' || 
        status === 'less-than-05' || status === 'less-than-5-values') {
        
        let differenceText = '';
        
        if (status === 'less-than-5-values') {
            differenceText = `<span class="info-value">Only ${station.valueCount} values available</span>`;
        } else {
            differenceText = `<span class="info-value">${station.difference.toFixed(2)} hPa</span>`;
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
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">${status.replace(/-/g, ' ')}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Difference:</span>
                    ${differenceText}
                </div>
                <div class="info-row">
                    <span class="info-label">Variable:</span>
                    <span class="info-value">${station.variable}</span>
                </div>
            </div>
        </div>
        `;
    }
    
    // Handle less-than-10-days status
    if (status === 'less-than-10-days') {
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
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">Less than 10 days of data</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Days with data:</span>
                    <span class="info-value">${station.daysWithData}</span>
                </div>
            </div>
        </div>
        `;
    }
    
    // Handle special cases
    if (status === 'oscar-issue') {
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
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value error-text">OSCAR Schedule Issue</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Details:</span>
                    <span class="info-value">Station is registered in OSCAR but has scheduling issues.</span>
                </div>
            </div>
        </div>
        `;
    }
    
    if (status === 'no-match') {
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
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value error-text">No Match in OSCAR/Surface</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Details:</span>
                    <span class="info-value">Station could not be matched with any entry in OSCAR/Surface.</span>
                </div>
            </div>
        </div>
        `;
    }

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
                    ${station.DWD !== undefined ? `
                    <tr>
                        <td>DWD</td>
                        <td>${station.DWD}</td>
                        <td>${expected}</td>
                        <td>${Math.round((station.DWD / expected) * 100) + '%'}</td>
                    </tr>` : ''}
                    ${station.ECMWF !== undefined ? `
                    <tr>
                        <td>ECMWF</td>
                        <td>${station.ECMWF}</td>
                        <td>${expected}</td>
                        <td>${Math.round((station.ECMWF / expected) * 100) + '%'}</td>
                    </tr>` : ''}
                    ${station.JMA !== undefined ? `
                    <tr>
                        <td>JMA</td>
                        <td>${station.JMA}</td>
                        <td>${expected}</td>
                        <td>${Math.round((station.JMA / expected) * 100) + '%'}</td>
                    </tr>` : ''}
                    ${station.NCEP !== undefined ? `
                    <tr>
                        <td>NCEP</td>
                        <td>${station.NCEP}</td>
                        <td>${expected}</td>
                        <td>${Math.round((station.NCEP / expected) * 100) + '%'}</td>
                    </tr>` : ''}
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
        </div>
    </div>
    `;
}

// Add missing display functions
window.displayStations = function(stations) {
    try {
        // Ensure map is initialized
        if (!window.map) {
            console.error('Map is not initialized');
            window.showAlert('error', 'Map is not initialized. Please refresh the page.');
            return;
        }
        
        console.log('Displaying stations on map:', stations.length);
        
        // Clear existing markers
        if (window.stationMarkers && window.stationMarkers.length > 0) {
            window.stationMarkers.forEach(markerArray => {
                if (Array.isArray(markerArray)) {
                    markerArray.forEach(marker => {
                        if (marker) window.map.removeLayer(marker);
                    });
                } else if (markerArray) {
                    window.map.removeLayer(markerArray);
                }
            });
        }
        window.stationMarkers = [];

        // Add new markers
        stations.forEach(station => {
            const status = window.determineStationStatus(station);
            const markers = window.createStationMarker(station, status);
            if (markers) {
                // Always show markers initially since all statuses are active by default
                markers.forEach(marker => {
                    if (marker) {
                        try {
                            window.map.addLayer(marker);
                        } catch (e) {
                            console.error('Error adding marker to map:', e);
                        }
                    }
                });
                window.stationMarkers.push(markers);
            }
        });

        // Store current stations for filtering
        window.currentStations = stations;

        // Update statistics after displaying stations
        window.updateStatistics({
            total: stations.length,
            stations: stations
        });
        
        console.log(`Displayed ${stations.length} stations on map`);
    } catch (error) {
        console.error('Error displaying stations:', error);
        window.showAlert('error', 'Error displaying stations: ' + error.message);
    }
};

window.updateStatistics = function(metadata) {
    try {
        // Calculate stats from visible stations
        let total = window.currentStations ? window.currentStations.length : 0;
        let issues = 0;
        
        if (window.currentStations && window.currentStations.length > 0) {
            window.currentStations.forEach(station => {
                const status = window.determineStationStatus(station);
                if (status === 'issues-high' || status === 'issues-low' || 
                    status === 'not-received' || status === 'oscar-issue' || 
                    status === 'no-match') {
                    issues++;
                }
            });
        }
        
        // Update the statistics in the UI
        const totalElement = document.getElementById('totalStations');
        const issuesElement = document.getElementById('issuesReports');
        const percentElement = document.getElementById('issuesPercent');
        
        if (totalElement) totalElement.textContent = total;
        if (issuesElement) issuesElement.textContent = issues;
        
        // Update percentage if there are stations
        if (percentElement && total > 0) {
            const percentage = Math.round((issues / total) * 100);
            percentElement.textContent = `${percentage}%`;
        } else if (percentElement) {
            percentElement.textContent = '0%';
        }
        
        // Update territory stats if available
        const territoryStatsElement = document.getElementById('territoryStats');
        if (territoryStatsElement) {
            const territory = document.getElementById('regionSelect').value;
            const territoryName = document.getElementById('regionSelect').options[document.getElementById('regionSelect').selectedIndex].text;
            territoryStatsElement.textContent = ` - ${territoryName}`;
        }
        
        console.log('Statistics updated:', { total, issues });
    } catch (error) {
        console.error('Error updating statistics:', error);
    }
};

window.initializeControls = function() {
    // Initialize legend controls
    window.initializeLegendControls();
    
    // Region selector
    document.getElementById('regionSelect').addEventListener('change', function() {
        const territory = this.value;
        window.updateMapCenterForTerritory(territory);
        window.loadStationData();
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
            window.loadStationData();
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
                window.loadStationData();
            });
        });
    }

    // Baseline buttons
    const baselineButtons = document.querySelectorAll('.baseline .baseline-btn');
    if (baselineButtons.length > 0) {
        baselineButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                baselineButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                window.loadStationData();
            });
        });
    }

    // Variable type selector
    const variableSelect = document.getElementById('variableType');
    if (variableSelect) {
        variableSelect.addEventListener('change', function() {
            window.loadStationData();
        });
    }

    // Date control
    document.getElementById('observationDate').addEventListener('change', window.loadStationData);
};

window.initializeDefaults = function() {
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
    document.getElementById('regionSelect').value = window.DEFAULT_REGION || 'ALL_COMBINED';
    
    // Set default time period button (00)
    const timeButtons = document.querySelectorAll('.time-periods .time-btn');
    if (timeButtons.length > 0) {
        timeButtons.forEach(btn => btn.classList.remove('active'));
        const defaultTimeBtn = document.querySelector('.time-periods .time-btn[data-period="00"]');
        if (defaultTimeBtn) {
            defaultTimeBtn.classList.add('active');
        }
    }
    
    // Set default baseline button (oscar)
    const baselineButtons = document.querySelectorAll('.baseline .baseline-btn');
    if (baselineButtons.length > 0) {
        baselineButtons.forEach(btn => btn.classList.remove('active'));
        const defaultBaselineBtn = document.querySelector('.baseline .baseline-btn[data-baseline="oscar"]');
        if (defaultBaselineBtn) {
            defaultBaselineBtn.classList.add('active');
        }
    }
};

// Override the loadStationData function to use dummy data
window.loadStationData = function() {
    if (window.isLoading) {
        console.log('Skipping data load - already loading');
        return;
    }
    
    let territory = document.getElementById('regionSelect').value;
    const variable = document.getElementById('variableType').value;
    const date = document.getElementById('observationDate').value;
    const periodType = document.getElementById('periodType').value;
    const timePeriod = document.querySelector('.time-periods .time-btn.active')?.dataset.period;
    const monitoringCentre = document.getElementById('monitoringCentre').value;
    const baseline = document.querySelector('.baseline .baseline-btn.active')?.dataset.baseline || 'oscar';
    
    // Update URL parameters
    window.updateURLParameters({
        territory: territory,
        variable: variable,
        date: date,
        period: periodType,
        time_period: periodType === 'six-hour' ? timePeriod : null,
        monitoring_centre: monitoringCentre,
        baseline: baseline
    });
    
    console.log('Loading dummy station data with params:', {
        region: territory,
        variable: variable,
        date: date,
        periodType: periodType,
        timePeriod: timePeriod,
        monitoringCentre: monitoringCentre,
        baseline: baseline
    });
    
    window.isLoading = true;
    window.showLoading(true);
    
    // Simulate API delay
    setTimeout(() => {
        try {
            // Generate dummy data based on selected parameters
            const dummyData = generateDummyData(territory, variable, periodType, timePeriod, monitoringCentre, baseline);
            
            window.currentStations = dummyData.stations;
            window.displayStations(window.currentStations);
            window.updateStatistics(dummyData.metadata);
            
            console.log('Dummy data load complete:', {
                stationCount: window.currentStations.length,
                metadata: dummyData.metadata
            });
            
            window.showLoading(false);
            window.isLoading = false;
            
        } catch (error) {
            console.error('Error loading dummy station data:', error);
            window.showAlert('error', `Failed to load station data: ${error.message}`);
            window.showLoading(false);
            window.isLoading = false;
        }
    }, 800); // Simulate API delay of 800ms
};

// Initialize the map if it doesn't exist
function initializeMap() {
    if (!window.map && document.getElementById('map')) {
        console.log('Initializing map...');
        try {
            // Set default view to Indonesia with appropriate zoom level
            window.map = L.map('map', {
                center: [-2.5, 118],
                zoom: 5,
                minZoom: 3,
                maxZoom: 18,
                zoomControl: true,
                scrollWheelZoom: true,
                fullscreenControl: true,
                worldCopyJump: true
            });
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(window.map);
            
            console.log('Map initialized successfully');
            
            // Add a simple error handler
            window.map.on('error', function(e) {
                console.error('Map error:', e);
            });
            
            return true;
        } catch (error) {
            console.error('Error initializing map:', error);
            window.showAlert('error', 'Failed to initialize map: ' + error.message);
            
            // Add fallback error message to the map container
            const mapContainer = document.getElementById('map');
            if (mapContainer) {
                mapContainer.innerHTML = `
                    <div style="padding: 20px; text-align: center; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
                        <h3>Map Initialization Error</h3>
                        <p>There was a problem loading the map. Please try refreshing the page.</p>
                        <button onclick="location.reload()" style="padding: 8px 16px; background: #0d6efd; color: white; 
                                border: none; border-radius: 4px; margin-top: 10px; cursor: pointer;">
                            Refresh Page
                        </button>
                    </div>
                `;
            }
            return false;
        }
    }
    return window.map ? true : false;
}