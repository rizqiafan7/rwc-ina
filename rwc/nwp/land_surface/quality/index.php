<?php
require_once '../../../config/config.php';
$pageTitle = "Availability of surface land observations (GBON)";
include '../../../includes/header.php';
include '../../../includes/navigation.php';
?>


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Indonesian Station Monitoring</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/1.6.0/Control.FullScreen.css" />
<link rel="stylesheet" href="<?= asset('css/monitoring.css') ?>" />

<style>
/* Fullscreen control positioning */
.leaflet-control-fullscreen {
    margin-top: 10px !important;
}

.leaflet-control-fullscreen a {
    background: #fff;
    border-bottom: 1px solid #ccc;
    width: 30px;
    height: 30px;
    line-height: 30px;
    display: block;
    text-align: center;
    text-decoration: none;
    color: black;
    border-radius: 4px;
}

.leaflet-control-fullscreen a:hover {
    background-color: #f4f4f4;
}

/* Position below zoom controls */
.leaflet-control-fullscreen.leaflet-control {
    margin-top: 45px !important;
}

/* Fullscreen mode adjustments */
.leaflet-pseudo-fullscreen {
    position: fixed !important;
    width: 100% !important;
    height: 100% !important;
    top: 0 !important;
    left: 0 !important;
    z-index: 99999;
}

.leaflet-container:-webkit-full-screen {
    width: 100% !important;
    height: 100% !important;
}

.leaflet-container:-ms-fullscreen {
    width: 100% !important;
    height: 100% !important;
}

.leaflet-container:full-screen {
    width: 100% !important;
    height: 100% !important;
}

/* Legend dot colors */
.dot {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    margin-right: 8px;
}

.dot.more-than-100 {
    background-color: #e83e8c; /* Pink */
}

.dot.normal {
    background-color: #28a745; /* Green */
}

.dot.issues-low {
    background-color: #ffc107; /* Yellow/Orange */
}

.dot.issues-high {
    background-color: #dc3545; /* Red */
}

.dot.not-received {
    background-color: #6c757d; /* Dark Gray */
}

.dot.oscar-issue {
    background-color: #adb5bd; /* Light Gray */
}

.dot.no-match {
    background-color: #ffff00; /* Bright Yellow */
}

.dot.less-than-10-days {
    background-color: #ccc; /* Light Gray */
}

/* Observation and model differences colors */
.dot.greater-than-10 {
    background-color: #ff4500; /* Red/Orange */
}

.dot.between-5-and-10 {
    background-color: #ffa500; /* Orange */
}

.dot.between-1-and-5 {
    background-color: #ffff00; /* Yellow */
}

.dot.between-05-and-1 {
    background-color: #00ff00; /* Green */
}

.dot.less-than-05 {
    background-color: #008000; /* Dark Green */
}

.dot.less-than-5-values {
    background-color: #808080; /* Gray */
}

/* Info icon styling */
.info-icon {
    margin-left: 5px;
    color: #6c757d;
    cursor: pointer;
}

/* Baseline button styles */
.baseline {
    display: flex;
    gap: 5px;
    align-items: center;
}

.baseline-btn {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 6px 15px;
    cursor: pointer;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 14px;
}

.baseline-separator {
    opacity: 0.7;
    margin: 0 1px;
}

.baseline-type {
    font-weight: normal;
    opacity: 0.8;
}

/* Adjust control layout */
.controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    align-items: flex-end;
}

.control {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.label {
    font-weight: bold;
    font-size: 0.9rem;
    margin-bottom: 3px;
}

/* Legend styling */
.panel.legend {
    max-width: 280px;
}

.legend-item {
    display: flex;
    align-items: center;
    padding: 4px 8px;
    font-size: 13px;
    border-radius: 4px;
    margin-bottom: 3px;
}

.legend-item.clickable {
    cursor: pointer;
}

.legend-item.clickable:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Info icon styling */
.info-icon {
    margin-left: 5px;
    color: #6c757d;
    cursor: pointer;
    font-size: 12px;
}

/* Baseline button styles */
.baseline {
    display: flex;
    gap: 5px;
    align-items: center;
}

.baseline-btn {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 6px 15px;
    cursor: pointer;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 14px;
}

.baseline-separator {
    opacity: 0.7;
    margin: 0 1px;
}

.baseline-type {
    font-weight: normal;
    opacity: 0.8;
}
</style>
</head>


<div class="header">
<h1 class="title">
<i class="fas fa-satellite-dish"></i>
Quality of surface land observations (Global NWP)
</h1>

<div class="controls">
<div class="control">
<label class="label"><i class="fas fa-clock"></i> Type Of Period</label>
<select class="select" id="periodType">
<option value="six-hour">Six-hour</option>
<option value="daily">Daily</option>
<option value="monthly">Monthly</option>
<option value="alert">Alert</option>
</select>
</div>

<div class="control">
<label class="label"><i class="fas fa-thermometer-half"></i> Variable</label>
<select class="select" id="variableType">
<option value="pressure">Surface Pressure</option>
<option value="geopotential">Geopotential</option>
<option value="temperature">2m Temperature</option>
<option value="zonal_wind">10m Zonal Wind</option>
<option value="meridional_wind">10m Meridional Wind</option>
<option value="humidity">2m Relative Humidity</option>
</select>
</div>

<div class="control">
<label class="label"><i class="fas fa-building"></i> Monitoring Centre</label>
<select class="select" id="monitoringCentre">
<option value="ALL">All</option>
<option value="DWD">DWD</option>
<option value="ECMWF">ECMWF</option>
<option value="JMA">JMA</option>
<option value="NCEP">NCEP</option>
</select>
</div>

<div class="control">
<label class="label"><i class="fas fa-globe-asia"></i> Region</label>
<select class="select" id="regionSelect">
<option value="ALL_COMBINED" selected>All Stations</option>
<optgroup label="Regional WMO V">
<option value="IDN">Indonesia (IDN)</option>
<option value="MYS">Malaysia (MYS)</option>
<option value="SGP">Singapore (SGP)</option>
<option value="PHL">Philippines (PHL)</option>
<option value="BRN">Brunei (BRN)</option>
<option value="TLS">Timor Leste (TLS)</option>
<option value="PNG">Papua New Guinea (PNG)</option>
</optgroup>
<optgroup label="Regional USA (PASIFIC)">
<option value="USA_PACIFIC">USA Stations (Pacific Region)</option>
</optgroup>
</select>
</div>

<div class="control">
<label class="label"><i class="fas fa-calendar-alt"></i> Date</label>
<input type="date" class="date-input" id="observationDate">
</div>

<div class="control">
<label class="label"><i class="fas fa-clock"></i> Six-hour period</label>
<div class="time-periods">
<button class="time-btn" data-period="00">00</button>
<button class="time-btn" data-period="06">06</button>
<button class="time-btn" data-period="12">12</button>
<button class="time-btn active" data-period="18">18</button>
</div>
</div>

</div>
</div>

<div class="map-container">
<div class="loading" id="loading">
<div class="spinner"></div>
<div>Loading GBON stations...</div>
</div>

<div class="map-controls">
<button class="ctrl-btn" onclick="resetView()" title="Reset View">
<i class="fas fa-home"></i>
</button>
<button class="ctrl-btn" onclick="refresh()" title="Refresh">
<i class="fas fa-sync-alt"></i>
</button>
<button class="ctrl-btn" onclick="saveData()" title="Export Data">
<i class="fas fa-save"></i>
</button>
<button class="ctrl-btn" onclick="toggleLegends()" title="Toggle Legends">
<i class="fas fa-eye"></i>
</button>
</div>

<div id="map"></div>

<div class="panel legend">
<div class="panel-title">
<i class="fas fa-info-circle"></i>
<span id="observationTitle">Received Observations</span>
</div>
<div class="legend-item clickable" data-status="more-than-100">
<div class="dot more-than-100"></div>
<span>More than 100%</span>
</div>
<div class="legend-item clickable" data-status="normal">
<div class="dot normal"></div>
<span>Normal (≥ 80%)</span>
</div>
<div class="legend-item clickable" data-status="issues-low">
<div class="dot issues-low"></div>
<span>Availability issues (≥ 30%)</span>
</div>
<div class="legend-item clickable" data-status="issues-high">
<div class="dot issues-high"></div>
<span>Availability issues (< 30%)</span>
</div>
<div class="legend-item clickable" data-status="not-received">
<div class="dot not-received"></div>
<span>Not received in period</span>
</div>
<div class="legend-item clickable" data-status="oscar-issue">
<div class="dot oscar-issue"></div>
<span>OSCAR schedule issue</span>
<i class="fas fa-info-circle info-icon"></i>
</div>
<div class="legend-item clickable" data-status="no-match">
<div class="dot no-match"></div>
<span>No match in OSCAR/Surface</span>
<i class="fas fa-info-circle info-icon"></i>
</div>
<div class="legend-item clickable" data-status="less-than-10-days" style="display: none;" id="lessThanTenDays">
<div class="dot less-than-10-days"></div>
<span>Less than 10 days</span>
</div>
</div>

<div class="panel status">
<div class="panel-title">
<i class="fas fa-chart-bar"></i>
Statistics Observations
<span class="panel-subtitle" id="territoryStats"></span>
</div>
<div class="status-grid">
<div class="stat-item">
<span class="stat-count" id="totalStations">-</span>
<span class="stat-label">Total Stations</span>
</div>
<div class="stat-item">
<span class="stat-count" id="issuesReports">-</span>
<span class="stat-label">Issues</span>
<span class="stat-percent" id="issuesPercent">-%</span>
</div>
</div>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/1.6.0/Control.FullScreen.min.js"></script>
<script>
// Initialize global variables
window.map = null;
window.stationMarkers = [];
window.currentStations = [];
window.isLoading = false;

// Add this before including land_surface.js
document.addEventListener('DOMContentLoaded', function() {
    const title = document.getElementById('observationTitle');
    const lessThanTenDays = document.getElementById('lessThanTenDays');
    
    document.getElementById('periodType').addEventListener('change', function() {
        if (this.value === 'monthly') {
            title.textContent = 'Observation and model differences';
            lessThanTenDays.style.display = 'flex';
            
            // Show monthly legend
            showModelDifferencesLegend('monthly');
        } else if (this.value === 'alert') {
            title.textContent = 'Observation and model differences';
            lessThanTenDays.style.display = 'none';
            
            // Show alert-specific legend
            showModelDifferencesLegend('alert');
        } else if (this.value === 'daily') {
            title.textContent = 'Observation and model differences';
            lessThanTenDays.style.display = 'none';
            
            // Show daily legend
            showModelDifferencesLegend('daily');
        } else {
            // six-hour
            title.textContent = 'Observation and model differences';
            lessThanTenDays.style.display = 'none';
            
            // Show six-hour legend
            showModelDifferencesLegend('six-hour');
        }
    });
    
    function showModelDifferencesLegend(periodType) {
        // Hide all regular legend items
        document.querySelectorAll('.legend-item').forEach(item => {
            item.style.display = 'none';
        });
        
        // Create and show alert-specific legend items
        const legendContainer = document.querySelector('.panel.legend');
        
        // Remove existing alert legend if it exists
        const existingLegend = document.getElementById('alert-legend');
        if (existingLegend) {
            existingLegend.remove();
        }
        
        // Create new legend based on period type
        const alertLegend = document.createElement('div');
        alertLegend.id = 'alert-legend';
        
        let legendHeader = '';
        let legendItems = '';
        
        // Set header and items based on period type
        if (periodType === 'alert') {
            legendHeader = '<div class="panel-subtitle">5-day moving average</div><div class="panel-subtitle">Absolute values (hPa)</div>';
        } else if (periodType === 'monthly') {
            legendHeader = '<div class="panel-subtitle">Absolute values (hPa)</div>';
        } else {
            // six-hour and daily
            legendHeader = '<div class="panel-subtitle">Absolute values (hPa)</div>';
        }
        
        // Common legend items for all types
        legendItems = `
            <div class="legend-item clickable" data-status="greater-than-10">
                <div class="dot greater-than-10"></div>
                <span>> 10</span>
            </div>
            <div class="legend-item clickable" data-status="between-5-and-10">
                <div class="dot between-5-and-10"></div>
                <span>5 < x ≤ 10</span>
            </div>
            <div class="legend-item clickable" data-status="between-1-and-5">
                <div class="dot between-1-and-5"></div>
                <span>1 < x ≤ 5</span>
            </div>
            <div class="legend-item clickable" data-status="between-05-and-1">
                <div class="dot between-05-and-1"></div>
                <span>0.5 < x ≤ 1</span>
            </div>
            <div class="legend-item clickable" data-status="less-than-05">
                <div class="dot less-than-05"></div>
                <span>≤ 0.5</span>
            </div>
        `;
        
        // Add specific items based on period type
        if (periodType === 'alert') {
            legendItems += `
                <div class="legend-item clickable" data-status="less-than-5-values">
                    <div class="dot less-than-5-values"></div>
                    <span>Less than 5 values</span>
                </div>
            `;
        } else if (periodType === 'monthly') {
            legendItems += `
                <div class="legend-item clickable" data-status="less-than-10-days">
                    <div class="dot less-than-10-days"></div>
                    <span>Less than 10 days</span>
                </div>
            `;
        }
        
        alertLegend.innerHTML = legendHeader + legendItems;
        legendContainer.appendChild(alertLegend);
    }
    
    function showRegularLegend() {
        // Hide alert legend if it exists
        const alertLegend = document.getElementById('alert-legend');
        if (alertLegend) {
            alertLegend.remove();
        }
        
        // Show regular legend items
        document.querySelectorAll('.legend-item').forEach(item => {
            if (item.id !== 'lessThanTenDays') {
                item.style.display = 'flex';
            }
        });
    }
    
    // Initialize with the default view based on the selected period type
    const initialPeriodType = document.getElementById('periodType').value;
    if (initialPeriodType === 'monthly') {
        title.textContent = 'Observation and model differences';
        lessThanTenDays.style.display = 'flex';
        showModelDifferencesLegend('monthly');
    } else if (initialPeriodType === 'alert') {
        title.textContent = 'Observation and model differences';
        showModelDifferencesLegend('alert');
    } else if (initialPeriodType === 'daily') {
        title.textContent = 'Observation and model differences';
        showModelDifferencesLegend('daily');
    } else {
        // six-hour
        title.textContent = 'Observation and model differences';
        showModelDifferencesLegend('six-hour');
    }
    
    // Debug mode for development
    window.debugMode = true;
    console.log('Debug mode enabled');
});
</script>

<!-- Load only the necessary scripts -->
<script src="<?= asset('js/nwp_land_surface.js') ?>"></script>


<?php include '../../../includes/footer.php'; ?> 