// assets/js/script.js

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('hidden');
        });
    }

    // Navigation link handling
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(l => {
                l.classList.remove('active', 'text-bmkg-blue', 'border-bmkg-blue');
                l.classList.add('text-gray-600', 'border-transparent');
            });
            
            // Add active class to clicked nav link
            this.classList.remove('text-gray-600', 'border-transparent');
            this.classList.add('active', 'text-bmkg-blue', 'border-bmkg-blue');
            
            // Close mobile menu on link click
            if (window.innerWidth < 1024) {
                navMenu.classList.add('hidden');
            }
        });
    });

    // Add floating animation to monitoring items on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.monitoring-item').forEach(item => {
        observer.observe(item);
    });

    // Handle window resize for mobile menu
    window.addEventListener('resize', function() {
        if (navMenu) {
            if (window.innerWidth >= 1024) {
                navMenu.classList.remove('hidden');
            } else {
                navMenu.classList.add('hidden');
            }
        }
    });

    // Add staggered animation delays to monitoring items
    document.querySelectorAll('.monitoring-item').forEach((item, index) => {
        item.style.animationDelay = `${0.1 + (index * 0.1)}s`;
    });
});

// Monitoring map click handlers
function openMonitoring(type) {
    // Add click animation
    if (event && event.currentTarget) {
        event.currentTarget.style.transform = 'scale(0.95)';
        setTimeout(() => {
            event.currentTarget.style.transform = '';
        }, 150);
    }
    
    // Simulate opening monitoring page
    const typeNames = {
        'surface': 'Surface land observations',
        'upper': 'Upper-air land observations',
        'nwp-surface': 'NWP Surface land observations',
        'nwp-upper': 'NWP Upper-air land observations',
        'nwp-marine': 'NWP Marine Surface observations',
        'climate-surface': 'Climate Surface land observations',
        'climate-upper': 'Climate Upper-air land observations'
    };
    
    alert(`Membuka dashboard monitoring ${typeNames[type] || type} Asia Pasifik...`);
    
    // In a real application, you would navigate to the specific monitoring page
    // window.location.href = `pages/monitoring.php?type=${type}`;
}

// Smooth page load animation
window.addEventListener('load', function() {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.5s ease-in-out';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});