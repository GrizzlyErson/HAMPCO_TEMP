 <!-- Main Content goes here -->
 </main>
</div>


<!-- Chart.js -->
<script src="vendor/chart.js/Chart.min.js"></script>

<!-- Include SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Latest Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Latest Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<!-- Optional: Material Icons CDN for icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="assets/js/app.js"></script>>





<script>
   $("#toggleAssets").click(function(){
      $("#assetsDropdown").slideToggle(300);
    });
  
  const overlay = document.getElementById('overlay');


  menuButton.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
  });



  overlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
  });
</script>

<!-- Initialize overlay functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('overlay');
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    
    if (overlay && sidebar && toggleBtn) {
        // Toggle sidebar
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }
});
</script>

<script>
    const scrollTopBtn = document.getElementById('scrollTopBtn');

    window.onscroll = function() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            scrollTopBtn.classList.remove('hidden');
        } else {
            scrollTopBtn.classList.add('hidden');
        }
    };

    scrollTopBtn.addEventListener('click', function() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    });

    document.getElementById('buttonPosition').addEventListener('change', function() {
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        const selectedPosition = this.value;
        scrollTopBtn.classList.remove('bottom-5', 'right-5', 'left-5', 'top-5');
        selectedPosition.split(' ').forEach(cls => scrollTopBtn.classList.add(cls));
        updateSettingsTable();
    });

    document.getElementById('scrollButtonColor').addEventListener('change', function() {
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        const selectedColor = this.value;
        scrollTopBtn.classList.remove('bg-blue-500', 'bg-green-500', 'bg-red-500');
        scrollTopBtn.classList.add(selectedColor);
        updateSettingsTable();
    });

    document.getElementById('scrollButtonIcon').addEventListener('change', function() {
        const scrollTopBtnIcon = scrollTopBtn.querySelector('.material-icons');
        scrollTopBtnIcon.textContent = this.value;
        updateSettingsTable();
    });

    document.getElementById('pageColor').addEventListener('change', function() {
        const selectedColor = this.value;
        document.body.classList.remove('bg-white', 'bg-gray-200', 'bg-gray-800');
        document.body.classList.add(selectedColor);
        updateSettingsTable();
    });

    const customizationMenu = document.getElementById('customization-menu');
    const customizationMenuItems = document.getElementById('customization-menu-items');

    customizationMenu.addEventListener('click', function() {
        const isExpanded = customizationMenu.getAttribute('aria-expanded') === 'true';
        customizationMenu.setAttribute('aria-expanded', !isExpanded);
        customizationMenuItems.classList.toggle('hidden');
    });

    const tableCustomizationMenu = document.getElementById('table-customization-menu');
    const tableCustomizationMenuItems = document.getElementById('table-customization-menu-items');

    tableCustomizationMenu.addEventListener('click', function() {
        const isExpanded = tableCustomizationMenu.getAttribute('aria-expanded') === 'true';
        tableCustomizationMenu.setAttribute('aria-expanded', !isExpanded);
        tableCustomizationMenuItems.classList.toggle('hidden');
    });

    document.addEventListener('click', function(event) {
        if (!customizationMenu.contains(event.target) && !customizationMenuItems.contains(event.target)) {
            customizationMenu.setAttribute('aria-expanded', 'false');
            customizationMenuItems.classList.add('hidden');
        }
        if (!tableCustomizationMenu.contains(event.target) && !tableCustomizationMenuItems.contains(event.target)) {
            tableCustomizationMenu.setAttribute('aria-expanded', 'false');
            tableCustomizationMenuItems.classList.add('hidden');
        }
    });

    const productionTable = document.getElementById('productionTable');
    const finishedProductsTable = document.getElementById('finished-products-table');
    const settingsTableBody = document.querySelector('#settings-table tbody');

    function updateSettingsTable() {
        const settings = [
            { name: 'Scroll Speed', value: $('#scrollSpeed option:selected').text() },
            { name: 'Button Color', value: $('#buttonColor option:selected').text() },
            { name: 'Button Position', value: $('#buttonPosition option:selected').text() },
            { name: 'Scroll Button Color', value: $('#scrollButtonColor option:selected').text() },
            { name: 'Scroll Button Icon', value: $('#scrollButtonIcon option:selected').text() },
            { name: 'Page Color', value: $('#pageColor option:selected').text() },
            { name: 'Table Font Size', value: $('#tableFontSize option:selected').text() },
            { name: 'Table Font Color', value: $('#tableFontColor option:selected').text() },
            { name: 'Table Header Color', value: $('#tableHeaderColor option:selected').text() },
        ];

        settingsTableBody.innerHTML = '';
        settings.forEach(setting => {
            const row = `<tr>
                <td class="py-3 px-6 text-left">${setting.name}</td>
                <td class="py-3 px-6 text-left">${setting.value}</td>
            </tr>`;
            settingsTableBody.innerHTML += row;
        });
    }

    $('#scrollSpeed, #buttonColor, #buttonPosition, #scrollButtonColor, #scrollButtonIcon, #pageColor, #tableFontSize, #tableFontColor, #tableHeaderColor').on('change', function() {
        updateSettingsTable();
    });

    $('#tableFontSize').on('change', function() {
        const fontSize = $(this).val();
        productionTable.classList.remove('text-xs', 'text-sm', 'text-base');
        productionTable.classList.add(fontSize);
        finishedProductsTable.classList.remove('text-xs', 'text-sm', 'text-base');
        finishedProductsTable.classList.add(fontSize);
    });

    $('#tableFontColor').on('change', function() {
        const fontColor = $(this).val();
        productionTable.classList.remove('text-gray-600', 'text-blue-600', 'text-green-600');
        productionTable.classList.add(fontColor);
        finishedProductsTable.classList.remove('text-gray-600', 'text-blue-600', 'text-green-600');
        finishedProductsTable.classList.add(fontColor);
    });

    $('#tableHeaderColor').on('change', function() {
        const headerColor = $(this).val();
        $('#productionTable thead tr').removeClass('bg-gray-100', 'bg-blue-200', 'bg-green-200').addClass(headerColor);
        $('#finished-products-table thead tr').removeClass('bg-gray-100', 'bg-blue-200', 'bg-green-200').addClass(headerColor);
    });

    updateSettingsTable();
</script>

</body>
</html>