// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Check if Leaflet library is loaded
    if (typeof L === 'undefined') {
        console.error('Leaflet library is not loaded!');
        showAlert('error', 'Failed to load map library. Please refresh the page or check your internet connection.');
        
        // Add error message to the map container
        const mapContainer = document.querySelector('.map-container');
        if (mapContainer) {
            mapContainer.innerHTML += `
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                            background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.2); text-align: center; max-width: 80%;">
                    <h2 style="color: #ef4444; margin-bottom: 10px;">Map Library Error</h2>
                    <p>The Leaflet map library failed to load. Please check your internet connection and refresh the page.</p>
                    <button onclick="location.reload()" style="padding: 8px 16px; background: #1e3c72; color: white; 
                            border: none; border-radius: 5px; margin-top: 15px; cursor: pointer;">
                        Refresh Page
                    </button>
                </div>
            `;
        }
        return;
    }
    
    console.log('Leaflet version:', L.version);
    initializeMap();
    initializeControls();
    initializeDefaults();
    
    // Initialize from URL parameters if they exist
    initializeFromURL();
    
    // Check if API is accessible
    checkApiAccess().then(isAccessible => {
        if (isAccessible) {
            // Delay loading data slightly to ensure map is fully initialized
            setTimeout(() => {
                loadStationData();
                
                // Auto-refresh
                setInterval(loadStationData, REFRESH_INTERVAL);
            }, 500);
        } else {
            showAlert('error', 'Could not connect to the API. Using mock data instead.');
            // Try with mock data
            setTimeout(() => {
                const territory = document.getElementById('regionSelect').value;
                fetch(`${API_ENDPOINT}?territory=${territory}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.stations && data.stations.length > 0) {
                            currentStations = data.stations;
                            displayStations(currentStations);
                            updateStatistics(data.metadata);
                            showAlert('success', `Using mock data for ${territory}`);
                        }
                    })
                    .catch(error => {
                        console.error('Failed to load mock data:', error);
                        showAlert('error', 'Failed to load any data. Please check your connection.');
                    });
            }, 500);
        }
    });
});

// Initialize Leaflet map
function initializeMap() {
    try {
        console.log('Initializing map...');
        // Set default view to Indonesia with appropriate zoom level
        map = L.map('map', {
            center: [-2.5, 118],
            zoom: 5,
            minZoom: 3,
            maxZoom: 18,
            zoomControl: true,
            scrollWheelZoom: true,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: 'topleft',
                title: 'Toggle fullscreen',
                titleCancel: 'Exit fullscreen mode',
                forceSeparateButton: true
            },
            worldCopyJump: true, // Enable world copy jumping
            maxBounds: [[-90, -Infinity], [90, Infinity]] // Allow infinite horizontal scrolling
        });
        console.log('Map initialized successfully');
    } catch (error) {
        console.error('Error initializing map:', error);
        showAlert('error', 'Failed to initialize map: ' + error.message);
    }

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        noWrap: false // Allow the tiles to wrap around the world
    }).addTo(map);
    
    // Add scale control
    L.control.scale({
        metric: true,
        imperial: false,
        position: 'bottomleft'
    }).addTo(map);

    map.attributionControl.setPrefix('RWC System Indonesia Monitoring Status');
    
    map.options.preferCanvas = true;
} 