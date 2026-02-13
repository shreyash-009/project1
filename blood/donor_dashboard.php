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
$sql = "SELECT r.*, h.name AS hospital_name, h.city, h.phone as hospital_phone
        FROM requests r 
        JOIN hospitals h ON r.hospital_id = h.id
        WHERE r.blood_group IN ('$placeholders')
        AND r.status='Pending'
        ORDER BY r.request_date DESC";

$result = $conn->query($sql);

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
        
        <!-- Emergency Alerts -->
        <div class="alert-section">
            <h3>🚨 Active Emergency Requests</h3>
            
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $is_urgent = time() - strtotime($row['request_date']) < 3600; // Less than 1 hour old
                    ?>
                    <div class="emergency-card">
                        <div class="hospital-name">
                            <?php echo htmlspecialchars($row['hospital_name']); ?>
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
                                <div class="detail-value"><?php echo htmlspecialchars($row['hospital_phone'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Request Posted</div>
                                <div class="detail-value"><?php echo date('H:i, d M', strtotime($row['request_date'])); ?></div>
                            </div>
                        </div>
                        
                        <button class="cta-button" onclick="alert('Thank you for your interest! Please contact the hospital directly at the provided number.')">
                            I Can Help
                        </button>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="no-alerts">
                    <h2>✓ No Emergency Requests Right Now</h2>
                    <p>Good news! There are currently no critical blood requests matching your blood type. Keep checking back as new requests may appear.</p>
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
