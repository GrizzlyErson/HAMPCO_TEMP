<?php
include('../class.php');

$db = new global_class();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['requestType'])) {
        if ($_POST['requestType'] == 'RegisterMember') {
            $fname = $_POST['first-name'];
            $mname = $_POST['last-name'];
            $email = $_POST['email'];
            $role = $_POST['role'];
            $sex = $_POST['sex'];
            $password = $_POST['password'];
            $confirmpass = $_POST['confirm-password'];
            $phone = $_POST['phone'];

            $result = $db->RegisterMember($fname, $mname, $email, $phone, $role, $sex, $password);
            
            if (is_array($result) && isset($result['success'])) {
                echo json_encode([
                    'status' => $result['success'] ? 'success' : 'error',
                    'message' => $result['message']
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result
                ]);
            }
        } else if ($_POST['requestType'] == 'RegisterCustomer') {
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $password = $_POST['password'];
        
            $result = $db->RegisterCustomer($fullname, $email, $phone, $password);
            if ($result === true) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Registration successful!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result 
                ]);
            }
        } else if ($_POST['requestType'] == 'LoginMember') {
            $id_number = $_POST['id_number'];
            $password = $_POST['password'];

            $result = $db->LoginMember($id_number, $password);

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful!',
                    'data' => $result['data'] 
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message'] 
                ]);
            }
        } else if ($_POST['requestType'] == 'LoginCustomer') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $result = $db->LoginCustomer($email, $password);

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful!',
                    'data' => $result['data'] 
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message'] 
                ]);
            }
        } else if ($_POST['requestType'] == 'Login_Admin') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $result = $db->LoginAdmin($username, $password);

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful!',
                    'data' => $result['data'] 
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message'] 
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request type'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Access Denied! No Request Type.'
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>