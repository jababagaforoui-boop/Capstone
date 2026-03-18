<?php
session_start();
include 'includes/db.php';

// Security check
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
    header("Location: ../login.php");
    exit();
}

/* HANDLE REPLY TO REQUEST */
if(isset($_POST['reply_request'])){
    $request_id = (int)$_POST['request_id'];
    $reply_msg  = trim($_POST['reply_msg']);
    $status     = $_POST['status']; // 'confirmed' or 'rejected'

    if(!empty($reply_msg) && in_array($status, ['confirmed','rejected'])){
        $stmt = $conn->prepare("UPDATE requests SET admin_reply=?, status=? WHERE id=?");
        $stmt->bind_param("ssi", $reply_msg, $status, $request_id);
        $stmt->execute();
        $success_reply = "Reply sent successfully!";
    } else {
        $error_reply = "Please fill all fields and select a status.";
    }
}

/* FETCH ALL REQUESTS */
$result = $conn->query("
    SELECT r.*, b.branch_name 
    FROM requests r 
    JOIN branches b ON r.branch_id = b.id 
    ORDER BY r.request_datetime DESC
");
$requests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

/* COUNT REQUEST STATUS FOR SUMMARY CARDS */
$pending_count = $confirmed_count = $rejected_count = 0;
foreach($requests as $r){
    switch($r['status']){
        case 'pending': $pending_count++; break;
        case 'confirmed': $confirmed_count++; break;
        case 'rejected': $rejected_count++; break;
    }
}

/* FETCH RETURNS FOR ADMIN VIEW */
$returns = [];
$stmt_ret = $conn->prepare("
    SELECT ret.*, b.branch_name 
    FROM returns ret 
    JOIN branches b ON ret.branch_id = b.id
    ORDER BY ret.return_datetime DESC
    LIMIT 50
");
if($stmt_ret){
    $stmt_ret->execute();
    $res_ret = $stmt_ret->get_result();
    while($row=$res_ret->fetch_assoc()) $returns[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Reports</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Sidebar, cards, table styles (same as previous) */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
body{background:#e6f4ea;color:#2d6a4f;}
.wrapper{display:flex;min-height:100vh;}
.sidebar{width:240px;background:#38b000;color:#fff;padding:25px;display:flex;flex-direction:column;}
.sidebar h2{text-align:center;font-size:1.8rem;margin-bottom:30px;font-weight:700;}
.sidebar a{display:flex;align-items:center;gap:10px;padding:12px 18px;margin-bottom:10px;background:#2d6a4f;color:#fff;border-radius:10px;font-weight:600;text-decoration:none;transition:0.3s;}
.sidebar a i{width:20px;text-align:center;}
.sidebar a.active,.sidebar a:hover{background:#70d6ff;color:#000;}
.sidebar .logout{background:#d90429;margin-top:auto;}
.sidebar .logout:hover{background:#9b0a20;}
.main-content{flex:1;padding:30px;}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;}
.header h1{font-size:2.2rem;color:#2d6a4f;margin-bottom:5px;}
.header p{color:#52796f;font-size:1rem;margin:0;}
#darkToggle{padding:8px 15px;border:none;border-radius:6px;background:#334155;color:#fff;cursor:pointer;font-weight:600;transition:0.3s;}
#darkToggle:hover{background:#1e293b;}
.summary-cards{display:flex;gap:20px;margin-bottom:30px;flex-wrap:wrap;}
.summary-card{flex:1;padding:20px;border-radius:12px;color:#fff;font-weight:700;text-align:center;font-size:1.1rem;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.summary-card.pending{background:#fff8dc;color:#9f6b00;}
.summary-card.confirmed{background:#d4edda;color:#155724;}
.summary-card.rejected{background:#f8d7da;color:#721c24;}
.card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.1);margin-bottom:25px;}
.card h2{color:#2d6a4f;margin-bottom:20px;}
.success-msg{background:#d4edda;color:#155724;padding:12px;border-radius:8px;margin-bottom:20px;}
.error-msg{background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:20px;}
table{width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);}
th, td{padding:12px;text-align:center;font-size:0.95rem;border-bottom:1px solid #ddd;}
th{background:#38b000;color:#fff;font-weight:600;}
tr:nth-child(even){background:#f6fbf7;}
tr:hover{background:#e0f4e6;transition:0.2s;}
tr.pending{background:#fff8dc !important;}      
tr.confirmed{background:#d4edda !important;}    
tr.rejected{background:#f8d7da !important;}     
textarea{resize:none;width:100%;padding:8px;border-radius:6px;border:1px solid #ccc;margin-bottom:8px;font-size:0.9rem;}
form button{padding:8px 15px;background:#38b000;color:#fff;border:none;border-radius:6px;font-weight:600;font-size:0.9rem;cursor:pointer;transition:0.2s;}
form button:hover{background:#2d6a4f;}
</style>
</head>
<body>
<div class="wrapper">

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="branches.php"><i class="fas fa-store"></i> Branches</a>
    <a href="deliveries.php"><i class="fas fa-truck"></i> Deliveries</a>
    <a href="sales.php"><i class="fas fa-chart-line"></i> Sales Report</a>
    <a href="reports.php" class="active"><i class="fas fa-file-alt"></i> Reports</a>
    <a href="stocks.php"><i class="fas fa-boxes"></i> Stocks</a>
    <a href="users.php"><i class="fas fa-users"></i> Users</a>
    <a href="../home.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <div>
            <h1>Branch Requests & Returns</h1>
            <p>Track requests and returned eggs from branches</p>
        </div>
        <button id="darkToggle">🌙 Dark Mode</button>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card pending">Pending Requests: <?= $pending_count ?></div>
        <div class="summary-card confirmed">Confirmed Requests: <?= $confirmed_count ?></div>
        <div class="summary-card rejected">Rejected Requests: <?= $rejected_count ?></div>
    </div>

    <?php if(isset($success_reply)) echo "<div class='success-msg'>$success_reply</div>"; ?>
    <?php if(isset($error_reply)) echo "<div class='error-msg'>$error_reply</div>"; ?>

    <!-- Requests Table -->
    <div class="card">
        <h2>📋 Branch Requests</h2>
        <?php if($requests): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Branch</th>
                    <th>Big Trays</th>
                    <th>Small Trays</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Admin Reply</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($requests as $r): ?>
                <tr class="<?= $r['status']; ?>">
                    <td><?= $r['id']; ?></td>
                    <td><?= htmlspecialchars($r['branch_name']); ?></td>
                    <td><?= $r['big_trays']; ?></td>
                    <td><?= $r['small_trays']; ?></td>
                    <td><?= htmlspecialchars($r['message']); ?></td>
                    <td><?= ucfirst($r['status']); ?></td>
                    <td><?= htmlspecialchars($r['admin_reply']); ?></td>
                    <td>
                        <?php if($r['status']=='pending'): ?>
                        <form method="post">
                            <input type="hidden" name="request_id" value="<?= $r['id']; ?>">
                            <textarea name="reply_msg" placeholder="Reply to branch" required></textarea>
                            <select name="status">
                                <option value="confirmed">Confirm</option>
                                <option value="rejected">Reject</option>
                            </select>
                            <button type="submit" name="reply_request">Send</button>
                        </form>
                        <?php else: ?>
                            ✅ Replied
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No requests yet.</p>
        <?php endif; ?>
    </div>

    <!-- Returns Table -->
    <div class="card">
        <h2>🛑 Returned Eggs</h2>
        <?php if($returns): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Branch</th>
                    <th>Return Type</th>
                    <th>Big Trays</th>
                    <th>Small Trays</th>
                    <th>Egg Pieces</th>
                    <th>Remarks</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($returns as $r): ?>
                <tr>
                    <td><?= $r['id']; ?></td>
                    <td><?= htmlspecialchars($r['branch_name']); ?></td>
                    <td><?= htmlspecialchars($r['return_type']); ?></td>
                    <td><?= $r['big_trays']; ?></td>
                    <td><?= $r['small_trays']; ?></td>
                    <td><?= $r['egg_pieces']; ?></td>
                    <td><?= htmlspecialchars($r['remarks']); ?></td>
                    <td><?= $r['return_datetime']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No returns recorded yet.</p>
        <?php endif; ?>
    </div>

</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const body = document.body;
    const darkToggle = document.getElementById("darkToggle");

    // Load saved mode
    if(localStorage.getItem("darkMode") === "enabled") {
        body.classList.add("dark");
        darkToggle.textContent = "☀️ Light Mode";
    }

    darkToggle.addEventListener("click", () => {
        body.classList.toggle("dark");
        if(body.classList.contains("dark")){
            localStorage.setItem("darkMode","enabled");
            darkToggle.textContent = "☀️ Light Mode";
        } else {
            localStorage.setItem("darkMode","disabled");
            darkToggle.textContent = "🌙 Dark Mode";
        }
    });
});
</script>
</body>
</html>