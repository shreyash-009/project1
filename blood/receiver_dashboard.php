<?php
session_start();

// Check if user is logged in and is a receiver
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'receiver') {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

// Create tables if they don't exist
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

// Get receiver info - first check if receiver_id is in session
if (!isset($_SESSION['receiver_id'])) {
    // Try to find receiver in database
    $receiver_query = "SELECT id, name, blood_group_needed FROM receivers LIMIT 1";
    $receiver_result = $conn->query($receiver_query);
    
    if ($receiver_result->num_rows == 0) {
        header("Location: receiver_register.php");
        exit();
    }
    
    $receiver = $receiver_result->fetch_assoc();
    $_SESSION['receiver_id'] = $receiver['id'];
} else {
    // Fetch receiver's info
    $receiver_id = $_SESSION['receiver_id'];
    $receiver_query = "SELECT id, name, blood_group_needed FROM receivers WHERE id = $receiver_id";
    $receiver_result = $conn->query($receiver_query);
    
    if ($receiver_result->num_rows == 0) {
        header("Location: receiver_register.php");
        exit();
    }
    
    $receiver = $receiver_result->fetch_assoc();
}

$receiver_name = $receiver['name'];
$receiver_id = $receiver['id'];

// Fetch all pending blood requests from this receiver
$requests_sql = "SELECT * FROM blood_requests WHERE receiver_id = $receiver_id ORDER BY request_date DESC";
$requests_result = $conn->query($requests_sql);

// Fetch donors matching the receiver's blood type (notification system)
function get_compatible_donors($blood_group) {
    global $conn;
    
    $compatibility = [
        'O+' => ['O+', 'A+', 'B+', 'AB+'],
        'O-' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
        'A+' => ['A+', 'AB+'],
        'A-' => ['A-', 'A+', 'AB-', 'AB+'],
        'B+' => ['B+', 'AB+'],
        'B-' => ['B-', 'B+', 'AB-', 'AB+'],
        'AB+' => ['AB+'],
        'AB-' => ['AB-', 'AB+']
    ];
    
    $compatible_types = isset($compatibility[$blood_group]) ? $compatibility[$blood_group] : [$blood_group];
    $placeholders = implode("','", $compatible_types);
    
    $sql = "SELECT * FROM donors WHERE blood_group IN ('$placeholders') LIMIT 20";
    return $conn->query($sql);
}

$compatible_donors = get_compatible_donors($receiver['blood_group_needed']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiver Dashboard - Blood Request Management</title>
    <link rel="stylesheet" href="../main.css">
    <style>
        body {
            padding-top: 120px;
            background: linear-gradient(135deg, #f0f9ff, #ffffff);
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .hero-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .hero-section h1 {
            color: #c0392b;
            margin: 0 0 10px 0;
        }
        
        .hero-section p {
            color: #666;
            margin: 0;
        }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #1f3c88;
            border-bottom: 3px solid #2980b9;
            padding-bottom: 12px;
            margin-top: 0;
        }
        
        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .requests-table th, .requests-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .requests-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .requests-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-pending {
            color: #e67e22;
            font-weight: bold;
        }
        
        .status-fulfilled {
            color: #27ae60;
            font-weight: bold;
        }
        
        .donor-card {
            background: linear-gradient(135deg, #e3f2fd, #f0f9ff);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 4px solid #2980b9;
        }
        
        .donor-card h4 {
            color: #c0392b;
            margin: 0 0 8px 0;
        }
        
        .donor-card p {
            color: #555;
            margin: 5px 0;
            font-size: 14px;
        }
        
        .donors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .no-data {
            color: #999;
            text-align: center;
            padding: 30px;
            font-style: italic;
        }
        
        .notification {
            background: #e8f4f8;
            border-left: 4px solid #2980b9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .notification.success {
            background: #d4edda;
            border-left-color: #27ae60;
        }
        
        .notification.info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1>🩸 Welcome, <?php echo htmlspecialchars($receiver_name); ?>!</h1>
            <p>Your Blood Group: <strong><?php echo htmlspecialchars($receiver['blood_group_needed']); ?></strong></p>
            <p>Manage your blood requests and connect with available donors</p>
        </div>

        <!-- Your Blood Requests Section -->
        <div class="section">
            <h2>📋 Your Blood Requests</h2>
            
            <?php
            if ($requests_result->num_rows > 0) {
                echo "
                <table class='requests-table'>
                    <tr>
                        <th>Blood Group</th>
                        <th>Quantity (Units)</th>
                        <th>City</th>
                        <th>Request Date</th>
                        <th>Status</th>
                    </tr>";
                
                while ($row = $requests_result->fetch_assoc()) {
                    $status_class = ($row['request_status'] === 'Pending') ? 'status-pending' : 'status-fulfilled';
                    echo "
                    <tr>
                        <td><strong>" . htmlspecialchars($row['blood_group']) . "</strong></td>
                        <td>" . htmlspecialchars($row['quantity']) . "</td>
                        <td>" . htmlspecialchars($row['city']) . "</td>
                        <td>" . date('M d, Y H:i', strtotime($row['request_date'])) . "</td>
                        <td class='$status_class'>" . htmlspecialchars($row['request_status']) . "</td>
                    </tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='no-data'>❌ No blood requests yet. <a href='receiver_register.php'>Submit your first request</a></p>";
            }
            ?>
        </div>

        <!-- Available Donors Section -->
        <div class="section">
            <h2>🔔 Available Donors (Compatible with Your Blood Type)</h2>
            
            <?php
            if ($compatible_donors->num_rows > 0) {
                echo "<div class='notification info'>✅ Found " . $compatible_donors->num_rows . " compatible donor(s)! They have been notified of your need.</div>";
                echo "<div class='donors-grid'>";
                
                while ($donor = $compatible_donors->fetch_assoc()) {
                    echo "
                    <div class='donor-card'>
                        <h4>💉 " . htmlspecialchars($donor['name']) . "</h4>
                        <p><strong>Blood Group:</strong> " . htmlspecialchars($donor['blood_group']) . "</p>
                        <p><strong>City:</strong> " . htmlspecialchars($donor['city'] ?? 'N/A') . "</p>
                        <p><strong>Status:</strong> " . (($donor['available'] ?? 0) ? '<span style=\"color: #27ae60; font-weight: bold;\">✓ Available</span>' : '<span style=\"color: #e74c3c;\">Not Available</span>') . "</p>
                        <p style='font-size: 12px; color: #999;'>Last registered: " . (isset($donor['registration_date']) ? date('M d, Y', strtotime($donor['registration_date'])) : 'N/A') . "</p>
                    </div>";
                }
                echo "</div>";
            } else {
                echo "<div class='notification'>;⚠️ No donors currently registered with a compatible blood type. Check back soon!</div>";
            }
            ?>
        </div>

        <!-- Quick Stats Section -->
        <div class="section">
            <h2>📊 Quick Stats</h2>
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 8px;">
                    <h3 style="margin: 0; font-size: 24px;">
                        <?php 
                        $pending_count = 0;
                        $requests_result->data_seek(0);
                        while ($row = $requests_result->fetch_assoc()) {
                            if ($row['request_status'] === 'Pending') {
                                $pending_count++;
                            }
                        }
                        echo $pending_count;
                        ?>
                    </h3>
                    <p style="margin: 0;">Pending Requests</p>
                </div>
                
                <div style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 20px; border-radius: 8px;">
                    <h3 style="margin: 0; font-size: 24px;"><?php echo $compatible_donors->num_rows; ?></h3>
                    <p style="margin: 0;">Compatible Donors</p>
                </div>
                
                <div style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; padding: 20px; border-radius: 8px;">
                    <h3 style="margin: 0; font-size: 24px;">
                        <?php 
                        $fulfilled_count = 0;
                        $requests_result->data_seek(0);
                        while ($row = $requests_result->fetch_assoc()) {
                            if ($row['request_status'] !== 'Pending') {
                                $fulfilled_count++;
                            }
                        }
                        echo $fulfilled_count;
                        ?>
                    </h3>
                    <p style="margin: 0;">Fulfilled Requests</p>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="section" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
            <h2 style="color: #c0392b;">❓ How This Works</h2>
            <ol style="color: #333; line-height: 1.8;">
                <li><strong>Register:</strong> Complete your receiver profile with your blood type and location.</li>
                <li><strong>Submit Request:</strong> Create a blood request for the amount you need.</li>
                <li><strong>Get Notified:</strong> Compatible donors are automatically notified of your request.</li>
                <li><strong>Connect:</strong> Donors can view your request and choose to help you.</li>
                <li><strong>Track Status:</strong> Monitor your requests and their fulfillment status in the dashboard.</li>
            </ol>
        </div>
    </div>

</body>
</html>
