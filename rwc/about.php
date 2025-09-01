<?php
// pages/about.php
require_once 'config/config.php';
include 'includes/header.php';
include 'includes/navigation.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8 lg:py-12 max-w-7xl">
    <!-- Header Section -->
    <div class="text-center mb-12">
        <h1 class="text-3xl lg:text-4xl text-bmkg-blue font-bold mb-4">
            About RWC Asia Pacific Dashboard
        </h1>
        <div class="w-24 h-1 bg-bmkg-blue mx-auto mb-6"></div>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Leading monitoring platform for meteorological and climatological operational systems in the Asia Pacific region
        </p>
    </div>

    <!-- Vision & Mission Section -->
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <div class="bg-white rounded-lg shadow-lg p-8 border-t-4 border-bmkg-blue">
            <div class="flex items-center mb-4">
                <div class="bg-bmkg-blue text-white p-3 rounded-full mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800">Vision</h3>
            </div>
            <p class="text-gray-600 leading-relaxed">
                To become a trusted and integrated monitoring platform to support sustainable and innovative meteorological and climatological operations in the Asia Pacific region.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8 border-t-4 border-green-500">
            <div class="flex items-center mb-4">
                <div class="bg-green-500 text-white p-3 rounded-full mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800">Mission</h3>
            </div>
            <p class="text-gray-600 leading-relaxed">
                To provide comprehensive, accurate, and easily accessible real-time monitoring systems to support operational decision-making in meteorology and climatology.
            </p>
        </div>
    </div>

    <!-- About Description -->
    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-8 mb-12">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl font-semibold text-bmkg-blue mb-6 text-center">
                About Operational System Monitoring Dashboard
            </h2>
            <div class="prose prose-lg max-w-none text-gray-700">
                <p class="mb-4">
                    The RWC (Regional Weather Centre) Asia Pacific Dashboard is a comprehensive platform specifically developed to monitor the performance of all meteorological and climatological observation components in the Asia Pacific region, covering Regional WMO V and surrounding areas.
                </p>
                <p class="mb-4">
                    This system is designed to provide accurate and easy-to-understand real-time data visualization, enabling operators and decision-makers to monitor the operational status of various meteorological instruments, observation stations, and data communication systems centrally.
                </p>
                <p>
                    With an intuitive interface and advanced features, this dashboard becomes an essential tool for maintaining continuity and quality of meteorological services in the Asia Pacific region.
                </p>
            </div>
        </div>
    </div>

    <!-- Key Features -->
    <div class="mb-12">
        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-8">Key Features</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-300">
                <div class="bg-blue-100 text-bmkg-blue p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Real-time Monitoring</h3>
                <p class="text-gray-600 text-sm">Real-time operational status monitoring for all system components</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-300">
                <div class="bg-green-100 text-green-600 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Regional Coverage</h3>
                <p class="text-gray-600 text-sm">Covers the entire Asia Pacific region including Regional WMO V</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-300">
                <div class="bg-purple-100 text-purple-600 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Alert System</h3>
                <p class="text-gray-600 text-sm">Automatic warning system for operational disruptions and anomalies</p>
            </div>
        </div>
    </div>

    <!-- Coverage Area -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Coverage Area</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-bmkg-blue mb-4">Regional WMO V</h3>
                <div class="space-y-2 text-gray-600">
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-bmkg-blue rounded-full mr-3 mt-2"></span>
                        <span>Indonesia (IDN)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-bmkg-blue rounded-full mr-3 mt-2"></span>
                        <span>Malaysia (MYS)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-bmkg-blue rounded-full mr-3 mt-2"></span>
                        <span>Singapore (SGP)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-bmkg-blue rounded-full mr-3 mt-2"></span>
                        <span>Philippines (PHL)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-bmkg-blue rounded-full mr-3 mt-2"></span>
                        <span>Brunei (BRN)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-bmkg-blue rounded-full mr-3 mt-2"></span>
                        <span>Timor Leste (TLS)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-bmkg-blue rounded-full mr-3 mt-2"></span>
                        <span>Papua New Guinea (PGN)</span>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-bmkg-blue mb-4">Regional USA States</h3>
                <div class="space-y-2 text-gray-600">
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3 mt-2"></span>
                        <span>LIHUE, KAUAI, HAWAII</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3 mt-2"></span>
                        <span>HONOLULU, OAHU, HAWAII</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3 mt-2"></span>
                        <span>KAHULUI AIRPORT, MAUI, HAWAII</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3 mt-2"></span>
                        <span>HILO HI, HAWAII</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3 mt-2"></span>
                        <span>WEATHER FORECAST OFFICE, GUAM, MARIANA IS.</span>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3 mt-2"></span>
                        <span>PAGO PAGO/INT.AIRP. AMERICAN SAMOA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>