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

/* Six-hour specific colors */
.dot.complete-launch {
    background-color: #28a745; /* Green */
}

.dot.incomplete-variables {
    background-color: #ffc107; /* Yellow */
}

.dot.incomplete-layers {
    background-color: #fd7e14; /* Orange */
}

/* Observation and model differences colors */
.dot.greater-than-10 {
    background-color: #ff4500; /* Red/Orange */
}

.dot.between-5-and-10 {
    background-color: #ffa500; /* Orange */
}

.dot.between-3-and-5 {
    background-color: #ffff00; /* Yellow */
}

.dot.between-1-and-3 {
    background-color: #90EE90; /* Light Green */
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
</style>
</head>


<div class="header">
<h1 class="title">
<i class="fas fa-satellite-dish"></i>
Availability of surface land observations (Global NWP)
</h1>

<div class="controls">
<div class="control">
<label class="label"><i class="fas fa-clock"></i> Type Of Period</label>
<select class="select" id="periodType">
<option value="six-hour">Six-hour</option>
<option value="daily">Daily</option>
<option value="monthly">Monthly</option>
</select>
</div>

<div class="control">
<label class="label"><i class="fas fa-thermometer-half"></i> Variable</label>
<select class="select" id="variableType">
<option value="pressure">Surface Pressure</option>
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
<span id="observationTitle">Observation and model differences</span>
<div class="panel-subtitle" id="observationSubtitle">Root Mean Square Error (K)</div>
</div>

<!-- Six-hour legend items -->
<div id="sixhour-legend" style="display: block;">
<div class="legend-item clickable" data-status="greater-than-10">
<div class="dot greater-than-10"></div>
<span>> 10</span>
</div>
<div class="legend-item clickable" data-status="between-5-and-10">
<div class="dot between-5-and-10"></div>
<span>5 < x ≤ 10</span>
</div>
<div class="legend-item clickable" data-status="between-3-and-5">
<div class="dot between-3-and-5"></div>
<span>3 < x ≤ 5</span>
</div>
<div class="legend-item clickable" data-status="between-1-and-3">
<div class="dot between-1-and-3"></div>
<span>1 < x ≤ 3</span>
</div>
<div class="legend-item clickable" data-status="between-05-and-1">
<div class="dot between-05-and-1"></div>
<span>0.5 < x ≤ 1</span>
</div>
<div class="legend-item clickable" data-status="less-than-05">
<div class="dot less-than-05"></div>
<span>≤ 0.5</span>
</div>
</div>

<!-- Daily legend items -->
<div id="daily-legend" style="display: none;">
<div class="legend-item clickable" data-status="greater-than-10">
<div class="dot greater-than-10"></div>
<span>> 10</span>
</div>
<div class="legend-item clickable" data-status="between-5-and-10">
<div class="dot between-5-and-10"></div>
<span>5 < x ≤ 10</span>
</div>
<div class="legend-item clickable" data-status="between-3-and-5">
<div class="dot between-3-and-5"></div>
<span>3 < x ≤ 5</span>
</div>
<div class="legend-item clickable" data-status="between-1-and-3">
<div class="dot between-1-and-3"></div>
<span>1 < x ≤ 3</span>
</div>
<div class="legend-item clickable" data-status="between-05-and-1">
<div class="dot between-05-and-1"></div>
<span>0.5 < x ≤ 1</span>
</div>
<div class="legend-item clickable" data-status="less-than-05">
<div class="dot less-than-05"></div>
<span>≤ 0.5</span>
</div>
</div>

<!-- Monthly legend items -->
<div id="monthly-legend" style="display: none;">
<div class="legend-item clickable" data-status="greater-than-10">
<div class="dot greater-than-10"></div>
<span>> 10</span>
</div>
<div class="legend-item clickable" data-status="between-5-and-10">
<div class="dot between-5-and-10"></div>
<span>5 < x ≤ 10</span>
</div>
<div class="legend-item clickable" data-status="between-3-and-5">
<div class="dot between-3-and-5"></div>
<span>3 < x ≤ 5</span>
</div>
<div class="legend-item clickable" data-status="between-1-and-3">
<div class="dot between-1-and-3"></div>
<span>1 < x ≤ 3</span>
</div>
<div class="legend-item clickable" data-status="between-05-and-1">
<div class="dot between-05-and-1"></div>
<span>0.5 < x ≤ 1</span>
</div>
<div class="legend-item clickable" data-status="less-than-05">
<div class="dot less-than-05"></div>
<span>≤ 0.5</span>
</div>
<div class="legend-item clickable" data-status="less-than-10-days">
<div class="dot less-than-10-days"></div>
<span>Less than 10 days</span>
</div>
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
    // Function to update legend and controls based on period type
    function updateLegendAndControls(periodType) {
        // Hide all legend sections first
        document.getElementById('sixhour-legend').style.display = 'none';
        document.getElementById('daily-legend').style.display = 'none';
        document.getElementById('monthly-legend').style.display = 'none';
        
        // Update title and subtitle separately to preserve HTML structure
        const titleElement = document.getElementById('observationTitle');
        const subtitleElement = document.getElementById('observationSubtitle');
        
        switch(periodType) {
            case 'six-hour':
                titleElement.textContent = 'Observation and model differences';
                subtitleElement.textContent = 'Root Mean Square Error (K)';
                document.getElementById('sixhour-legend').style.display = 'block';
                break;
                
            case 'daily':
                titleElement.textContent = 'Observation and model differences';
                subtitleElement.textContent = 'Root Mean Square Error (K)';
                document.getElementById('daily-legend').style.display = 'block';
                break;
                
            case 'monthly':
                titleElement.textContent = 'Observation and model differences';
                subtitleElement.textContent = 'Root Mean Square Error (K)';
                document.getElementById('monthly-legend').style.display = 'block';
                break;
                
            default:
                titleElement.textContent = 'Observation and model differences';
                subtitleElement.textContent = 'Root Mean Square Error (K)';
                document.getElementById('sixhour-legend').style.display = 'block';
                break;
        }
    }
    
    // Initial setup based on default selection
    updateLegendAndControls(document.getElementById('periodType').value);
    
    // Listen for period type changes
    document.getElementById('periodType').addEventListener('change', function() {
        updateLegendAndControls(this.value);
    });
    
    // Debug mode for development
    window.debugMode = true;
    console.log('Debug mode enabled');
});
</script>

<!-- Load only the necessary scripts -->
<script src="<?= asset('js/nwp_land_surface.js') ?>"></script>

<?php include '../../../includes/footer.php'; ?>