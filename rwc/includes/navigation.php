<?php
// includes/navigation.php

// Navigation menu configuration
$navigation_menu = [
    ['title' => 'Home', 'url' => '/index.php'],
    [
        'title' => 'GBON', 
        'url' => '/gbon',
        'submenu' => [
            ['title' => 'Surface', 'url' => '/gbon/land_surface/index.php'],
            ['title' => 'Upper Air', 'url' => '/gbon/land_upper-air/index.php'],
        ]
    ],
    [
        'title' => 'NWP', 
        'url' => '/nwp',
        'submenu' => [
            [
                'title' => 'Surface', 
                'url' => '#',
                'submenu' => [
                    ['title' => 'Availability', 'url' => '/nwp/land_surface/availability/index.php'],
                    ['title' => 'Quality', 'url' => '/nwp/land_surface/quality/index.php'],
                ]
            ],
            [
                'title' => 'Upper Air', 
                'url' => '#',
                'submenu' => [
                    ['title' => 'Availability', 'url' => '/nwp/land_upper-air/availability/index.php'],
                    ['title' => 'Quality', 'url' => '/nwp/land_upper-air/quantity/index.php'],
                ]
            ],
        ]
    ],
    ['title' => 'About', 'url' => '/about.php'],
    ['title' => 'Support', 'url' => '/support.php'],
    ['title' => 'Tickets', 'url' => '/tickets.php'],
];
?>

<!-- Navigation -->
<nav class="bg-gray-50 border-b border-gray-200 sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <!-- Mobile Menu -->
        <div class="flex justify-between items-center lg:hidden py-3">
            <span class="text-gray-600 font-medium">RWC - Monitoring System</span>
            <button id="mobile-menu-toggle" class="text-gray-600 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <!-- Navigation Links -->
        <div id="nav-menu" class="hidden lg:flex lg:justify-between lg:items-center pb-4 lg:pb-0">
            <!-- Left Menu (Home, GBON, NWP) -->
            <ul class="flex flex-col lg:flex-row lg:space-x-8 space-y-2 lg:space-y-0">
                <?php 
                // Filter menu untuk sebelah kiri (Home, GBON, NWP)
                $left_menu = array_filter($navigation_menu, function($menu) {
                    return in_array(strtolower($menu['title']), ['home', 'gbon', 'nwp']);
                });
                ?>
                <?php foreach ($left_menu as $menu): ?>
                    <?php 
                    $is_active = function_exists('isActivePage') ? isActivePage($menu['url']) : false;
                    $active_class = $is_active 
                        ? 'active text-bmkg-blue border-bmkg-blue'
                        : 'text-gray-600 border-transparent';
                    ?>
                    <li class="<?= isset($menu['submenu']) ? 'relative dropdown' : '' ?>">
                        <?php if (isset($menu['submenu'])): ?>
                            <!-- Menu with Submenu -->
                            <button class="dropdown-toggle w-full text-left flex items-center justify-between py-3 lg:py-4 text-sm hover:text-bmkg-blue transition-colors duration-300 border-b-2 hover:border-bmkg-blue <?= $active_class ?> focus:outline-none">
                                <?= htmlspecialchars($menu['title']) ?>
                                <svg class="w-4 h-4 dropdown-arrow transition-transform duration-200 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <!-- Dropdown Submenu -->
                            <ul class="dropdown-menu hidden lg:absolute lg:top-full lg:left-0 lg:mt-1 lg:w-64 lg:bg-white lg:border lg:border-gray-200 lg:rounded-md lg:shadow-lg bg-gray-50 pl-4 lg:pl-0 z-50">
                                <?php foreach ($menu['submenu'] as $submenu): ?>
                                    <?php 
                                    $is_submenu_active = function_exists('isActivePage') ? isActivePage($submenu['url']) : false;
                                    $submenu_active_class = $is_submenu_active 
                                        ? 'bg-bmkg-blue text-white'
                                        : 'text-gray-700 hover:bg-gray-100';
                                    ?>
                                    <li class="<?= isset($submenu['submenu']) ? 'relative dropdown-nested' : '' ?>">
                                        <?php if (isset($submenu['submenu'])): ?>
                                            <!-- Nested Submenu -->
                                            <button class="dropdown-nested-toggle w-full text-left flex items-center justify-between px-4 py-2 text-sm transition-colors duration-200 <?= $submenu_active_class ?> focus:outline-none">
                                                <?= htmlspecialchars($submenu['title']) ?>
                                                <svg class="w-3 h-3 dropdown-nested-arrow transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                            <!-- Nested Dropdown -->
                                            <ul class="dropdown-nested-menu hidden lg:absolute lg:top-0 lg:left-full lg:ml-1 lg:w-48 lg:bg-white lg:border lg:border-gray-200 lg:rounded-md lg:shadow-lg bg-gray-100 pl-4 lg:pl-0 z-60">
                                                <?php foreach ($submenu['submenu'] as $nested_submenu): ?>
                                                    <?php 
                                                    $is_nested_active = function_exists('isActivePage') ? isActivePage($nested_submenu['url']) : false;
                                                    $nested_active_class = $is_nested_active 
                                                        ? 'bg-bmkg-blue text-white'
                                                        : 'text-gray-700 hover:bg-gray-100';
                                                    ?>
                                                    <li>
                                                        <a href="<?= url($nested_submenu['url']) ?>" 
                                                           class="block px-4 py-2 text-sm transition-colors duration-200 <?= $nested_active_class ?>">
                                                            <?= htmlspecialchars($nested_submenu['title']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <!-- Regular Submenu -->
                                            <a href="<?= url($submenu['url']) ?>" 
                                               class="block px-4 py-2 text-sm transition-colors duration-200 <?= $submenu_active_class ?>">
                                                <?= htmlspecialchars($submenu['title']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <!-- Regular Menu -->
                            <a href="<?= url($menu['url']) ?>" 
                               class="nav-link block py-3 lg:py-4 text-sm hover:text-bmkg-blue transition-colors duration-300 border-b-2 hover:border-bmkg-blue <?= $active_class ?>">
                                <?= htmlspecialchars($menu['title']) ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Right Menu (About, Support, Tickets) -->
            <ul class="flex flex-col lg:flex-row lg:space-x-8 space-y-2 lg:space-y-0 mt-4 lg:mt-0">
                <?php 
                // Filter menu untuk sebelah kanan (About, Support, Tickets)
                $right_menu = array_filter($navigation_menu, function($menu) {
                    return in_array(strtolower($menu['title']), ['about', 'support', 'tickets']);
                });
                ?>
                <?php foreach ($right_menu as $menu): ?>
                    <?php 
                    $is_active = function_exists('isActivePage') ? isActivePage($menu['url']) : false;
                    $active_class = $is_active 
                        ? 'active text-bmkg-blue border-bmkg-blue'
                        : 'text-gray-600 border-transparent';
                    ?>
                    <li class="<?= isset($menu['submenu']) ? 'relative dropdown' : '' ?>">
                        <?php if (isset($menu['submenu'])): ?>
                            <!-- Menu with Submenu -->
                            <button class="dropdown-toggle w-full text-left flex items-center justify-between py-3 lg:py-4 text-sm hover:text-bmkg-blue transition-colors duration-300 border-b-2 hover:border-bmkg-blue <?= $active_class ?> focus:outline-none">
                                <?= htmlspecialchars($menu['title']) ?>
                                <svg class="w-4 h-4 dropdown-arrow transition-transform duration-200 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <!-- Dropdown Submenu -->
                            <ul class="dropdown-menu hidden lg:absolute lg:top-full lg:right-0 lg:mt-1 lg:w-48 lg:bg-white lg:border lg:border-gray-200 lg:rounded-md lg:shadow-lg bg-gray-50 pl-4 lg:pl-0 z-50">
                                <?php foreach ($menu['submenu'] as $submenu): ?>
                                    <?php 
                                    $is_submenu_active = function_exists('isActivePage') ? isActivePage($submenu['url']) : false;
                                    $submenu_active_class = $is_submenu_active 
                                        ? 'bg-bmkg-blue text-white'
                                        : 'text-gray-700 hover:bg-gray-100';
                                    ?>
                                    <li>
                                        <a href="<?= url($submenu['url']) ?>" 
                                           class="block px-4 py-2 text-sm transition-colors duration-200 <?= $submenu_active_class ?>">
                                            <?= htmlspecialchars($submenu['title']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <!-- Regular Menu -->
                            <a href="<?= url($menu['url']) ?>" 
                               class="nav-link block py-3 lg:py-4 text-sm hover:text-bmkg-blue transition-colors duration-300 border-b-2 hover:border-bmkg-blue <?= $active_class ?>">
                                <?= htmlspecialchars($menu['title']) ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>



<!-- CSS -->
<style>
.dropdown-arrow.rotated {
    transform: rotate(180deg);
}

.dropdown-nested-arrow.rotated {
    transform: rotate(90deg);
}

@media (min-width: 1024px) {
    .dropdown:hover .dropdown-menu {
        display: block;
    }
    
    .dropdown-nested:hover .dropdown-nested-menu {
        display: block;
    }
}

/* Mobile menu styling improvements */
@media (max-width: 1023px) {
    #nav-menu {
        flex-direction: column;
        width: 100%;
    }
    
    #nav-menu ul {
        width: 100%;
    }
    
    .dropdown-nested-menu {
        position: static !important;
        margin-left: 0 !important;
        width: 100% !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
    }
}
</style>


<!-- external Script-->
<script src="<?= asset('js/script.js') ?>"></script>

<!-- Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    mobileToggle?.addEventListener('click', () => {
        navMenu.classList.toggle('hidden');
    });
    
    // Dropdown toggle
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const menu = this.nextElementSibling;
            const arrow = this.querySelector('.dropdown-arrow');
            
            // Toggle current dropdown
            menu.classList.toggle('hidden');
            arrow.classList.toggle('rotated');
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-toggle').forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherMenu = otherToggle.nextElementSibling;
                    const otherArrow = otherToggle.querySelector('.dropdown-arrow');
                    otherMenu.classList.add('hidden');
                    otherArrow.classList.remove('rotated');
                }
            });
        });
    });
    
    // Nested dropdown toggle
    document.querySelectorAll('.dropdown-nested-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const menu = this.nextElementSibling;
            const arrow = this.querySelector('.dropdown-nested-arrow');
            
            // Toggle current nested dropdown
            menu.classList.toggle('hidden');
            arrow.classList.toggle('rotated');
            
            // Close other nested dropdowns in the same parent
            const parentDropdown = this.closest('.dropdown-menu');
            parentDropdown.querySelectorAll('.dropdown-nested-toggle').forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherMenu = otherToggle.nextElementSibling;
                    const otherArrow = otherToggle.querySelector('.dropdown-nested-arrow');
                    otherMenu?.classList.add('hidden');
                    otherArrow?.classList.remove('rotated');
                }
            });
        });
    });
});
</script>