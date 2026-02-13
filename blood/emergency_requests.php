<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

// Fetch all pending requests
$sql = "SELECT r.*, h.name AS hospital_name 
        FROM requests r 
        JOIN hospitals h ON r.hospital_id = h.id
        WHERE r.status='Pending'
        ORDER BY r.request_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Emergency Blood Requests</title>
<link rel="stylesheet" href="../main.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

<h2 style="text-align:center;margin-top:30px;">Emergency Blood Requests</h2>

<?php
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Hospital</th>
                <th>Blood Group</th>
                <th>Quantity</th>
                <th>City</th>
                <th>Request Date</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        // Highlight rare blood groups
        $bg_color = in_array($row['blood_group'], ['AB-', 'O-']) ? 'style="background-color:#f9d6d5;"' : '';
        echo "<tr $bg_color>
                <td>".$row['hospital_name']."</td>
                <td>".$row['blood_group']."</td>
                <td>".$row['quantity']."</td>
                <td>".$row['city']."</td>
                <td>".$row['request_date']."</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;color:#c0392b;font-weight:bold;'>No pending emergency requests.</p>";
}
?>
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
