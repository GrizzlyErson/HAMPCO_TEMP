<?php
$productResult = $db->fetch_all_product();

if ($productResult && $productResult->num_rows > 0) {
    while ($row = $productResult->fetch_assoc()) {
        $image_src = 'https://via.placeholder.com/64?text=No+Image';
        if (!empty($row['prod_image'])) {
            $image_src = '../upload/' . htmlspecialchars($row['prod_image']);
        }
        ?>
        <tr class="border-b border-gray-200 hover:bg-gray-50">
            <td class="py-3 px-6 text-left">
                <img src="<?php echo $image_src; ?>"
                     alt="Product Image"
                     class="w-16 h-16 object-cover rounded"
                     onerror="this.src='https://via.placeholder.com/64?text=No+Image'">
            </td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_id']); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_name']); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_stocks']); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_price']); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['category_name']); ?></td>
            <td class="py-3 px-6 text-left">
                <p title="<?php echo htmlspecialchars($row['prod_description']); ?>">
                    <?php echo htmlspecialchars($row['prod_description']); ?>
                </p>
            </td>
            <td class="py-3 px-6">
                <div class="relative inline-block">
                    <button type="button"
                            class="actionToggle bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded text-xs flex items-center shadow">
                        <span class="material-icons text-sm mr-1">more_vert</span>
                        Actions
                    </button>
                    <div class="actionMenu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden z-10">
                        <button class="updateRmBtn w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center text-gray-800 text-sm border-b"
                            data-id="<?php echo htmlspecialchars($row['prod_id']); ?>"
                            data-name="<?php echo htmlspecialchars($row['prod_name']); ?>"
                            data-description="<?php echo htmlspecialchars($row['prod_description']); ?>"
                            data-price="<?php echo htmlspecialchars($row['prod_price']); ?>"
                            data-category-id="<?php echo htmlspecialchars($row['prod_category_id']); ?>">
                            <span class="material-icons text-sm mr-2">edit</span>
                            Update
                        </button>

                        <button class="stockInRmBtn w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center text-gray-800 text-sm border-b"
                            data-id="<?php echo htmlspecialchars($row['prod_id']); ?>"
                            data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
                            <span class="material-icons text-sm mr-2">arrow_upward</span>
                            Stock In
                        </button>

                        <button class="manageMaterialsBtn w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center text-gray-800 text-sm border-b"
                            data-id="<?php echo htmlspecialchars($row['prod_id']); ?>"
                            data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
                            <span class="material-icons text-sm mr-2">inventory_2</span>
                            Materials
                        </button>

                        <button class="deleteRmBtn w-full text-left px-4 py-2 hover:bg-red-50 flex items-center text-red-600 text-sm"
                            data-prod_id="<?php echo htmlspecialchars($row['prod_id']); ?>"
                            data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
                            <span class="material-icons text-sm mr-2">delete</span>
                            Remove
                        </button>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
        <td colspan="8" class="py-3 px-6 text-center text-gray-500">
            No products found.
        </td>
    </tr>
    <?php
}

