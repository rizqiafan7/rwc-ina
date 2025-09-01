<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?= asset('images/favicon.ico') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/monitoring.css') ?>">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bmkg-blue': '#4a90e2',
                        'bmkg-dark': '#2C3E4F',
                        'bmkg-red': '#e74c3c',
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans">
    <!-- Header -->
    <header class="bg-bmkg-dark border-b-2 border-gray-300">
        <div class="container mx-auto px-4 py-3">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                <!-- Left Section -->
                <div class="flex items-center gap-4 text-center lg:text-left">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full overflow-hidden flex items-center justify-center">
                    <img src="<?= asset('images/logo.png') ?>" alt="Logo" class="w-full h-full object-cover" />
                </div>
                    <h1 class="text-white text-sm sm:text-base lg:text-lg xl:text-xl font-bold leading-tight">
                        <?php echo SITE_TITLE; ?>
                    </h1>
                </div>
                
                <!-- Right Section -->
                <div class="flex items-center">
                    <a href="tel:<?php echo SUPPORT_PHONE; ?>" class="contact-button bg-white text-black px-3 py-2 lg:px-4 lg:py-2 rounded text-xs lg:text-sm font-bold hover:bg-gray-100 cursor-pointer">
                        <i class="fas fa-phone mr-2"></i>
                        HELP CENTER 24/7
                    </a>
                </div>
            </div>
        </div>
    </header>