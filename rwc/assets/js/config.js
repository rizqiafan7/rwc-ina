// Global variables
let map, stationMarkers = [], currentStations = [];
let isLoading = false;
let legendsVisible = true;

// Configuration
const API_ENDPOINT = window.location.origin + '/api/'; // WMO OSCAR API proxy endpoint
const DEFAULT_REGION = 'ALL_COMBINED'; // Default to All Stations (Region V + USA)
const REFRESH_INTERVAL = 300000; // 5 minutes (300,000 ms) 