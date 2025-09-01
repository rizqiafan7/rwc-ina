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
    background-color: #6c757d;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 6px 15px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: background-color 0.2s;
}

.baseline-btn:hover {
    background-color: #5a6268;
}

.baseline-btn.active {
    background-color: #007bff;
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

/* Hide baseline control for six-hour */
.control.baseline-control {
    display: flex;
}

.control.baseline-control.hidden {
    display: none;
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
<label class="label"><i class="fas fa-chart-bar"></i> Variable</label>
<select class="select" id="variableType">
<option value="temperature">Temperature</option>
<option value="humidity">Humidity</option>
<option value="wind">Wind</option>
<option value="pressure">Pressure</option>
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

<div class="control baseline-control">
<label class="label"><i class="fas fa-chart-line"></i> Baseline</label>
<div class="baseline">
<button class="baseline-btn active" data-baseline="oscar">Oscar</button>
<button class="baseline-btn" data-baseline="2-daily">2-daily</button>
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

<!-- Six-hour legend items -->
<div id="sixhour-legend" style="display: none;">
<div class="legend-item clickable" data-status="complete-launch">
<div class="dot complete-launch"></div>
<span>At least one complete launch (all variables and layers)</span>
</div>
<div class="legend-item clickable" data-status="incomplete-variables">
<div class="dot incomplete-variables"></div>
<span>Incomplete launch (missing variables)</span>
</div>
<div class="legend-item clickable" data-status="incomplete-layers">
<div class="dot incomplete-layers"></div>
<span>Incomplete launch (missing layers)</span>
</div>
<div class="legend-item clickable" data-status="not-received">
<div class="dot not-received"></div>
<span>Not received in period</span>
</div>
<div class="legend-item clickable" data-status="no-match">
<div class="dot no-match"></div>
<span>No match in OSCAR/Surface</span>
<i class="fas fa-info-circle info-icon"></i>
</div>
</div>

<!-- Daily legend items -->
<div id="daily-legend" style="display: none;">
<div class="legend-item clickable" data-status="more-than-100">
<div class="dot more-than-100"></div>
<span>More than declared in OSCAR/Surface</span>
</div>
<div class="legend-item clickable" data-status="normal">
<div class="dot normal"></div>
<span>No issue</span>
</div>
<div class="legend-item clickable" data-status="issues-low">
<div class="dot issues-low"></div>
<span>Completeness issue</span>
</div>
<div class="legend-item clickable" data-status="issues-high">
<div class="dot issues-high"></div>
<span>Availability issue</span>
</div>
<div class="legend-item clickable" data-status="not-received">
<div class="dot not-received"></div>
<span>Not received in period</span>
</div>
<div class="legend-item clickable" data-status="no-match">
<div class="dot no-match"></div>
<span>No match in OSCAR/Surface</span>
<i class="fas fa-info-circle info-icon"></i>
</div>
</div>

<!-- Monthly legend items -->
<div id="monthly-legend" style="display: none;">
<div class="legend-item clickable" data-status="more-than-100">
<div class="dot more-than-100"></div>
<span>More than declared in OSCAR/Surface</span>
</div>
<div class="legend-item clickable" data-status="normal">
<div class="dot normal"></div>
<span>No issue (≥ 80%)</span>
</div>
<div class="legend-item clickable" data-status="issues-low">
<div class="dot issues-low"></div>
<span>Availability issue (> 30%)</span>
</div>
<div class="legend-item clickable" data-status="issues-high">
<div class="dot issues-high"></div>
<span>Availability issue (< 30%)</span>
</div>
<div class="legend-item clickable" data-status="not-received">
<div class="dot not-received"></div>
<span>Not received in period</span>
</div>
<div class="legend-item clickable" data-status="no-match">
<div class="dot no-match"></div>
<span>No match in OSCAR/Surface</span>
<i class="fas fa-info-circle info-icon"></i>
</div>
</div>

<!-- Original legend items (hidden by default) -->
<div id="original-legend">
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

// Initialize legend and controls
document.addEventListener('DOMContentLoaded', function() {
    const title = document.getElementById('observationTitle');
    const baselineControl = document.querySelector('.control.baseline-control');
    
    // Function to update legend and controls based on period type
    function updateLegendAndControls(periodType) {
        // Hide all legend sections first
        document.getElementById('sixhour-legend').style.display = 'none';
        document.getElementById('daily-legend').style.display = 'none';
        document.getElementById('monthly-legend').style.display = 'none';
        document.getElementById('original-legend').style.display = 'none';
        
        switch(periodType) {
            case 'six-hour':
                title.textContent = 'Received soundings';
                document.getElementById('sixhour-legend').style.display = 'block';
                baselineControl.classList.add('hidden');
                break;
                
            case 'daily':
                title.textContent = 'Received soundings';
                document.getElementById('daily-legend').style.display = 'block';
                baselineControl.classList.remove('hidden');
                break;
                
            case 'monthly':
                title.textContent = 'Received complete soundings';
                document.getElementById('monthly-legend').style.display = 'block';
                baselineControl.classList.remove('hidden');
                break;
                
            case 'alert':
                title.textContent = 'Observation and model differences';
                showAlertLegend();
                baselineControl.classList.remove('hidden');
                break;
                
            default:
                title.textContent = 'Received Observations';
                document.getElementById('original-legend').style.display = 'block';
                baselineControl.classList.remove('hidden');
                break;
        }
    }
    
    // Initial setup based on default selection
    updateLegendAndControls(document.getElementById('periodType').value);
    
    // Listen for period type changes
    document.getElementById('periodType').addEventListener('change', function() {
        updateLegendAndControls(this.value);
    });
    
    function showAlertLegend() {
        // Hide all other legends
        document.getElementById('sixhour-legend').style.display = 'none';
        document.getElementById('daily-legend').style.display = 'none';
        document.getElementById('monthly-legend').style.display = 'none';
        document.getElementById('original-legend').style.display = 'none';
        
        // Create and show alert-specific legend
        const legendContainer = document.querySelector('.panel.legend');
        
        // Check if alert legend already exists
        if (!document.getElementById('alert-legend')) {
            const alertLegend = document.createElement('div');
            alertLegend.id = 'alert-legend';
            
            alertLegend.innerHTML = `
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
                    <span>x ≤ 0.5</span>
                </div>
                <div class="legend-item clickable" data-status="less-than-5-values">
                    <div class="dot less-than-5-values"></div>
                    <span>Less than 5 values</span>
                </div>
            `;
            
            legendContainer.appendChild(alertLegend);
        } else {
            document.getElementById('alert-legend').style.display = 'block';
        }
    }
    
    // Debug mode for development
    window.debugMode = true;
    console.log('Legend controls initialized');
});
</script>

<!-- Load only the necessary scripts -->
<script src="<?= asset('js/nwp_land_surface.js') ?>"></script>

<script>
// Initialize map as soon as the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Ensure the map is initialized
    if (typeof window.map === 'undefined' || window.map === null) {
        console.log('Initializing map from index.php');
        // Initialize the map
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
        
        console.log('Map initialized successfully from index.php');
    }
    
    // Force load station data after a short delay
    setTimeout(() => {
        if (typeof window.loadStationData === 'function') {
            window.loadStationData();
        } else {
            console.error('loadStationData function not found');
        }
    }, 1000);
});
</script>

<?php include '../../../includes/footer.php'; ?>