<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php?role=customer"); exit;
}
$user = $_SESSION['user'];
$cid = $user['id'];

// Fetch combined bookings with existing user ratings and comments
$bookings = $conn->query("SELECT b.*, r.id as review_id, r.rating as current_rating, r.comment as current_comment FROM (
                            SELECT id, problem_type as title, status, amount, created_at, 'mechanic' as type, COALESCE(NULLIF(shop_id, 0), worker_id, 0) as shop_id 
                            FROM mechanic_bookings WHERE customer_id=$cid 
                            UNION 
                            SELECT id, 'Spare Parts Order' as title, status, total_amount as amount, created_at, 'order' as type, shop_id 
                            FROM spare_parts_orders WHERE customer_id=$cid 
                         ) as b
                         LEFT JOIN reviews r ON r.customer_id = $cid AND r.target_id = b.shop_id AND r.target_type = (CASE WHEN b.type='mechanic' THEN 'mechanic' ELSE 'spare_part' END)
                         ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
</head>
<body>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
            <div class="sidebar-section-label">Main Menu</div>
            <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="buy_spares.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Shop Parts</a>
            <a href="bookings.php" class="nav-link active"><i class="fa-solid fa-calendar-check"></i> My Orders</a>
            <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
        </aside>

        <main class="main-area">
            <header class="top-bar">
                <div class="top-bar-title"><h1>Order History</h1><p>Manage your mechanic bookings and part purchases</p></div>
            </header>

            <div class="page-content">
                <div class="card">
                    <div class="card-header">
                        <h3>All Bookings & Orders</h3>
                        <div class="toggle-wrap">
                            <span style="font-size:12px; color:var(--text-light);">Latest first</span>
                        </div>
                    </div>

                    <?php if($bookings && $bookings->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Type & Service</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight:700;">#<?php echo $row['id']; ?></td>
                                    <td>
                                        <div style="font-weight:600; color:var(--text-dark);"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <div style="font-size:11px; display:flex; align-items:center; gap:5px;">
                                            <?php if($row['type'] == 'mechanic'): ?>
                                                <i class="fa-solid fa-wrench" style="color:var(--primary);"></i>
                                                <span style="color:var(--primary); font-weight:600; text-transform:uppercase;">Mechanic Visit</span>
                                            <?php else: ?>
                                                <i class="fa-solid fa-bag-shopping" style="color:var(--secondary);"></i>
                                                <span style="color:var(--secondary); font-weight:600; text-transform:uppercase;">Spare Parts</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size:14px; font-weight:500;"><?php echo date('d M Y', strtotime($row['created_at'])); ?></div>
                                        <div style="font-size:11px; color:var(--text-light);"><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                                    </td>
                                    <td style="font-weight:800; color:var(--text-dark);">₹<?php echo number_format($row['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo in_array($row['status'], ['completed', 'delivered']) ? 'badge-green' : (in_array($row['status'], ['pending', 'assigned']) ? 'badge-orange' : 'badge-blue'); 
                                        ?>">
                                            <?php echo strtoupper($row['status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php if($row['status'] == 'completed' || $row['status'] == 'delivered'): ?>
                                            <?php if($row['shop_id'] > 0): ?>
                                                <?php $btnTxt = $row['review_id'] ? 'Update Rate' : 'Rate'; ?>
                                                <button class="btn btn-sm" style="background:#5B21B6; color:#fff; border-radius:10px; padding:6px 15px;" onclick="openRatingModal(<?php echo $row['shop_id']; ?>, '<?php echo $row['type']=='mechanic'?'mechanic':'spare_part'; ?>', '<?php echo addslashes($row['title']); ?>', <?php echo (int)($row['current_rating']??0); ?>, '<?php echo addslashes($row['current_comment']??''); ?>')"><i class="fa-solid fa-star" style="color:#F59E0B; margin-right:5px;"></i> <?php echo $btnTxt; ?></button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if($row['type'] == 'mechanic'): ?>
                                            <a href="book_mechanic.php?booking_id=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm" style="border-radius:10px; margin-left:5px;">Track Info</a>
                                        <?php else: ?>
                                            <a href="view_order.php?id=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm" style="border-radius:10px; margin-left:5px;">Invoice</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 100px;">
                        <i class="fa-solid fa-receipt" style="font-size:60px; color:#ddd; margin-bottom:20px;"></i>
                        <h3>Your history is clean</h3>
                        <p style="color:var(--text-light);">You haven't placed any orders or requested a mechanic yet.</p>
                        <a href="dashboard.php" class="btn btn-primary" style="margin-top: 30px;">Start Exploring</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="ratingModal" class="modal-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); display:none; align-items:center; justify-content:center; z-index:9999;">
        <div class="card" style="width:90%; max-width:380px; text-align:center; padding:30px; border-radius:20px; box-shadow:0 20px 40px rgba(0,0,0,0.2); background:#fff; border:none;">
            <h2 style="font-size:24px; font-weight:700; color:#1F2937; margin-bottom:5px;">Rate this Service</h2>
            <p id="modalTitle" style="color:#6B7280; font-size:16px; margin-bottom:25px;">Service Name</p>
            
            <div id="starContainer" style="font-size:48px; color:#D1D5DB; margin-bottom:25px; display:flex; gap:8px; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-star" data-val="1"></i>
                <i class="fa-solid fa-star" data-val="2"></i>
                <i class="fa-solid fa-star" data-val="3"></i>
                <i class="fa-solid fa-star" data-val="4"></i>
                <i class="fa-solid fa-star" data-val="5"></i>
            </div>

            <textarea id="reviewComment" class="form-input" style="width:100%; margin-bottom:25px; border-radius:15px; background:#F9FAFB; border:1px solid #E5E7EB; padding:15px;" rows="4" placeholder="Write a comment (optional)"></textarea>
            
            <div style="display:flex; flex-direction:column; gap:12px;">
                <button class="btn" style="background:#6D28D9; color:#fff; border-radius:15px; padding:15px; font-weight:700; width:100%; font-size:18px;" onclick="submitRating()">Submit Review</button>
                <button style="background:none; border:none; color:#9CA3AF; cursor:pointer; font-weight:600;" onclick="closeRatingModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let currentRating = 0;
        let ratingData = { id: 0, type: '' };

        function openRatingModal(tid, type, title, existingRating = 0, existingComment = '') {
            ratingData = { id: tid, type: type };
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('ratingModal').style.display = 'flex';
            
            // Pre-fill existing rating data
            currentRating = existingRating;
            highlightStars(currentRating);
            document.getElementById('reviewComment').value = existingComment;
        }

        function closeRatingModal() {
            document.getElementById('ratingModal').style.display = 'none';
        }

        const stars = document.querySelectorAll('#starContainer i');
        stars.forEach(s => {
            s.addEventListener('mouseover', () => highlightStars(s.dataset.val));
            s.addEventListener('click', () => {
                currentRating = s.dataset.val;
                highlightStars(currentRating);
            });
        });

        document.getElementById('starContainer').addEventListener('mouseleave', () => {
            highlightStars(currentRating);
        });

        function highlightStars(val) {
            stars.forEach(s => {
                s.style.color = (s.dataset.val <= val) ? '#F59E0B' : '#E2E8F0';
            });
        }

        function resetStars() {
            currentRating = 0;
            highlightStars(0);
            document.getElementById('reviewComment').value = '';
        }

        async function submitRating() {
            if(currentRating == 0) { alert("Please select a star rating"); return; }
            
            const formData = new FormData();
            formData.append('customer_id', <?php echo $cid; ?>);
            formData.append('target_id', ratingData.id);
            formData.append('target_type', ratingData.type);
            formData.append('rating', currentRating);
            formData.append('comment', document.getElementById('reviewComment').value);

            try {
                const res = await fetch(apiUrl('road_backend/customer/submit_review.php'), {
                    method: 'POST', body: formData
                });
                
                // Read as text first to avoid JSON parse errors
                const text = await res.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch(e) {
                    console.error("API Response was not JSON", text);
                    alert("Server Error: " + text.substring(0, 100));
                    return;
                }

                if(result.success) {
                    alert(result.message);
                    closeRatingModal();
                    window.location.reload(); // Refresh to update "Update Rate" button status
                } else {
                    alert("Submission Failed: " + result.message);
                }
            } catch(e) { 
                console.error("Network or script error", e);
                alert("Network error. Please try again later."); 
            }
        }
    </script>
</body>
</html>
