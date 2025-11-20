<?php
require_once dirname(__FILE__) . "/../dbconnect.php";
require_once dirname(__FILE__) . "/../class.php";

$db = new global_class();

$query = "SELECT id, id_number, fullname, email, phone, role, sex, status, availability_status FROM user_member WHERE status = '1' ORDER BY fullname ASC";
$result = $db->conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $availabilityClass = $row['availability_status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        $availabilityText = ucfirst($row['availability_status']);
        
        echo "<tr class='border-b border-gray-200 hover:bg-gray-100'>";
        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['id_number']) . "</td>";
        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['fullname']) . "</td>";
        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['sex']) . "</td>";
        echo "<td class='py-3 px-6'>";
        echo "<span class='px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800'>Verified</span>";
        echo "<span class='ml-2 px-2 py-1 rounded-full text-xs font-semibold {$availabilityClass}'>{$availabilityText}</span>";
        echo "</td>";
        echo "<td class='py-3 px-6'>";
        echo "<button class='removeBtn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded text-sm transition-colors duration-200' data-id='" . $row['id'] . "' data-name='" . htmlspecialchars($row['fullname']) . "'>Remove</button>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' class='py-3 px-6 text-center'>No verified members found</td></tr>";
}
?> 