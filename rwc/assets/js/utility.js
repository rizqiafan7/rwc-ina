// Add toggle legends function 
function toggleLegends() { 
    const legendPanels = document.querySelectorAll('.panel'); 
    const toggleButton = document.querySelector('button[onclick="toggleLegends()"] i'); 
    
    legendsVisible = !legendsVisible; 
    
    legendPanels.forEach(panel => { 
        if (legendsVisible) { 
            panel.classList.remove('hidden'); 
            toggleButton.classList.remove('fa-eye-slash'); 
            toggleButton.classList.add('fa-eye'); 
        } else { 
            panel.classList.add('hidden'); 
            toggleButton.classList.remove('fa-eye'); 
            toggleButton.classList.add('fa-eye-slash'); 
        } 
    }); 
}

// Filter markers based on active status filters
function filterMarkersByStatus() {
    // Get active status filters
    const activeStatuses = [];
    document.querySelectorAll('.legend-item.clickable.active').forEach(item => {
        activeStatuses.push(item.dataset.status);
    });
    
    console.log('Active status filters:', activeStatuses);
    
    // Filter markers
    stationMarkers.forEach(markerArray => {
        if (Array.isArray(markerArray)) {
            const station = currentStations[stationMarkers.indexOf(markerArray)];
            if (station) {
                const status = determineStationStatus(station);
                
                // Show or hide all markers in the array based on status
                markerArray.forEach(marker => {
                    if (marker) {
                        if (activeStatuses.includes(status)) {
                            // Show marker
                            if (!map.hasLayer(marker)) {
                                marker.addTo(map);
                            }
                        } else {
                            // Hide marker
                            if (map.hasLayer(marker)) {
                                marker.remove();
                            }
                        }
                    }
                });
            }
        }
    });
    
    // Update statistics to reflect visible markers only
    updateStatistics();
}

// Initialize legend controls
function initializeLegendControls() {
    document.querySelectorAll('.legend-item.clickable').forEach(item => {
        // Set initial state to visible (not clicked)
        item.dataset.hidden = 'false';
        
        item.addEventListener('click', function() {
            const status = this.dataset.status;
            const isHidden = this.dataset.hidden === 'true';
            
            // Toggle hidden state
            this.dataset.hidden = (!isHidden).toString();
            
            // Toggle visibility class
            this.classList.toggle('hidden');
            
            // Show/hide markers of this status
            stationMarkers.forEach(markerArray => {
                if (Array.isArray(markerArray)) {
                    const station = currentStations[stationMarkers.indexOf(markerArray)];
                    if (station && determineStationStatus(station) === status) {
                        markerArray.forEach(marker => {
                            if (marker) {
                                if (isHidden) {
                                    marker.addTo(map); // Show marker
                                } else {
                                    marker.remove(); // Hide marker
                                }
                            }
                        });
                    }
                }
            });
            
            // Update statistics
            updateStatistics();
        });
    });
}

// Initialize station status filters
function initializeStatusFilters() {
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
            showAlert(
                isActive ? 'info' : 'warning',
                isActive ? `Showing ${statusName} stations` : `Hiding ${statusName} stations`
            );
        });
    });
}

// Function to save station data
function saveData() {
    if (currentStations.length === 0) {
        showAlert('warning', 'No data to save');
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
            'center'
        ];
        
        // Convert stations data to CSV rows
        const csvRows = [headers.join(',')];
        
        // Centers to include in export
        const centers = ['DWD', 'ECMWF', 'JMA', 'NCEP'];
        
        currentStations.forEach(station => {
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
                    `"${center}"`
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
        
        // Determine if we're on the upper air or land surface page
        const isUpperAir = window.location.href.includes('upper-air') || 
                          document.title.toLowerCase().includes('upper air');
        
        // Get period type based on page type
        let period, time_period, filename;
        
        if (isUpperAir) {
            // Upper air uses daily/monthly
            period = document.getElementById('periodType').value || 'daily';
            filename = `upper_air_${territory}_${variable}_${period}_${selectedDate}.csv`;
        } else {
            // Land surface uses six_hour/daily/monthly
            period = document.getElementById('periodType').value || 'six_hour';
            
            // For six_hour period, include time period in filename
            if (period === 'six_hour') {
                time_period = document.querySelector('.time-periods .time-btn.active')?.dataset.period || '00';
                filename = `land_surface_${territory}_${variable}_${period}_${time_period}_${selectedDate}.csv`;
            } else {
                filename = `land_surface_${territory}_${variable}_${period}_${selectedDate}.csv`;
            }
        }
        
        // Create a link element to trigger the download
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        
        // Append link to body, click it, then remove it
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        const dataType = isUpperAir ? 'Upper air' : 'Land surface';
        showAlert('success', `${dataType} station data saved as CSV successfully`);
    } catch (error) {
        console.error('Error saving data:', error);
        showAlert('error', 'Failed to save data: ' + error.message);
    }
}

// Show/hide loading indicator
function showLoading(show) { 
    isLoading = show; 
    document.getElementById('loading').style.display = show ? 'flex' : 'none'; 
}

// Show alert message
function showAlert(type, message) { 
    const alert = document.createElement('div'); 
    alert.className = `alert ${type}`; 
    alert.textContent = message; 
    document.body.appendChild(alert); 

    setTimeout(() => alert.classList.add('show'), 100); 
    setTimeout(() => { 
        alert.classList.remove('show'); 
        setTimeout(() => document.body.removeChild(alert), 300); 
    }, 3000); 
}

// Reset map view to fit all markers or default view
function resetView() { 
    if (stationMarkers.length > 0) { 
        // Flatten the array of marker arrays and filter out any null/undefined values
        const allMarkers = stationMarkers
            .flat()
            .filter(marker => marker && map.hasLayer(marker));
            
        if (allMarkers.length > 0) {
            const group = new L.featureGroup(allMarkers);
            map.fitBounds(group.getBounds().pad(0.1));
        } else {
            map.setView([-2.5, 118], 5);
        }
    } else { 
        map.setView([-2.5, 118], 5); 
    } 
} 