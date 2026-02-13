<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include 'db.php';

// Check if user has registered as a donor
// First check if donor_id is in session
if (!isset($_SESSION['donor_id'])) {
    // Try to find donor by name (from registration form it would be stored)
    // As a fallback, just get the first donor (this is a limitation of the current system)
    // In a real app, you'd link donors to users via user_id
    $donor_query = "SELECT id, blood_group, name FROM donors LIMIT 1";
    $donor_result = $conn->query($donor_query);
    
    if ($donor_result->num_rows == 0) {
        header("Location: donor_register.php");
        exit();
    }
    
    $donor = $donor_result->fetch_assoc();
    $_SESSION['donor_id'] = $donor['id'];
} else {
    // Fetch donor's blood type
    $donor_id = $_SESSION['donor_id'];
    $donor_query = "SELECT blood_group, name FROM donors WHERE id = $donor_id";
    $donor_result = $conn->query($donor_query);
    
    if ($donor_result->num_rows == 0) {
        header("Location: donor_register.php");
        exit();
    }
}

$donor = isset($donor) ? $donor : $donor_result->fetch_assoc();
$blood_type = $donor['blood_group'];
$donor_name = $donor['name'];

// Get compatible blood types for this donor
$compatible_types = get_compatible_blood_types($blood_type);
$placeholders = implode("','", $compatible_types);

// Fetch emergency requests matching the donor's blood type (including hospital contact)
$sql = "SELECT r.*, h.name AS hospital_name, h.city, h.phone as hospital_phone, 'Hospital' as request_type
        FROM requests r 
        JOIN hospitals h ON r.hospital_id = h.id
        WHERE r.blood_group IN ('$placeholders')
        AND r.status='Pending'
        ORDER BY r.request_date DESC";

$result = $conn->query($sql);

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

// Create notifications table
$notifications_sql = "CREATE TABLE IF NOT EXISTS donor_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    request_id INT,
    request_type VARCHAR(20),
    blood_group VARCHAR(5),
    requester_name VARCHAR(100),
    message TEXT,
    notification_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT DEFAULT 0,
    INDEX idx_donor (donor_id),
    INDEX idx_read (is_read)
)";

$conn->query($notifications_sql);

// Function to create notification
function createNotification($donor_id, $request_id, $request_type, $blood_group, $requester_name) {
    global $conn;
    $message = "A " . $request_type . " needs blood type " . $blood_group . " from " . $requester_name;
    $sql = "INSERT INTO donor_notifications (donor_id, request_id, request_type, blood_group, requester_name, message)
            VALUES ('$donor_id', '$request_id', '$request_type', '$blood_group', '$requester_name')";
    return $conn->query($sql);
}

// Check for unread notifications
$donor_id = $_SESSION['donor_id'] ?? 0;
$notifications_query = "SELECT * FROM donor_notifications WHERE donor_id = $donor_id AND is_read = 0 ORDER BY notification_date DESC";
$notifications_result = $conn->query($notifications_query);
$unread_count = $notifications_result ? $notifications_result->num_rows : 0;
$receiver_sql = "SELECT br.*, rec.name AS receiver_name, rec.city, rec.phone as receiver_phone, 'Receiver' as request_type
        FROM blood_requests br
        JOIN receivers rec ON br.receiver_id = rec.id
        WHERE br.blood_group IN ('$placeholders')
        AND br.request_status='Pending'
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

// Sort by date
usort($all_requests, function($a, $b) {
    return strtotime($b['request_date']) - strtotime($a['request_date']);
});

// Function to get compatible blood types
function get_compatible_blood_types($blood_type) {
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
    
    return $compatibility[$blood_type] ?? [$blood_type];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - Emergency Alerts</title>
    <link rel="stylesheet" href="../main.css">
    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .donor-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-text h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .info-text p {
            margin: 5px 0;
            font-size: 16px;
        }
        
        .alert-section {
            margin-bottom: 40px;
        }
        
        .alert-section h3 {
            color: #c0392b;
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 3px solid #c0392b;
            padding-bottom: 10px;
        }
        
        .emergency-card {
            background: white;
            border-left: 5px solid #c0392b;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .emergency-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .emergency-card .hospital-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .emergency-card .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }
        
        .detail-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .detail-value {
            font-size: 16px;
            color: #333;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .blood-badge {
            display: inline-block;
            background: #c0392b;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .urgent-label {
            display: inline-block;
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .no-alerts {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        
        .cta-button {
            background: #c0392b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            transition: background 0.3s;
        }
        
        .cta-button:hover {
            background: #a93226;
        }
        
        .notification-banner {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .notification-banner .icon {
            font-size: 32px;
        }
        
        .notification-banner .content {
            flex: 1;
        }
        
        .notification-banner h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        
        .notification-banner p {
            margin: 0;
            font-size: 14px;
            opacity: 0.95;
        }
        
        .notification-banner .count {
            background: rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 16px;
            white-space: nowrap;
        }
        
        .notification-item {
            background: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body style="padding-top: 140px; background: #f8f9fa;">
    <?php include '../navbar.php'; ?>
    
    <div class="dashboard-container">
        <!-- Donor Info -->
        <div class="donor-info">
            <div class="info-text">
                <h2>Welcome, <?php echo htmlspecialchars($donor_name); ?>!</h2>
                <p>Your Blood Type: <strong><?php echo htmlspecialchars($blood_type); ?></strong></p>
                <p>Keep yourself updated on emergency blood requests that match your blood type.</p>
            </div>
        </div>
        
        <!-- Notification Banner -->
        <?php if (!empty($all_requests)): ?>
            <div class="notification-banner">
                <div class="icon">🔔</div>
                <div class="content">
                    <h3>You Can Help!</h3>
                    <p>There are urgent blood requests that match your blood type. Someone needs you right now!</p>
                </div>
                <div class="count"><?php echo count($all_requests); ?> Request<?php echo count($all_requests) !== 1 ? 's' : ''; ?></div>
            </div>
            
            <!-- Recent Unread Notifications -->
            <?php if ($unread_count > 0): ?>
                <div style="background: #e8f4f8; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2980b9;">
                    <strong style="color: #2980b9;">📬 You have <?php echo $unread_count; ?> new notification<?php echo $unread_count !== 1 ? 's' : ''; ?>:</strong>
                    <?php 
                    $notifications_result->data_seek(0);
                    while ($notif = $notifications_result->fetch_assoc()): 
                    ?>
                        <div class="notification-item">
                            ✅ <?php echo htmlspecialchars($notif['message']); ?> - 
                            <small style="color: #666;"><?php echo date('M d, H:i', strtotime($notif['notification_date'])); ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #c3e6cb; text-align: center;">
                <h3 style="margin-top: 0;">✓ All Clear</h3>
                <p>No emergency blood requests at the moment. We'll notify you when someone needs your blood type!</p>
            </div>
        <?php endif; ?>
        
        <!-- Emergency Alerts -->
        <div class="alert-section">
            <h3>🚨 Active Emergency Requests</h3>
            
            <?php
            if (!empty($all_requests)) {
                foreach($all_requests as $row) {
                    $is_urgent = time() - strtotime($row['request_date']) < 3600; // Less than 1 hour old
                    $request_name = ($row['request_type'] === 'Hospital') ? $row['hospital_name'] : $row['receiver_name'];
                    $contact_phone = ($row['request_type'] === 'Hospital') ? $row['hospital_phone'] : $row['receiver_phone'];
                    $badge_color = ($row['request_type'] === 'Receiver') ? '#2980b9' : '#c0392b';
                    ?>
                    <div class="emergency-card">
                        <div class="hospital-name">
                            <?php echo htmlspecialchars($request_name); ?>
                            <span style="font-size: 12px; background: <?php echo $badge_color; ?>; color: white; padding: 3px 8px; border-radius: 3px; margin-left: 10px;">
                                <?php echo $row['request_type']; ?>
                            </span>
                            <?php if($is_urgent): ?>
                                <span class="urgent-label">⚡ URGENT</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="details">
                            <div class="detail-item">
                                <div class="detail-label">Blood Type Needed</div>
                                <div class="detail-value"><span class="blood-badge"><?php echo htmlspecialchars($row['blood_group']); ?></span></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Quantity Required</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['quantity']); ?> units</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['city']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Contact Number</div>
                                <div class="detail-value"><?php echo htmlspecialchars($contact_phone ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Request Posted</div>
                                <div class="detail-value"><?php echo date('H:i, d M', strtotime($row['request_date'])); ?></div>
                            </div>
                        </div>
                        
                        <button class="cta-button" onclick="alert('Thank you for your interest! Please contact <?php echo addslashes($request_name); ?> directly at the provided number.')">
                            I Can Help
                        </button>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="no-alerts">
                    <h2>✓ No Emergency Requests Right Now</h2>
                    <p>Good news! There are currently no critical blood requests (from hospitals or receivers) matching your blood type. Keep checking back as new requests may appear.</p>
                    <p style="margin-top: 15px; color: #666;">Consider registering at a blood donation camp to help others in need.</p>
                </div>
                <?php
            }
            ?>
        </div>
        
        <!-- Info Box -->
        <div style="background: #e8f4f8; padding: 20px; border-radius: 8px; margin-top: 30px; border-left: 4px solid #667eea;">
            <h4 style="margin-top: 0; color: #333;">💡 How This Works</h4>
            <ul style="margin: 10px 0; color: #555; line-height: 1.8;">
                <li>We match emergency blood requests to your blood type automatically</li>
                <li>You can see hospitals that urgently need blood matching your type</li>
                <li>Click "I Can Help" to express your interest in donating</li>
                <li>The hospital will contact you with donation details</li>
            </ul>
        </div>
    </div>
</body>
</html>
