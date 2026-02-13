<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

// Create receivers table if it doesn't exist
$receivers_sql = "CREATE TABLE IF NOT EXISTS receivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    blood_group_needed VARCHAR(5) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_request_date DATETIME,
    total_requests_fulfilled INT DEFAULT 0
)";

$blood_requests_sql = "CREATE TABLE IF NOT EXISTS blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receiver_id INT NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    quantity INT NOT NULL,
    city VARCHAR(50) NOT NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    request_status VARCHAR(20) DEFAULT 'Pending',
    fulfilled_by_donor_id INT,
    fulfillment_date DATETIME,
    FOREIGN KEY (receiver_id) REFERENCES receivers(id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_status (request_status)
)";

$conn->query($receivers_sql);
$conn->query($blood_requests_sql);

// Fetch all pending hospital requests
$sql = "SELECT r.*, h.name AS hospital_name, 'Hospital' as request_type
        FROM requests r 
        JOIN hospitals h ON r.hospital_id = h.id
        WHERE r.status='Pending'
        ORDER BY r.request_date DESC";

$result = $conn->query($sql);

// Fetch all pending receiver blood requests  
$receiver_sql = "SELECT br.*, rec.name AS receiver_name, 'Receiver' as request_type
        FROM blood_requests br
        JOIN receivers rec ON br.receiver_id = rec.id
        WHERE br.request_status='Pending'
        ORDER BY br.request_date DESC";

$receiver_result = $conn->query($receiver_sql);

// Merge both results
$all_requests = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_requests[] = $row;
    }
}
if ($receiver_result) {
    while ($row = $receiver_result->fetch_assoc()) {
        $all_requests[] = $row;
    }
}

// Sort by date (newest first)
usort($all_requests, function($a, $b) {
    return strtotime($b['request_date']) - strtotime($a['request_date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Emergency Blood Requests</title>
<link rel="stylesheet" href="../main.css">
<style>
    body {
        padding-top: 120px;
        background: linear-gradient(135deg, #f0f9ff, #ffffff);
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    h2 {
        text-align: center;
        color: #c0392b;
        margin-bottom: 30px;
        font-size: 32px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    table th {
        background: #1f3c88;
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    
    table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    
    table tr:hover {
        background: #f9f9f9;
    }
    
    table tr:last-child td {
        border-bottom: none;
    }
</style>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container">
        <h2>🚨 Emergency Blood Requests</h2>

<?php
if (!empty($all_requests)) {
    echo "<table>
            <tr>
                <th>Request From</th>
                <th>Type</th>
                <th>Blood Group</th>
                <th>Quantity</th>
                <th>City</th>
                <th>Request Date</th>
            </tr>";
    foreach($all_requests as $row) {
        // Highlight rare blood groups
        $bg_color = in_array($row['blood_group'], ['AB-', 'O-']) ? 'style="background-color:#f9d6d5;"' : '';
        $name = ($row['request_type'] === 'Hospital') ? $row['hospital_name'] : $row['receiver_name'];
        $type_badge = ($row['request_type'] === 'Hospital') ? '<span style="background: #c0392b; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">🏥 Hospital</span>' : '<span style="background: #2980b9; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">👤 Receiver</span>';
        echo "<tr $bg_color>
                <td>" . htmlspecialchars($name) . "</td>
                <td>$type_badge</td>
                <td>" . htmlspecialchars($row['blood_group']) . "</td>
                <td>" . htmlspecialchars($row['quantity']) . "</td>
                <td>" . htmlspecialchars($row['city']) . "</td>
                <td>" . date('M d, Y H:i', strtotime($row['request_date'])) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;color:#c0392b;font-weight:bold;'>No pending emergency requests from hospitals or receivers.</p>";
}
?>
    </div>
</body>
</html>


</head>

<body style="padding-top:140px;">

<!-- ================= SAS NAVBAR (TOP) ================= -->

<!-- Navbar -->

<!-- Mobile menu script -->
<script>
const toggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('nav ul');
toggle.addEventListener('click', () => {
    menu.classList.toggle('show');
});
</script>

<script>
const toggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('nav ul');
toggle.addEventListener('click', () => {
    menu.classList.toggle('show');
});
</script>
