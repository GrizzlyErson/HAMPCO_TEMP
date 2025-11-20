<?php


include ('dbconnect.php');

date_default_timezone_set('Asia/Manila');

class global_class extends db_connect
{
    public function __construct()
    {
        $this->connect();
    }


    public function LoginAdmin($username, $password)
    {
        $query = $this->conn->prepare("SELECT * FROM `user_admin` WHERE `username` = ?");
        $query->bind_param("s", $username);
    
        if ($query->execute()) {
            $result = $query->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
    
                if (password_verify($password, $user['password'])) {
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['user_type'] = "admin";
                    $query->close();
                    return ['success' => true, 'data' => $user];
                } else {
                    // Password mismatch
                    $query->close();
                    return ['success' => false, 'message' => 'Login Failed.'];
                }
            } else {
                // No user found
                $query->close();
                return ['success' => false, 'message' => 'Login Failed.'];
            }
        } else {
            $query->close();
            return ['success' => false, 'message' => 'Database error during execution.'];
        }
    }



    public function LoginMember($id_number, $password)
    {
        $query = $this->conn->prepare("SELECT * FROM `user_member` WHERE `id_number` = ?");
        $query->bind_param("s", $id_number);
    
        if ($query->execute()) {
            $result = $query->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
    
                if (password_verify($password, $user['password'])) {
                    // Check verification status
                    if ($user['status'] == '0') {
                        $query->close();
                        return ['success' => false, 'message' => 'Your account is pending verification. Please wait for administrator approval.'];
                    }
                    
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
    
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['user_type'] = "member";
                    $query->close();
                    return ['success' => true, 'data' => $user];
                } else {
                    // Password mismatch
                    $query->close();
                    return ['success' => false, 'message' => 'Incorrect password.'];
                }
            } else {
                // No user found
                $query->close();
                return ['success' => false, 'message' => 'Member ID not found.'];
            }
        } else {
            $query->close();
            return ['success' => false, 'message' => 'Database error during execution.'];
        }
    }
    










     public function LoginCustomer($email, $password)
    {
        $query = $this->conn->prepare("SELECT * FROM `user_customer` WHERE `customer_email` = ? AND `customer_status` = '1'");
        $query->bind_param("s", $email);
    
        if ($query->execute()) {
            $result = $query->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
    
                if (password_verify($password, $user['customer_password'])) {
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
    
                    $_SESSION['customer_id'] = $user['customer_id'];
                    $query->close();
                    return ['success' => true, 'data' => $user];
                } else {
                    // Password mismatch
                    $query->close();
                    return ['success' => false, 'message' => 'Incorrect password.'];
                }
            } else {
                // No user found
                $query->close();
                return ['success' => false, 'message' => 'Email not found or account inactive.'];
            }
        } else {
            $query->close();
            return ['success' => false, 'message' => 'Database error during execution.'];
        }
    }
    



    public function RegisterMember($fname, $mname, $email, $phone, $role, $sex, $password)
    {
        try {
            // Step 1: Check if the email already exists in the database
            $query = $this->conn->prepare("SELECT COUNT(*) as count FROM `user_member` WHERE `email` = ?");
            if (!$query) {
                error_log("Prepare failed for email check: " . $this->conn->error);
                return ['success' => false, 'message' => 'Database error during email check.'];
            }
            
            $query->bind_param("s", $email);
            if (!$query->execute()) {
                error_log("Execute failed for email check: " . $query->error);
                $query->close();
                return ['success' => false, 'message' => 'Database error while checking email.'];
            }
            
            $result = $query->get_result();
            $row = $result->fetch_assoc();
            $emailCount = $row['count'];
            $query->close();
        
            // If email already exists, return error message
            if ($emailCount > 0) {
                return ['success' => false, 'message' => 'Email is already registered.'];
            }
        
            // Step 2: Generate a unique member ID
            $year = date('Y');
            $role_prefix = strtoupper(substr($role, 0, 3));
            
            // Get the last sequence number for this role and year
            $query = $this->conn->prepare("
                SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(id_number, '-', -1) AS UNSIGNED)), 0) as last_seq 
                FROM user_member 
                WHERE id_number LIKE ?
            ");
            if (!$query) {
                error_log("Prepare failed for sequence check: " . $this->conn->error);
                return ['success' => false, 'message' => 'Database error during ID generation.'];
            }

            $pattern = $role_prefix . '-' . $year . '-%';
            $query->bind_param("s", $pattern);
            if (!$query->execute()) {
                error_log("Execute failed for sequence check: " . $query->error);
                $query->close();
                return ['success' => false, 'message' => 'Database error during ID generation.'];
            }

            $result = $query->get_result();
            if (!$result) {
                error_log("Get result failed for sequence check: " . $this->conn->error);
                $query->close();
                return ['success' => false, 'message' => 'Database error during ID generation.'];
            }

            $row = $result->fetch_assoc();
            if (!$row) {
                error_log("Fetch assoc failed for sequence check");
                $query->close();
                return ['success' => false, 'message' => 'Database error during ID generation.'];
            }

            $last_seq = $row['last_seq'] ?? 0;
            $query->close();
            
            // Generate new sequence number
            $new_seq = str_pad($last_seq + 1, 3, '0', STR_PAD_LEFT);
            $id_number = $role_prefix . '-' . $year . '-' . $new_seq;
        
            // Step 3: If email doesn't exist, proceed with registration
            $fullname = $fname . ' ' . $mname;
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
            $query = $this->conn->prepare("
                INSERT INTO `user_member`
                    (`fullname`, `email`, `phone`, `password`, `role`, `sex`, `id_number`, `status`)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)
            ");
        
            if (!$query) {
                error_log("Prepare failed for member insert: " . $this->conn->error);
                return ['success' => false, 'message' => 'Database error during registration.'];
            }
        
            $query->bind_param(
                "sssssss",
                $fullname,
                $email,
                $phone,
                $hashedPassword,
                $role,
                $sex,
                $id_number
            );
        
            if (!$query->execute()) {
                error_log("Execute failed for member insert: " . $query->error);
                $query->close();
                return ['success' => false, 'message' => 'Registration failed. Please try again.'];
            }
            
            $query->close();
            return ['success' => true, 'message' => 'Registration successful! Your Member ID is: ' . $id_number];
        } catch (Exception $e) {
            error_log("Exception in RegisterMember: " . $e->getMessage());
            return ['success' => false, 'message' => 'An unexpected error occurred. Please try again.'];
        }
    }
    

    




 public function RegisterCustomer($fullname, $email, $phone,$password)
    {
        // Step 1: Check if the email already exists in the database
        $query = $this->conn->prepare("SELECT COUNT(*) FROM `user_customer` WHERE `customer_email` = ?");
        if (!$query) {
            return false; // Query preparation failed
        }
        $query->bind_param("s", $email);
        $query->execute();
        $query->bind_result($emailCount);
        $query->fetch();
        $query->close();
        // If email already exists, return false or an error message
        if ($emailCount > 0) {
            return "Email is already registered."; // Or you can return a specific error code/message
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        $query = $this->conn->prepare("
            INSERT INTO `user_customer`
                (`customer_fullname`, `customer_email`, `customer_phone`, `customer_password`)
            VALUES (?, ?, ?, ?)
        ");
        $query->bind_param(
            "ssss",
            $fullname,
            $email,
            $phone,
            $hashedPassword
        );
        $result = $query->execute();
        $query->close();
        return $result;
    }








public function check_account($id) {

    $id = intval($id);

    $query = "SELECT * FROM user_member WHERE id = $id";

    $result = $this->conn->query($query);

    $items = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    return $items; 
}

    



}