<h1 class="text-2xl font-semibold mb-6">All Products</h1>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="product-grid">
    <?php 
    $fetch_all_product = $db->fetch_all_product();  // Fetch all products

    // Check if the result has rows
    if ($fetch_all_product->num_rows > 0):
        // Fetch all rows as an associative array
        $products = $fetch_all_product->fetch_all(MYSQLI_ASSOC);

        foreach ($products as $product):
            $prod_price = $product['prod_price'];
    ?>
        <!-- Product Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group product-card" data-category-id="<?=$product['prod_category_id']?>" data-price="<?=$product['prod_price']?>">
            <a href="view_product?product_id=<?=$product['prod_id']?>" class="block h-full">
                <!-- Image Container with Badge -->
                <div class="relative w-full h-48 bg-gray-200 overflow-hidden flex items-center justify-center group-hover:shadow-lg">
                    <img src="../upload/<?=$product['prod_image']?>" alt="<?=$product['prod_name']?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                    
                    <!-- Badge -->
                    <div class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                        New
                    </div>
                </div>

                <!-- Card Content -->
                <div class="p-4 flex flex-col h-32">
                    <!-- Product Name -->
                    <h2 class="font-bold text-sm md:text-base text-gray-800 mb-2 line-clamp-2 product-name group-hover:text-blue-600 transition-colors">
                        <?=$product['prod_name']?>
                    </h2>

                    <!-- Product Description -->
                    <p class="text-xs md:text-sm text-gray-600 mb-3 line-clamp-2 flex-grow">
                        <?= substr($product['prod_description'], 0, 50) . (strlen($product['prod_description']) > 50 ? '...' : '') ?>
                    </p>

                    <!-- Price and Rating Row -->
                    <div class="flex items-center justify-between">
                        <!-- Price -->
                        <div>
                            <p class="text-lg md:text-xl font-bold text-red-600">
                                PHP <?=number_format($product['prod_price'], 2);?>
                            </p>
                        </div>
                        
                        <!-- Rating Stars -->
                        <div class="flex items-center">
                            <div class="flex text-yellow-400">
                                <span class="text-sm">★★★★★</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Action Button -->
                    <button class="mt-3 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg text-xs md:text-sm font-semibold transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293a1 1 0 00-.263 1.157l1.905 5.831A2 2 0 0010.25 23h3.5a2 2 0 001.946-1.39l1.838-5.119a1 1 0 00-.28-1.144L16 13M17 13v6m-3-6v6" />
                        </svg>
                        Add to Cart
                    </button>
                </div>
            </a>
        </div>

    <?php
        endforeach;
    else:
    ?>
        <p class="text-gray-600 col-span-full text-center py-8">No products found.</p>
    <?php endif; ?>
</div>



<script>
$(document).ready(function() {
    $('#search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('#product-grid .product-card').each(function() {
            var productName = $(this).find('.product-name').text().toLowerCase(); 
            if (productName.indexOf(searchTerm) !== -1) {
                $(this).show(); 
            } else {
                $(this).hide(); 
            }
        });
    });













// Function to filter products by category and price
function applyFilters() {
    const selectedCategory = document.querySelector('.category-filter.active')?.getAttribute('data-category-id') || 'all';
    const selectedPriceRange = document.querySelector('.price-filter:checked')?.getAttribute('data-price-range') || 'all';
    const productCards = document.querySelectorAll('.product-card');

    productCards.forEach(card => {
        const categoryId = card.getAttribute('data-category-id');
        const productPrice = parseFloat(card.getAttribute('data-price'));

        let categoryMatch = (selectedCategory === 'all' || categoryId === selectedCategory);
        let priceMatch = false;

        if (selectedPriceRange !== 'all') {
            const [minPrice, maxPrice] = selectedPriceRange.split('-').map(Number);
            priceMatch = (productPrice >= minPrice && productPrice <= maxPrice);
        } else {
            priceMatch = true;
        }

        if (categoryMatch && priceMatch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

const categoryLinks = document.querySelectorAll('.category-filter');
categoryLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        categoryLinks.forEach(link => link.classList.remove('active'));
        link.classList.add('active');
        applyFilters();
    });
});

const priceFilters = document.querySelectorAll('.price-filter');
priceFilters.forEach(radio => {
    radio.addEventListener('change', () => {
        applyFilters();
    });
});
applyFilters();    
});

</script>