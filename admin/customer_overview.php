<?php include "components/header.php";?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customer Overview</h1>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Total Sales Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Sales</div>
                        <div id="totalSalesCard" class="h5 mb-0 font-weight-bold text-gray-800">₱0.00</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Orders Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Completed Orders</div>
                        <div id="completedOrdersCard" class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pending Orders Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Pending Orders</div>
                        <div id="totalPendingOrdersCard" class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Most Sold Products -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Most Sold Products</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="mostSoldProductsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Total Quantity Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top 20 Customers -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Top 20 Customers</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="topCustomersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "components/footer.php";?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchMostSoldProducts();
    fetchTopCustomers();
    fetchSummaryData();
});

function fetchSummaryData() {
    // Fetch Total Sales
    fetch('backend/get_total_sales.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const totalSales = parseFloat(data.total_sales).toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
                document.getElementById('totalSalesCard').textContent = totalSales;
            }
        })
        .catch(error => console.error('Error fetching total sales:', error));

    // Fetch Completed Orders
    fetch('backend/get_completed_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('completedOrdersCard').textContent = data.completed_orders;
            }
        })
        .catch(error => console.error('Error fetching completed orders:', error));

    // Fetch Total Pending Orders
    fetch('backend/get_pending_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalPendingOrdersCard').textContent = data.pending_orders;
            }
        })
        .catch(error => console.error('Error fetching total pending orders:', error));
}

function loadTopSalesChart() {
    fetch('backend/get_top_sales_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const salesData = data.data;
                const labels = salesData.map(item => item.customer_name);
                const values = salesData.map(item => item.total_sales);

                const ctx = document.getElementById('topSalesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Sales',
                            data: values,
                            backgroundColor: '#4e73df',
                            borderColor: '#4e73df',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value, index, values) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += '₱' + context.parsed.y.toLocaleString();
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => console.error('Error loading top sales chart:', error));
}

function fetchMostSoldProducts() {
    fetch('backend/end-points/get_most_sold_products.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tableBody = document.getElementById('mostSoldProductsTable').getElementsByTagName('tbody')[0];
                tableBody.innerHTML = ''; // Clear existing data
                data.products.forEach(product => {
                    let row = tableBody.insertRow();
                    row.innerHTML = `
                        <td>${product.product_id}</td>
                        <td>${product.product_name}</td>
                        <td>${product.total_quantity_sold}</td>
                    `;
                });
            } else {
                console.error('Failed to fetch most sold products:', data.error);
            }
        })
        .catch(error => console.error('Error fetching most sold products:', error));
}

function fetchTopCustomers() {
    fetch('backend/end-points/get_top_customers.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tableBody = document.getElementById('topCustomersTable').getElementsByTagName('tbody')[0];
                tableBody.innerHTML = ''; // Clear existing data
                data.customers.forEach(customer => {
                    let row = tableBody.insertRow();
                    row.innerHTML = `
                        <td>${customer.customer_id}</td>
                        <td>${customer.customer_fullname}</td>
                        <td>${customer.total_orders}</td>
                        <td>${customer.total_spent}</td>
                    `;
                });
            } else {
                console.error('Failed to fetch top customers:', data.error);
            }
        })
        .catch(error => console.error('Error fetching top customers:', error));
}
</script>
