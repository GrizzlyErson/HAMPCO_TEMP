<?php
header('Content-Type: application/json');

require_once 'dbconnect.php';

// Function to calculate linear regression
function linear_regression($x, $y) {
    $n = count($x);
    if ($n == 0) return ['slope' => 0, 'intercept' => 0];
    if ($n == 1) return ['slope' => 0, 'intercept' => $y[0]];
    $sumX = array_sum($x);
    $sumY = array_sum($y);
    $sumX2 = 0;
    $sumXY = 0;
    for ($i = 0; $i < $n; $i++) {
        $sumX2 += $x[$i] * $x[$i];
        $sumXY += $x[$i] * $y[$i];
    }
    $denominator = ($n * $sumX2 - $sumX * $sumX);
    if ($denominator == 0) return ['slope' => 0, 'intercept' => $sumY / $n];
    
    $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
    $intercept = ($sumY - $slope * $sumX) / $n;
    return ['slope' => $slope, 'intercept' => $intercept];
}

try {
    // Fetch monthly sales data for completed orders
    $sql = "SELECT 
                DATE_FORMAT(date_created, '%Y-%m') AS month, 
                SUM(total_amount) AS monthly_sales
            FROM orders 
            WHERE order_status IN ('Shipped', 'Delivered')
            GROUP BY DATE_FORMAT(date_created, '%Y-%m')
            ORDER BY month ASC";

    $stmt = $db->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $salesData = [];
    while ($row = $result->fetch_assoc()) {
        $salesData[] = $row;
    }

    // Prepare data for forecasting
    $months = array_column($salesData, 'month');
    $monthlySales = array_column($salesData, 'monthly_sales');
    $monthlySales = array_map('floatval', $monthlySales);
    $timePeriods = range(1, count($monthlySales));

    // Calculate Moving Average (3-month period)
    $movingAverage = [];
    $period = 3;
    for ($i = 0; $i < count($monthlySales); $i++) {
        if ($i < $period - 1) {
            $movingAverage[] = null; // Not enough data for MA
        } else {
            $slice = array_slice($monthlySales, $i - ($period - 1), $period);
            $movingAverage[] = array_sum($slice) / $period;
        }
    }
    
    // Linear Regression on the original data
    $regression = linear_regression($timePeriods, $monthlySales);
    $slope = $regression['slope'];
    $intercept = $regression['intercept'];

    // Predict next month's sales
    $nextTimePeriod = count($timePeriods) + 1;
    $nextMonthPrediction = ($slope * $nextTimePeriod) + $intercept;
    
    // Generate regression line data for chart
    $regressionLine = [];
    foreach($timePeriods as $tp) {
        $regressionLine[] = ($slope * $tp) + $intercept;
    }

    // Get the last month and predict the next one
    $nextMonthLabel = 'Next Month';
    if(count($months) > 0) {
        $lastMonth = end($months);
        $nextMonthDate = new DateTime($lastMonth . '-01');
        $nextMonthDate->modify('+1 month');
        $nextMonthLabel = $nextMonthDate->format('Y-m');
    }

    $response = [
        'success' => true,
        'labels' => $months,
        'sales' => $monthlySales,
        'movingAverage' => $movingAverage,
        'regressionLine' => $regressionLine,
        'prediction' => [
            'month' => $nextMonthLabel,
            'sales' => $nextMonthPrediction > 0 ? $nextMonthPrediction : 0
        ]
    ];

} catch (Exception $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
}

echo json_encode($response);
?>
