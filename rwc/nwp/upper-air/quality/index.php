<?php
require_once '../../config/config.php';
//$pageTitle = "Availability of surface land observations (GBON)";
include '../../includes/header.php';
include '../../includes/navigation.php';
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
</style>
</head>


<div class="header">
<h1 class="title">
<i class="fas fa-satellite-dish"></i>
Availability of surface land observations (Global NWP)
</h1>

<div class="controls">
<div class="control">
<label class="label"><i class="fas fa-clock"></i> Period Type</label>
<select class="select" id="periodType">
<option value="six-hour">Six_hour</option>
<option value="daily">Daily</option>
<option value="monthly">Monthly</option>
</select>
</div>

<div class="control">
<label class="label"><i class="fas fa-monitoring"></i> Monitoring Category </label>
<select class="select" id="variabletype">
<option value="aviability">Aviablity</option>
<option value="quality">Quality</option>
</select>
</div>

<div class="control">
<label class="label"><i class="fas fa-thermometer-half"></i> Variable</label>
<select class="select" id="variableType">
<!-- <option value="pressure">Surface Pressure</option> -->
<option value="temperature">Temperature</option>
<option value="zonal_wind">Zonal Wind</option>
<option value="meridional_wind">Meridional Wind</option>
<option value="humidity">Relative Humidity</option>
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
<div>Loading NWP stations...</div>
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
<span id="observationTitle">Observation and modell differences</span>
<span id="observationTitle">Absolute values (hPa)</span>
</div>
<div class="legend-item clickable" data-status="complete">
<div class="dot complete"></div>
<span> > 10 </span>
</div>
<div class="legend-item clickable" data-status="normal">
<div class="dot normal"></div>
<span> 5 < x ≤ 10 </span>
</div>
<div class="legend-item clickabel" data-status="issues-low">
<div class="dot issues-low"></div>
<span> 1 < x ≤ 5 </span>
</div>
<div class="legend-item clickable" data-status="issues-high">
<div class="dot issues-high"></div>
<span> 0.5 < x ≤ 1 </span>
</div>
<div class="legend-item clickable" data-status="not-received">
<div class="dot not-received"></div>
<span> < 0.5 </span>
</div>
<!-- <div class="legend-item clickable" data-status="schedule-issues">
<div class="dot schedule-issues"></div>
<span>OSCAR schedule issues</span>
</div>
<div class="legend-item clickable" data-status="corresponding">
<div class="dot corresponding"></div>
<span>No match in OSCAR/Surface</span>
</div>
<div class="legend-item" id="lessThanTenDays" style="opacity: 0.5; cursor: default; display: none;">
<div class="dot" style="background-color: #ccc;"></div>
<span>Less than 10 days</span>
</div> -->
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
// Add this before including land_surface.js
document.getElementById('periodType').addEventListener('change', function() {
    const title = document.getElementById('observationTitle');
    const lessThanTenDays = document.getElementById('lessThanTenDays');
    
    if (this.value === 'monthly') {
        title.textContent = 'Monthly Received Observations';
        lessThanTenDays.style.display = 'flex';
    } else {
        title.textContent = 'Received Observations';
        lessThanTenDays.style.display = 'none';
    }
});
</script>
<script src="<?= asset('js/config.js') ?>"></script>
<script src="<?= asset('js/territories_center.js') ?>"></script>
<script src="<?= asset('js/territories.js') ?>"></script>
<script src="<?= asset('js/map.js') ?>"></script>
<script src="<?= asset('js/utility.js') ?>"></script>
<script src="<?= asset('js/land_surface.js') ?>"></script>


<?php include '../../includes/footer.php'; ?> 