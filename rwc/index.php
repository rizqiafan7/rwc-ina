<?php
// index.php
require_once 'config/config.php';
include 'includes/header.php';
include 'includes/navigation.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8 lg:py-12 max-w-7xl">
   <!-- Welcome Section -->
<section class="text-center mb-12 lg:mb-16">
    <h2 class="text-xl sm:text-2xl lg:text-3xl text-gray-800 mb-6 lg:mb-8 font-normal animate-fade-in-up">
        Welcome to the RWC Indonesian Operational System Monitoring Dashboard
    </h2>
    <div class="max-w-4xl mx-auto space-y-4 text-gray-600 leading-relaxed">
        <p class="text-sm sm:text-base animate-fade-in-up" style="animation-delay: 0.2s;">
            The RWC Indonesian Dashboard is a comprehensive platform developed to monitor the performance of all meteorological and climatological observation components in the Asia Pacific region, including WMO Regional V and surrounding areas.
        </p>
        <p class="text-sm sm:text-base animate-fade-in-up" style="animation-delay: 0.6s;">
            For more information regarding this regional monitoring system, <a href="about.php" class="text-bmkg-blue hover:underline transition-colors duration-300">click here</a>.
        </p>
        <p class="text-sm sm:text-base animate-fade-in-up" style="animation-delay: 0.8s;">
            Currently, main modules are available for WIGOS monitoring, covering various aspects of regional meteorological and climatological observations:
        </p>
    </div>
</section>

    <!-- Basic Observing Network (APBON) -->
    <section class="mb-12 lg:mb-16 animate-fade-in-up" style="animation-delay: 0.8s;">
        <div class="bg-gradient-to-r from-gray-600 to-gray-700 text-white px-6 py-4 rounded-t-lg">
            <h3 class="text-base lg:text-lg font-medium">Basic Observing Network </h3>
        </div>
        <div class="bg-white p-6 lg:p-8 rounded-b-lg shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 max-w-4xl mx-auto">
                <!-- Surface Land Observations -->
                <a href="gbon/land_surface/index.php" class="monitoring-item group transform transition-all duration-300 hover:-translate-y-2">
                    <div class="w-44 lg:w-52 mx-auto h-32 lg:h-36 relative rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                        <img src="assets/images/surface_gbon.png" 
                            alt="Surface Land Observations" 
                            class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    <div class="text-center mt-4">
                        <h4 class="text-sm lg:text-base font-bold text-gray-800 mb-1">Surface land observations</h4>
                        <p class="text-xs lg:text-sm text-gray-500">Station Compliance</p>
                    </div>
                </a>
                
                <!-- Upper-air Land Observations -->
                <a href="gbon/land_upper-air/index.php" class="monitoring-item group transform transition-all duration-300 hover:-translate-y-2">
                    <div class="w-44 lg:w-52 mx-auto h-32 lg:h-36 relative rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                        <img src="assets/images/upper-air_gbon.png" 
                            alt="Upper-air Land Observations" 
                            class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    <div class="text-center mt-4">
                        <h4 class="text-sm lg:text-base font-bold text-gray-800 mb-1">Upper-air land observations</h4>
                        <p class="text-xs lg:text-sm text-gray-500">Station Compliance</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- NWP and Climate Monitoring Sections -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-12 lg:gap-8 mb-12 lg:mb-16">
        <!-- Near-real-time NWP monitoring -->
        <section class="animate-fade-in-up mb-8 xl:mb-0" style="animation-delay: 1s;">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 text-white px-6 py-4 rounded-t-lg">
                <h3 class="text-sm lg:text-base font-medium leading-tight">Near-real-time NWP monitoring of Asia Pacific Observing System Networks</h3>
            </div>
            <div class="bg-white p-6 lg:p-8 rounded-b-lg shadow-lg h-full">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2 gap-6">
                    <!-- NWP Surface -->
                    <a href="nwp/land_surface/availability/index.php" class="monitoring-item group transform transition-all duration-300 hover:-translate-y-2">
                        <div class="w-full max-w-40 mx-auto h-28 lg:h-32 relative rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                            <img src="assets/images/nwp_surface.png" 
                                alt="NWP Surface Land Observations" 
                                class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="text-center mt-3">
                            <h4 class="text-xs lg:text-sm font-bold text-gray-800">Surface land observations</h4>
                        </div>
                    </a>
                    
                    <!-- NWP Upper -->
                    <a href="nwp/land_upper-air/availability/index.php" class="monitoring-item group transform transition-all duration-300 hover:-translate-y-2">
                        <div class="w-full max-w-40 mx-auto h-28 lg:h-32 relative rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                            <img src="assets/images/upper-air_nwp.png" 
                                alt="NWP Upper-air Land Observations" 
                                class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="text-center mt-3">
                            <h4 class="text-xs lg:text-sm font-bold text-gray-800">Upper-air land observations</h4>
                        </div>
                    </a>
                    
                    <!-- NWP Marine -->
                    <div class="monitoring-item group cursor-pointer transform transition-all duration-300 hover:-translate-y-2 sm:col-span-2 xl:col-span-1 2xl:col-span-2" onclick="openMonitoring('nwp-marine')">
                        <div class="w-full max-w-40 mx-auto h-28 lg:h-32 relative rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                            <img src="assets/images/marine_nwp.png" 
                                alt="NWP Marine Surface Observations" 
                                class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="text-center mt-3">
                            <h4 class="text-xs lg:text-sm font-bold text-gray-800">Marine Surface observations</h4>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Climate Monitoring -->
        <section class="animate-fade-in-up" style="animation-delay: 1.2s;">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 text-white px-6 py-4 rounded-t-lg">
                <h3 class="text-sm lg:text-base font-medium leading-tight">Monitoring of Asia Pacific Climate Observing System Networks</h3>
            </div>
            <div class="bg-white p-6 lg:p-8 rounded-b-lg shadow-lg h-full">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Climate Surface -->
                    <div class="monitoring-item group cursor-pointer transform transition-all duration-300 hover:-translate-y-2" onclick="openMonitoring('climate-surface')">
                        <div class="w-full max-w-40 mx-auto h-28 lg:h-32 relative rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                            <img src="assets/images/surface_gsn.png" 
                                alt="Climate Surface Land Observations" 
                                class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="text-center mt-3">
                            <h4 class="text-xs lg:text-sm font-bold text-gray-800">Surface land observations</h4>
                        </div>
                    </div>
                    
                    <!-- Climate Upper -->
                    <div class="monitoring-item group cursor-pointer transform transition-all duration-300 hover:-translate-y-2" onclick="openMonitoring('climate-upper')">
                        <div class="w-full max-w-40 mx-auto h-28 lg:h-32 relative rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                            <img src="assets/images/upper_guan.png" 
                                alt="Climate Upper-air Land Observations" 
                                class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="text-center mt-3">
                            <h4 class="text-xs lg:text-sm font-bold text-gray-800">Upper-air land observations</h4>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>