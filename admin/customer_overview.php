<?php include "components/header.php";?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sales Forecasting</h1>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Predicted Sales Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Predicted Sales for <span id="predictionMonth">Next Month</span>
                        </div>
                        <div id="predictedSalesCard" class="h5 mb-0 font-weight-bold text-gray-800">₱0.00</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Forecasting Chart -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Monthly Sales and Forecast</h6>
    </div>
    <div class="card-body">
        <div class="chart-area">
            <canvas id="salesForecastChart"></canvas>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchSalesDataAndRenderChart();
    fetchMostSoldProducts();
    fetchTopCustomers();
});

function fetchSalesDataAndRenderChart() {
    fetch('backend/get_sales_data_for_forecasting.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderSalesChart(data);
                updatePredictionCard(data.prediction);
            } else {
                console.error('Failed to fetch sales data for forecasting:', data.error);
            }
        })
        .catch(error => console.error('Error fetching sales data:', error));
}

function updatePredictionCard(prediction) {
    document.getElementById('predictionMonth').textContent = prediction.month;
    const predictedSales = parseFloat(prediction.sales).toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
    document.getElementById('predictedSalesCard').textContent = predictedSales;
}

function renderSalesChart(data) {
    const ctx = document.getElementById('salesForecastChart').getContext('2d');
    
    const labels = data.labels;
    
    // Add the prediction month to the labels for the chart
    const extendedLabels = [...labels, data.prediction.month];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: extendedLabels,
            datasets: [
                {
                    label: 'Monthly Sales',
                    data: data.sales,
                    backgroundColor: 'rgba(78, 115, 223, 0.5)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    order: 2
                },
                {
                    label: '3-Month Moving Average',
                    data: data.movingAverage,
                    type: 'line',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.1,
                    order: 1
                },
                {
                    label: 'Linear Regression Trend',
                    data: data.regressionLine,
                    type: 'line',
                    borderColor: 'rgba(246, 194, 62, 1)',
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.1,
                    order: 0
                },
                {
                    label: 'Predicted Sales',
                    data: [...new Array(data.sales.length).fill(null), data.prediction.sales],
                    backgroundColor: 'rgba(217, 30, 24, 0.5)',
                    borderColor: 'rgba(217, 30, 24, 1)',
                    borderWidth: 1,
                    order: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
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
