<?php
session_start();

// Check if user is logged in (both users and hospitals allowed)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../homepage.php");
    exit();
}

$userRole = $_SESSION['user_role'];

// ------------------- Database Connection -------------------
$conn = new mysqli("localhost", "root", "", "blood_donor");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ------------------- Handle Donor Form Submission (Users only) -------------------
$message = "";
if(isset($_POST['register']) && $userRole === 'user') {
    $name = $_POST['name'];
    $city = $_POST['city'];

    // Encode city for URL
    $city_encoded = urlencode($city);
    $url = "https://nominatim.openstreetmap.org/search?q=$city_encoded&format=json&limit=1";

    // Add proper User-Agent to avoid 403
    $options = [
        "http" => [
            "header" => "User-Agent: SAS-Blood-Hackathon-App/1.0\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    $data = json_decode($response);

    if(!empty($data)) {
        $lat = $data[0]->lat;
        $lng = $data[0]->lon;

        $stmt = $conn->prepare("INSERT INTO donors1 (name, lat, lng) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $name, $lat, $lng);
        $stmt->execute();
        $stmt->close();

        $message = "✅ Donor registered at $city!";
    } else {
        $message = "⚠️ City not found. Please enter a valid city.";
    }
}

// ------------------- Fetch Donors -------------------
$donors = [];
$result = $conn->query("SELECT * FROM donors1");
while($row = $result->fetch_assoc()) {
    $donors[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hospital & Donor Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../main.css" /> 
    <style>
        body { padding-top: 80px; background: #fafafa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        #map { height: 500px; width: 90%; max-width: 1000px; margin: 30px auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1; }
        form { max-width: 500px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: flex; flex-direction: column; gap: 12px; }
        form input, form button { padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-size: 16px; }
        form button { background-color: #c0392b; color: #fff; border: none; cursor: pointer; transition: 0.3s; }
        form button:hover { background-color: #e74c3c; }
        .message { text-align: center; margin-bottom: 10px; font-weight: bold; }
        h2 { text-align: center; color: #c0392b; margin-top: 20px; }
        nav ul li a.active { background-color: #2980b9; color: #fff; border-radius: 6px; padding: 6px 12px; }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

<!-- ================= Header ================= -->
<h2>Hospital & Donor Map</h2>

<?php if($message) echo "<div class='message'>$message</div>"; ?>

<!-- ================= Donor Registration Form (Users Only) ================= -->
<?php if($userRole === 'user'): ?>
<form method="post">
    <input type="text" name="name" placeholder="Donor Name" required>
    <input type="text" name="city" placeholder="City (e.g., Banepa)" required>
    <button type="submit" name="register">Register Donor</button>
</form>
<?php endif; ?>

<!-- ================= Navbar ================= -->
 
<!-- ================= Map (Hospitals Only) ================= -->
<?php if($userRole === 'hospital'): ?>
<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map');

    // OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Hospital in Kathmandu// Hospitals
const hospitals = [
    { name: "SAS Hospital", lat: 27.7172, lng: 85.3240 }, // Kathmandu
    { name: "SAS Hospital", lat: 29.2980, lng: 80.5800 }, // Dadeldhura
    { name: "SAS Hospital", lat: 27.3545, lng: 87.6730 },  // Taplejung
    { name: "Scheer Memorial Hospital, Banepa", lat: 27.6334, lng: 85.5273 },
    { name: "Hetauda Hospital", lat: 27.43269, lng: 85.02795 }

];


    // Donors from PHP
    const donors = <?php echo json_encode($donors); ?>;

    const allMarkers = [];

    // Add hospital markers
    hospitals.forEach(h => {
        const marker = L.marker([h.lat, h.lng], {icon: L.icon({
            iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            iconSize: [32,32],
            iconAnchor: [16,32]
        })}).addTo(map).bindPopup(`<b>${h.name}</b><br>Hospital`);
        allMarkers.push(marker);
    });

    // Add donor markers
    donors.forEach(d => {
        const marker = L.marker([d.lat, d.lng], {icon: L.icon({
            iconUrl: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            iconSize: [32,32],
            iconAnchor: [16,32]
        })}).addTo(map).bindPopup(`<b>${d.name}</b><br>Donor`);
        allMarkers.push(marker);
    });

    // Fit map to all markers
    if(allMarkers.length > 0){
        const group = new L.featureGroup(allMarkers);
        map.fitBounds(group.getBounds().pad(0.2));
    } else {
        map.setView([27.7172, 85.3240], 7);
    }

    // ================= Mobile Menu Toggle =================
    const menuToggle = document.querySelector('.menu-toggle');
    const navUl = document.querySelector('nav ul');
    menuToggle.addEventListener('click', () => {
        navUl.classList.toggle('show');
    });
</script>
<?php endif; ?>

</body>
</html>
