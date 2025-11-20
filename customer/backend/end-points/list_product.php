<h1 class="text-2xl font-semibold mb-6">All Products</h1>

<!-- Search Input -->
<input type="text" id="search" class="w-full p-2 mb-4 border rounded" placeholder="Search Products...">

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
        <div class="bg-white p-4 rounded shadow-lg transition-transform transform hover:scale-105 hover:shadow-2xl product-card" data-category-id="<?=$product['prod_category_id']?>" data-price="<?=$product['prod_price']?>">
            <a href="view_product?product_id=<?=$product['prod_id']?>">

                <!-- Product Image -->
                <img src="../upload/<?=$product['prod_image']?>" alt="Product Image" class="w-full rounded mb-4 transition-transform hover:scale-105">

                <!-- Product Name -->
                <h2 class="font-semibold text-lg transition-colors hover:text-blue-500 product-name"><?=$product['prod_name']?></h2>

                <!-- Product Description -->
                <p class="text-gray-600 transition-colors hover:text-gray-800"><?= substr($product['prod_description'], 0, 20) . (strlen($product['prod_description']) > 20 ? '...' : '') ?></p>

              
                <p class="text-lg font-bold text-red-600">PHP <?=number_format($product['prod_price'], 2);?></p>
                
            </a>
        </div>

    <?php
        endforeach;
    else:
    ?>
        <p class="text-gray-600 col-span-full text-center">No products found.</p>
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