<?php
require_once "../function/database.php";
require_once "components/header.php";

$db = new Database();
$current_status = 'available';

try {
    $stmt = $db->conn->prepare("SELECT availability_status FROM user_member WHERE id = ?");
    if ($stmt) {
        $member_id = $_SESSION['id'] ?? 0;
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $current_status = $row['availability_status'] ?? 'available';
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Failed to fetch availability status: " . $e->getMessage());
}
?>

<body class="hampco-admin-sidebar-layout">


    <!-- Begin Page Content -->
        <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                        <h1 class="h3 mb-0 text-gray-800">DASHBOARD  </h1>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-cart-plus"></i>
                            <div class="bg-white rounded-lg shadow-sm p-2 flex space-x-2">
                                <button id="availableBtn" class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 <?php echo $current_status === 'available' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                                    Available
                                </button>
                                <button id="unavailableBtn" class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 <?php echo $current_status === 'unavailable' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                                    Unavailable
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Pending Tasks</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                In Progress</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-4 col-md-12 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Completed Tasks</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    

                    <!-- Content Row -->

                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4" style="max-height: 347px;">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Recent Task</h6>
   
                                    
                                </div>
                                <div>
                                    <div class="table-responsive" style="overflow-y: auto; max-height: 290px;">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th scope="col">Product</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Deadline</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>No Record</td>
                                            <td>HAMPCO!: Unknown</td>
                                            <td>Undefined</td>
                                        </tr>
                                        <tr>
                                            <td>No Record</td>
                                            <td>HAMPCO!: Unknown</td>
                                            <td>Undefined</td>
                                        </tr>
                                        <tr>
                                            <td>No Record</td>
                                            <td>HAMPCO!: Unknown</td>
                                            <td>Undefined</td>
                                        </tr>
                                        <tr>
                                            <td>No Record</td>
                                            <td>HAMPCO!: Unknown</td>
                                            <td>Undefined</td>
                                        </tr>
                                        <tr>
                                            <td>No Record</td>
                                            <td>HAMPCO!: Unknown</td>
                                            <td>Undefined</td>
                                        </tr>
                                        <tr>
                                            <td>No Record</td>
                                            <td>HAMPCO!: Unknown</td>
                                            <td>Undefined</td>
                                        </tr>


                                        </tbody>
                                    </table>
                        </div>

                                </div>


                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4" >
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Task Progress</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body" style="max-height: 415px;">
                                    <h1>NO TASK PROGRESS</h1>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col-lg-12 mb-4">

                            <!-- Project Card Example -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Current Task Details</h6>
                                </div>
                                <div class="card-body">
                                    <h1>NO TASK DETAILS</h1>
                                </div>
                            </div>

                            <!-- Color System -->


                        </div>

                        
                    </div>

                </div>





    
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableBtn = document.getElementById('availableBtn');
    const unavailableBtn = document.getElementById('unavailableBtn');

    function updateAvailabilityStatus(status) {
        if (availableBtn) availableBtn.disabled = true;
        if (unavailableBtn) unavailableBtn.disabled = true;

        fetch('backend/end-points/update_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (status === 'available') {
                    if (availableBtn) {
                        availableBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                        availableBtn.classList.add('bg-green-500', 'text-white');
                    }
                    if (unavailableBtn) {
                        unavailableBtn.classList.remove('bg-red-500', 'text-white');
                        unavailableBtn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    }
                } else {
                    if (unavailableBtn) {
                        unavailableBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                        unavailableBtn.classList.add('bg-red-500', 'text-white');
                    }
                    if (availableBtn) {
                        availableBtn.classList.remove('bg-green-500', 'text-white');
                        availableBtn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Status Updated',
                    text: `Your status has been set to ${status}`,
                    timer: 1800,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: data.message || 'Failed to update status'
                });
            }
        })
        .catch(error => {
            console.error('Error updating availability:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating status'
            });
        })
        .finally(() => {
            if (availableBtn) availableBtn.disabled = false;
            if (unavailableBtn) unavailableBtn.disabled = false;
        });
    }

    if (availableBtn && unavailableBtn) {
        availableBtn.addEventListener('click', () => updateAvailabilityStatus('available'));
        unavailableBtn.addEventListener('click', () => updateAvailabilityStatus('unavailable'));
    }
});
</script>

<?php require_once "components/footer.php"; ?>