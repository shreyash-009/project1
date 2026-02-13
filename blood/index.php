<?php
session_start();

// Check if user is logged in and is a donor (blood donation is for donors, not hospitals)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'donor') {
    header("Location: ../homepage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SAS – Blood Donation</title>
<link rel="stylesheet" href="../main.css">
</head>

<body style="
    margin:0;
    font-family:'Segoe UI', Tahoma, sans-serif;
    padding-top:120px;
    background: linear-gradient(180deg, #f8fbff, #ffffff);
">


<!-- ================= SAS MAIN NAVBAR ================= -->
<?php include '../navbar.php'; ?>

<!-- ================= HERO SECTION ================= -->
<header id="home" style="
    text-align: center;
    padding: 90px 20px 60px;
    background: linear-gradient(135deg, #e3f2fd, #ffffff);
">
    <h1 style="
        font-size: 44px;
        color: #1f3c88;
        margin-bottom: 15px;
        letter-spacing: 0.5px;
    ">
        Save Lives. Donate Blood.
    </h1>
    <p style="
        font-size: 18px;
        color: #555;
        max-width: 650px;
        margin: auto;
        line-height: 1.7;
    ">
        Join the Blood Donor Portal to make a difference today!
    </p>
</header>

<!-- ================= FEATURES SECTION ================= -->
<section class="features" style="
    display: flex;
    gap: 25px;
    padding: 60px 6%;
    justify-content: center;
    flex-wrap: wrap;
">

    <div class="feature-card" style="
        background: white;
        padding: 30px;
        border-radius: 16px;
        width: 280px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        transition: transform 0.3s ease;
        text-align: center;
    ">
        <h2 style="color:#c0392b; margin-bottom:10px;">Donate Blood</h2>
        <p style="color:#555; line-height:1.6;">
            Register as a donor and help those in need with just a few clicks.
        </p>
    </div>

    <div class="feature-card" style="
        background: white;
        padding: 30px;
        border-radius: 16px;
        width: 280px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        text-align: center;
    ">
        <h2 style="color:#c0392b; margin-bottom:10px;">Hospitals</h2>
        <p style="color:#555; line-height:1.6;">
            Hospitals can manage blood requests efficiently and instantly.
        </p>
    </div>

    <div class="feature-card" style="
        background: white;
        padding: 30px;
        border-radius: 16px;
        width: 280px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        text-align: center;
    ">
        <h2 style="color:#c0392b; margin-bottom:10px;">Find Donors</h2>
        <p style="color:#555; line-height:1.6;">
            Search donors by blood group and city to save lives quickly.
        </p>
    </div>

</section>

</body>
</html>
