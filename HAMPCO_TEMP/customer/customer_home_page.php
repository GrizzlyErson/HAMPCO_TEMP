<?php
include "component/header.php";
?>
<div class="container mx-auto px-4 py-6">
    <!-- Hamburger Menu Button -->
    <button 
        id="hamburger-btn" 
        class="lg:hidden p-2 bg-gray-800 text-white rounded hover:bg-gray-700 focus:outline-none focus:ring"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="flex">
        <!-- Sidebar Filters -->
        <aside 
            id="sidebar" 
            class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg transform -translate-x-full lg:relative lg:translate-x-0 lg:w-1/4 lg:h-auto p-4 transition-transform duration-300 ease-in-out z-50"
        >
            <button 
                id="close-sidebar" 
                class="lg:hidden p-2 text-gray-700 hover:text-gray-900 focus:outline-none"
            >
                ✕
            </button>
            <h2 class="font-semibold mb-4">Categories</h2>
            <ul id="category-list">
                <li>
                    <a href="#" class="block py-2 px-2 text-gray-700 hover:bg-gray-200 hover:text-gray-900 transition-colors duration-300 category-filter active" data-category-id="all">
                        All Categories
                    </a>
                </li>
                <?php 
                // Fetch categories
                $categories = $db->fetch_all_categories();
                if ($categories):
                    foreach ($categories as $category):
                        echo ' 
                            <li>
                                <a href="#" class="block py-2 px-2 text-gray-700 hover:bg-gray-200 hover:text-gray-900 transition-colors duration-300 category-filter" data-category-id="'.$category['category_id'].'">
                                '.ucfirst($category['category_name']).' 
                                </a>
                            </li>';
                    endforeach;
                endif;
                ?>
            </ul>

            <h2 class="font-semibold mt-6 mb-4">Price Range</h2>
            <div class="space-y-2">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="price" class="mr-2 price-filter" data-price-range="all" checked>
                    All Prices
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="price" class="mr-2 price-filter" data-price-range="0-1000">
                    PHP 0 - PHP 1000
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="price" class="mr-2 price-filter" data-price-range="1000-2000">
                    PHP 1000 - PHP 2000
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="price" class="mr-2 price-filter" data-price-range="2000-3000">
                    PHP 2000 - PHP 3000
                </label>
                 <label class="flex items-center cursor-pointer">
                    <input type="radio" name="price" class="mr-2 price-filter" data-price-range="3000-4000">
                    PHP 3000 - PHP 4000
                </label>

                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="price" class="mr-2 price-filter" data-price-range="4000-5000">
                    PHP 4000 - PHP 5000
                </label>
            </div>
        </aside>

        <!-- Product Grid -->
        <main class="w-full lg:w-3/4 p-4">
            <!-- Image Carousel -->
            <div class="mb-8">
                <div class="relative w-full bg-gray-900 rounded-lg overflow-hidden">
                    <!-- Carousel Container -->
                    <div class="carousel-wrapper relative w-full h-64 md:h-80 lg:h-96 overflow-hidden">
                        <!-- Carousel Images -->
                        <div class="carousel-slides flex transition-transform duration-500 ease-in-out" id="carouselSlides">
                            <img src="../img/banner.jpg" alt="Banner 1" class="carousel-slide w-full h-full object-cover flex-shrink-0">
                            <img src="../img/logo.png" alt="Banner 2" class="carousel-slide w-full h-full object-cover flex-shrink-0">
                            <img src="../img/575098477_2712130095784912_2588969272524062982_n.jpg" alt="Banner 3" class="carousel-slide w-full h-full object-cover flex-shrink-0">
                        </div>

                        <!-- Previous Button -->
                        <button id="prevBtn" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-200 text-gray-800 p-3 rounded-full shadow-lg transition-all duration-200 z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Next Button -->
                        <button id="nextBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-200 text-gray-800 p-3 rounded-full shadow-lg transition-all duration-200 z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Carousel Indicators -->
                        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 z-10">
                            <button class="carousel-indicator active w-2 h-2 rounded-full bg-white transition-all duration-300" data-index="0"></button>
                            <button class="carousel-indicator w-2 h-2 rounded-full bg-white transition-all duration-300" data-index="1"></button>
                            <button class="carousel-indicator w-2 h-2 rounded-full bg-white transition-all duration-300" data-index="2"></button>
                        </div>
                    </div>
                </div>
            </div>

            <?php include "backend/end-points/list_product.php"; ?>
        </main>
    </div>
</div>

<script>
    // Carousel functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    const totalSlides = slides.length;
    const carouselSlides = document.getElementById('carouselSlides');
    const indicators = document.querySelectorAll('.carousel-indicator');

    function showSlide(n) {
        currentSlide = (n + totalSlides) % totalSlides;
        carouselSlides.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        // Update indicators
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentSlide);
            indicator.classList.toggle('w-4', index === currentSlide);
            indicator.classList.toggle('w-2', index !== currentSlide);
        });
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    // Event listeners for buttons
    document.getElementById('nextBtn').addEventListener('click', nextSlide);
    document.getElementById('prevBtn').addEventListener('click', prevSlide);

    // Event listeners for indicators
    indicators.forEach((indicator) => {
        indicator.addEventListener('click', (e) => {
            const index = parseInt(e.target.getAttribute('data-index'));
            showSlide(index);
        });
    });

    // Auto-play carousel every 5 seconds
    setInterval(nextSlide, 5000);

    // JavaScript to toggle sidebar visibility
    document.getElementById('hamburger-btn').addEventListener('click', function () {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
    });

    document.getElementById('close-sidebar').addEventListener('click', function () {
        document.getElementById('sidebar').classList.add('-translate-x-full');
    });

    // Close sidebar when clicking outside (optional)
    document.addEventListener('click', function (e) {
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.getElementById('hamburger-btn');
        if (!sidebar.contains(e.target) && !hamburger.contains(e.target) && window.innerWidth < 1024) {
            sidebar.classList.add('-translate-x-full');
        }
    });
</script>


<?php include "component/footer.php"; ?>
