<?php
include ('dbconnect.php');

date_default_timezone_set('Asia/Manila');

class global_class extends db_connect
{
    public function __construct()
    {
        $this->connect();
    }

    public function check_account($id, $type = 'admin') {
        $id = intval($id);
        $table = ($type === 'admin') ? 'user_admin' : 'user_member';
        
        $query = "SELECT * FROM {$table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        $stmt->close();
        return $items;
    }

     public function list_stock_logs()
    {
        $stmt = $this->conn->prepare("
            SELECT 
                stock_history.stock_id,
                stock_history.stock_type,
                stock_history.stock_outQty,
                stock_history.stock_changes,
                stock_history.stock_date,
                stock_history.stock_user_type,
                raw_materials.id AS raw_id,
                raw_materials.raw_materials_name,
                COALESCE(user_admin.id, user_member.id) AS user_id,
                COALESCE(user_admin.fullname, user_member.fullname) AS fullname,
                COALESCE('Administrator') AS role
            FROM stock_history
            LEFT JOIN user_admin
                ON stock_history.stock_user_type = 'Administrator'
                AND user_admin.id = stock_history.stock_user_id
            LEFT JOIN user_member
                ON stock_history.stock_user_type = 'member'
                AND user_member.id = stock_history.stock_user_id
            LEFT JOIN raw_materials
                ON raw_materials.id = stock_history.stock_raw_id
            ORDER BY `stock_id` DESC
        ");

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function get_raw_materials_details($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM raw_materials WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();  
        $stmt->close();
        return $data;
    }

    public function delete_raw_material($id)
    {
        try {
            $query = $this->conn->prepare("DELETE FROM raw_materials WHERE id = ?");
            if (!$query) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $query->bind_param("i", $id);
            $result = $query->execute();
            
            if (!$result) {
                throw new Exception("Execute failed: " . $query->error);
            }
            
            $query->close();
            return true;
        } catch (Exception $e) {
            error_log("Error in delete_raw_material: " . $e->getMessage());
            return false;
        }
    }

    public function update_raw_material($id, $name, $desc, $qty, $status)
    {
        $query = $this->conn->prepare("UPDATE raw_materials SET 
            raw_materials_name = ?,
            rm_description = ?,
            rm_quantity = ?,
            rm_status = ?
            WHERE id = ?");
            
        $query->bind_param("ssssi", $name, $desc, $qty, $status, $id);
        return $query->execute();
    }

    public function RawStockin($user_id, $raw_id, $stock_in_qty)
    {
       // Step 1: Get current rm_quantity
        $query = $this->conn->prepare("SELECT rm_quantity FROM raw_materials WHERE id = ?");
        $query->bind_param("i", $raw_id);
        $query->execute();
        $query->bind_result($current_qty);
        $query->fetch();
        $query->close();

        // Step 2: Add quantity
        $new_qty = $current_qty + $stock_in_qty;
        if ($new_qty < 0) {
            return false; // Prevent negative stock
        }

        // Step 3: Update rm_quantity
        $updateQty = $this->conn->prepare("UPDATE raw_materials SET rm_quantity = ? WHERE id = ?");
        $updateQty->bind_param("di", $new_qty, $raw_id);
        $resultQty = $updateQty->execute();
        $updateQty->close();

        if (!$resultQty) {
            return false;
        }

        $change_log = sprintf("%.3f -> %.3f", $current_qty, $new_qty);
        $insertLog = $this->conn->prepare("INSERT INTO stock_history (stock_raw_id,stock_user_type, stock_type,stock_outQty, stock_changes, stock_user_id) VALUES (?,'Administrator', 'Stock In',?, ?, ?)");
        $insertLog->bind_param("idsi", $raw_id, $stock_in_qty, $change_log, $user_id);
        $resultLog = $insertLog->execute();
        $insertLog->close();

        return $resultLog;
    }

    public function ProdStockin($user_id, $prod_id, $stock_in_qty)
    {
        // Step 1: Get current prod_stocks
        $query = $this->conn->prepare("SELECT prod_stocks FROM product WHERE prod_id = ?");
        $query->bind_param("i", $prod_id);
        $query->execute();
        $query->bind_result($current_qty);
        $query->fetch();
        $query->close();

        // Step 2: Add stock_in_qty to current quantity
        $new_qty = $current_qty + $stock_in_qty;
        if ($new_qty < 0) {
            return false; 
        }

        // Step 3: Update the product stock
        $updateQty = $this->conn->prepare("UPDATE product SET prod_stocks = ? WHERE prod_id = ?");
        $updateQty->bind_param("ii", $new_qty, $prod_id);
        $resultQty = $updateQty->execute();
        $updateQty->close();

        // Step 4: Insert into product_stock log
        $change_log = "$current_qty -> $new_qty";
        $insertLog = $this->conn->prepare("INSERT INTO product_stock(pstock_user_id, pstock_prod_id, pstock_stock_type, pstock_stock_outQty, pstock_stock_changes) VALUES (?, ?, 'Stock In', ?, ?)");
        $insertLog->bind_param("iiis", $user_id, $prod_id, $stock_in_qty, $change_log);
        $resultLog = $insertLog->execute();
        $insertLog->close();

        return $resultLog;
    }

    public function check_material_name_exists($raw_materials_name, $category, $exclude_id = null) {
        $query = "SELECT id FROM raw_materials WHERE LOWER(raw_materials_name) = LOWER(?) AND LOWER(category) = LOWER(?)";
        $params = [$raw_materials_name, $category];
        $types = "ss";
        
        if ($exclude_id !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= "i";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function AddRawMaterials($raw_materials_name, $category, $rm_qty, $rm_unit, $rm_status)
    {
        // For Silk material, category should be empty
        if ($raw_materials_name === 'Silk') {
            $category = '';
        }
        // Only check for duplicate if it's not Silk
        else if ($this->check_material_name_exists($raw_materials_name, $category)) {
            return ['status' => 'error', 'message' => 'A material with this name and category already exists'];
        }

        // Validate status
        $valid_statuses = ['Available', 'Not Available'];
        if (!in_array($rm_status, $valid_statuses)) {
            return ['status' => 'error', 'message' => 'Invalid status value'];
        }

        try {
            $query = $this->conn->prepare("
                INSERT INTO raw_materials (
                    raw_materials_name,
                    category,
                    rm_quantity,
                    rm_unit,
                    rm_status,
                    supplier_name
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if (!$query) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $supplier_name = isset($_POST['supplier_name']) ? $_POST['supplier_name'] : null;
            $query->bind_param("ssssss", $raw_materials_name, $category, $rm_qty, $rm_unit, $rm_status, $supplier_name);
            $result = $query->execute();
            
            if ($result) {
                $query->close();
                return ['status' => 'success', 'message' => 'Raw material added successfully'];
            } else {
                throw new Exception("Execute failed: " . $query->error);
            }
        } catch (Exception $e) {
            error_log("Error in AddRawMaterials: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to add raw material: ' . $e->getMessage()];
        }
    }

    public function UpdateRawMaterials($rm_id, $raw_materials_name, $category, $rm_quantity, $rm_unit, $rm_status, $supplier_name = '')
    {
        // Debug log input parameters
        error_log("UpdateRawMaterials called with params: " . json_encode([
            'rm_id' => $rm_id,
            'raw_materials_name' => $raw_materials_name,
            'category' => $category,
            'rm_quantity' => $rm_quantity,
            'rm_unit' => $rm_unit,
            'rm_status' => $rm_status,
            'supplier_name' => $supplier_name
        ]));

        // For Silk material, category should be empty
        if ($raw_materials_name === 'Silk') {
            $category = '';
            error_log("Material is Silk, setting category to empty string");
        }
        // Only check for duplicate if it's not Silk and category is not empty
        else if (!empty($category) && $this->check_material_name_exists($raw_materials_name, $category, $rm_id)) {
            error_log("Duplicate material found with name and category");
            return ['status' => 'error', 'message' => 'A material with this name and category already exists'];
        }

        // Validate status
        $valid_statuses = ['Available', 'Not Available'];
        if (!in_array($rm_status, $valid_statuses)) {
            error_log("Invalid status: " . $rm_status);
            return ['status' => 'error', 'message' => 'Invalid status value'];
        }

        try {
            // Get current material data
            $current = $this->conn->prepare("SELECT raw_materials_name, category FROM raw_materials WHERE id = ?");
            $current->bind_param("i", $rm_id);
            $current->execute();
            $result = $current->get_result();
            $current_data = $result->fetch_assoc();
            $current->close();

            // If material name is changing and category exists, check for duplicates
            if ($current_data && $current_data['raw_materials_name'] !== $raw_materials_name && !empty($category)) {
                if ($this->check_material_name_exists($raw_materials_name, $category)) {
                    error_log("Duplicate material found when changing name");
                    return ['status' => 'error', 'message' => 'A material with this name and category already exists'];
                }
            }

            $query = $this->conn->prepare("
                UPDATE raw_materials 
                SET raw_materials_name = ?,
                    category = ?,
                    rm_quantity = ?,
                    rm_unit = ?,
                    rm_status = ?,
                    supplier_name = ?
                WHERE id = ?
            ");
            
            if (!$query) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            error_log("About to execute update with category: " . $category);
            $query->bind_param("ssssssi", $raw_materials_name, $category, $rm_quantity, $rm_unit, $rm_status, $supplier_name, $rm_id);
            $result = $query->execute();
            
            if ($result) {
                error_log("Update successful");
                $query->close();
                return ['status' => 'success', 'message' => 'Raw material updated successfully'];
            } else {
                throw new Exception("Execute failed: " . $query->error);
            }
        } catch (Exception $e) {
            error_log("Error in UpdateRawMaterials: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to update raw material: ' . $e->getMessage()];
        }
    }

    public function RegisterMember($actionType, $userId)
    {
        if ($actionType == "verify") {
            $query = $this->conn->prepare("
                UPDATE `user_member`
                SET `status` = '1'
                WHERE `id` = ?
            ");
        } else if ($actionType == "decline") {
            $query = $this->conn->prepare("
                DELETE FROM `user_member`
                WHERE `id` = ?
            ");
        } else {
            return false;
        }
        $query->bind_param("i", $userId);
    
        $result = $query->execute();
        $query->close();
    
        return $result;
    }

    public function fetch_all_category(){
            $query = $this->conn->prepare("SELECT * FROM `product_category`");

            if ($query->execute()) {
                $result = $query->get_result();
                return $result;
            }
        }

    public function fetch_all_product(){
        $query = $this->conn->prepare("SELECT * 
        FROM `product` 
        LEFT JOIN product_category
        ON product.prod_category_id = product_category.category_id
        where prod_status='1'
        ");

        if ($query->execute()) {
            $result = $query->get_result();
            return $result;
        }
    }

    public function DeleteProduct($prod_id) {
        $status = 0; 
        $query = $this->conn->prepare(
            "UPDATE `product` SET `prod_status` = ? WHERE `prod_id` = ?"
        );
        $query->bind_param("is", $status, $prod_id);
        
        if ($query->execute()) {
            return 'success';
        } else {
            return 'Error: ' . $query->error;
        }
    }

    public function GetProductById($product_Id) {
        $query = "SELECT * FROM `product` WHERE `prod_id` = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $product_Id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $product = $result->fetch_assoc();
            $stmt->close();
            return $product;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function fetch_all_non_verify_member() {
        $sql = "SELECT 
                    um.id AS umid, 
                    um.id_number AS umid_number, 
                    um.fullname AS umfullname, 
                    um.email AS umemail, 
                    um.phone AS umphone, 
                    um.role AS umrole, 
                    um.sex AS umsex, 
                    um.date_created AS umdate_created, 
                    um.status AS umstatus
                FROM user_member AS um
                ORDER BY um.id ASC";
    
        $result = $this->conn->query($sql);
        return $result;
    }
    
    public function fetch_all_materials()
    {
        $query = "SELECT 
            id as rmid,
            raw_materials_name,
            category,
            rm_quantity,
            rm_unit,
            rm_status,
            supplier_name
        FROM raw_materials 
        ORDER BY id DESC";
        
        return $this->conn->query($query);
    }

    public function fetch_members_by_status($status)
    {
        try {
            if (!$this->conn) {
                error_log("Database connection is not available");
                return false;
            }

            $stmt = $this->conn->prepare("
                SELECT 
                    id AS umid, 
                    id_number AS umid_number, 
                    fullname AS umfullname, 
                    email AS umemail, 
                    phone AS umphone, 
                    role AS umrole, 
                    sex AS umsex, 
                    date_created AS umdate_created, 
                    status AS umstatus
                FROM user_member 
                WHERE status = ? 
                ORDER BY id DESC
            ");
            
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("i", $status);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error in fetch_members_by_status: " . $e->getMessage());
            return false;
        }
    }

    public function remove_member($member_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM user_member WHERE id = ?");
        $stmt->bind_param("i", $member_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function AddProduct($name, $description, $price, $category, $image = null)
    {
        try {
            $filename = null;
            if ($image && $image['error'] === UPLOAD_ERR_OK) {
                $filename = $this->handleFileUpload($image);
            }

            $query = $this->conn->prepare("
                INSERT INTO `product` 
                (`prod_name`, `prod_description`, `prod_price`, `prod_category_id`, `prod_image`, `prod_status`) 
                VALUES (?, ?, ?, ?, ?, 1)
            ");

            $query->bind_param("ssdss", $name, $description, $price, $category, $filename);
            
            if ($query->execute()) {
                return ['status' => 'success', 'message' => 'Product added successfully'];
            } else {
                throw new Exception($query->error);
            }
        } catch (Exception $e) {
            error_log("Error in AddProduct: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to add product: ' . $e->getMessage()];
        }
    }
}