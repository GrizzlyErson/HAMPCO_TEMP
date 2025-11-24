<?php
session_start();
include('backend/class.php');

$db = new global_class();

// Check if user is logged in and is an admin
if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    session_destroy();
    header('location: ../login_admin.php');
    exit();
}

// Verify admin account exists
$id = intval($_SESSION['id']);
$On_Session = $db->check_account($id, 'admin');

if (empty($On_Session)) {
    session_destroy();
    header('location: ../login_admin.php');
    exit();
}else{
    header('index.php');
}

// Store current page in session for refresh handling
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$_SESSION['current_admin_page'] = $current_page;
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HAMPCO || ADMINISTRATOR</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/css/alertify.css" integrity="sha512-MpdEaY2YQ3EokN6lCD6bnWMl5Gwk7RjBbpKLovlrH6X+DRokrPRAF3zQJl1hZUiLXfo2e9MrOt+udOnHCAmi5w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/alertify.min.js" integrity="sha512-JnjG+Wt53GspUQXQhc+c4j8SBERsgJAoHeehagKHlxQN+MtCCmFDghX9/AcbkkNRZptyZU4zC8utK59M5L45Iw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  

</head>
<body class="hampco-admin-sidebar-layout">

<input type="text" id="user_id" value="<?=$On_Session[0]['id']?>" hidden>

<?php include "navbar.php"; ?>

<main>



   

     