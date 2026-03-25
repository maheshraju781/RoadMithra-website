<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php?role=customer"); exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .service-category-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            border: 1px solid #F0F2F5;
        }
        .service-category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border-color: var(--primary-color);
        }
        .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 25px auto;
        }

    </style>
</head>
<body style="background: #FFF5F8;">

    <div class="web-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="../assets/images/logo.png" alt="Logo">
                <h1>Road Mithra</h1>
            </div>
            
            <nav>
                <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-compass"></i> Explore Services</a>
                <a href="bookings.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
                <a href="cart.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Shopping Cart</a>
                <a href="profile.php" class="nav-link"><i class="fa-solid fa-user-gear"></i> Account Settings</a>
                <div style="margin-top: 50px;">
                    <a href="../auth/logout.php" class="nav-link" style="color: var(--danger);"><i class="fa-solid fa-power-off"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-navbar">
                <div>
                    <h2 style="font-size: 24px; font-weight: 800;">Hello, <?php echo $user['name']; ?>!</h2>
                    <p style="color: var(--text-light); font-size: 14px;">Find assistance nearby in seconds.</p>
                </div>
                <div class="user-profile">
                    <div class="user-img"><?php echo substr($user['name'], 0, 1); ?></div>
                    <span style="font-weight: 600; font-size: 14px;"><?php echo $user['name']; ?></span>
                </div>
            </header>


            <!-- Categories -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 50px;">
                <a href="book_mechanic.php" class="service-category-card">
                    <div class="icon-circle" style="background: #E3F2FD; color: #2196F3;"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                    <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 10px;">Book a Mechanic</h3>
                    <p style="color: var(--text-light); font-size: 14px;">Instant service for breakdowns, punctures & repairs.</p>
                </a>
                
                <a href="buy_spares.php" class="service-category-card">
                    <div class="icon-circle" style="background: #E8F5E9; color: #4CAF50;"><i class="fa-solid fa-gears"></i></div>
                    <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 10px;">Buy Spare Parts</h3>
                    <p style="color: var(--text-light); font-size: 14px;">Genuine parts delivered to your breakdown location.</p>
                </a>

                <a href="quick_problems.php" class="service-category-card">
                    <div class="icon-circle" style="background: #FFF3E0; color: #FF9800;"><i class="fa-solid fa-bolt"></i></div>
                    <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 10px;">Common Problems</h3>
                    <p style="color: var(--text-light); font-size: 14px;">Battery jumpstart, refueling & flat tire assist.</p>
                </a>
            </div>

            <div class="card">
                <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 25px;">Recent Activity</h3>
                <div style="text-align: center; padding: 40px; color: #AAA;">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 40px; margin-bottom: 15px;"></i>
                    <p>You haven't made any bookings yet.</p>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
