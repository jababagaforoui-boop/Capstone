<?php
session_start();
include 'includes/db.php';

/* SECURITY CHECK */
if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'client'){
    header("Location: ../home.php");
    exit();
}

$user = $_SESSION['user'];
$branch_id   = $user['branch_id'] ?? null;
$branch_name = $user['branch_name'] ?? 'N/A';
$user_name   = $user['username'] ?? 'User';

/* PRICES */
$price_big_tray   = 106;
$price_small_tray = 56;

/* FETCH INVENTORY */
$inventory = ['big_trays'=>0,'small_trays'=>0,'egg_pieces'=>0,'updated_at'=>date('Y-m-d H:i:s')];
if($branch_id){
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE branch_id=? LIMIT 1");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $inv = $stmt->get_result()->fetch_assoc();
    if($inv) $inventory = $inv;
}

/* FETCH TOTAL RETURNS */
$total_big_return = $total_small_return = $total_egg_return = 0;
if($branch_id){
    $stmt_ret = $conn->prepare("SELECT SUM(big_trays) as big, SUM(small_trays) as small, SUM(egg_pieces) as pieces FROM returns WHERE branch_id=?");
    $stmt_ret->bind_param("i",$branch_id);
    $stmt_ret->execute();
    $res_ret = $stmt_ret->get_result()->fetch_assoc();
    $total_big_return   = $res_ret['big'] ?? 0;
    $total_small_return = $res_ret['small'] ?? 0;
    $total_egg_return   = $res_ret['pieces'] ?? 0;
}

/* FETCH TOTAL SALES */
$total_big_sold = $total_small_sold = 0;
$total_income = 0;
if($branch_id){
    $stmt_sales = $conn->prepare("SELECT SUM(big_trays_sold) as big, SUM(small_trays_sold) as small FROM sales WHERE branch_id=?");
    $stmt_sales->bind_param("i",$branch_id);
    $stmt_sales->execute();
    $res_sales = $stmt_sales->get_result()->fetch_assoc();
    $total_big_sold   = $res_sales['big'] ?? 0;
    $total_small_sold = $res_sales['small'] ?? 0;
    $total_income = ($total_big_sold*$price_big_tray) + ($total_small_sold*$price_small_tray);
}

/* EGGS CALCULATION */
$big_tray_eggs   = 12;
$small_tray_eggs = 6;

$egg_remaining   = ($inventory['big_trays']*$big_tray_eggs) + ($inventory['small_trays']*$small_tray_eggs) + $inventory['egg_pieces'];
$egg_returned    = ($total_big_return*$big_tray_eggs) + ($total_small_return*$small_tray_eggs) + $total_egg_return;
$egg_sold_total  = ($total_big_sold*$big_tray_eggs) + ($total_small_sold*$small_tray_eggs);

/* LOW STOCK ALERTS */
$low_big_trays   = $inventory['big_trays'] <= 5;
$low_small_trays = $inventory['small_trays'] <= 5;

/* 7-DAY STOCK PROJECTION */
$avg_daily_big = $avg_daily_small = 0;
$projected_big = []; $projected_small = []; $days_labels = [];
if($branch_id){
    $stmt_last_sales = $conn->prepare("SELECT * FROM sales WHERE branch_id=? AND sale_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt_last_sales->bind_param("i",$branch_id);
    $stmt_last_sales->execute();
    $res_last = $stmt_last_sales->get_result();
    $total_big_month = 0; $total_small_month = 0;
    while($row = $res_last->fetch_assoc()){
        $total_big_month += $row['big_trays_sold'];
        $total_small_month += $row['small_trays_sold'];
    }
    $avg_daily_big   = intval($total_big_month / 30);
    $avg_daily_small = intval($total_small_month / 30);

    $current_big = $inventory['big_trays'];
    $current_small = $inventory['small_trays'];
    for($i=0;$i<7;$i++){
        $current_big = max(0, $current_big - $avg_daily_big);
        $current_small = max(0, $current_small - $avg_daily_small);
        $projected_big[] = $current_big;
        $projected_small[] = $current_small;
        $days_labels[] = "Day ".($i+1);
    }
}

/* TREND ICONS */
$trend_big   = $total_big_return>0 ? "⬇" : "⏺";
$trend_small = $total_small_return>0 ? "⬇" : "⏺";
$trend_eggs  = $total_egg_return>0 ? "⬇" : "⏺";
$trend_sold_eggs = $egg_sold_total>0 ? "⬆" : "⏺";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Dashboard - <?php echo htmlspecialchars($branch_name); ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
body{background:#f0fdf4;min-height:100vh;display:flex;}
.sidebar{width:220px;background:#38b000;color:#fff;height:100vh;position:fixed;left:0;top:0;display:flex;flex-direction:column;padding:20px;}
.sidebar h2{margin-bottom:40px;font-size:1.5em;text-align:center;}
.sidebar a{display:block;padding:12px 20px;margin-bottom:15px;background:#2d6a4f;border-radius:10px;color:#fff;text-decoration:none;font-weight:bold;}
.sidebar a:hover{background:#70d6ff;color:#000;}
.sidebar .logout{background:#d00000;margin-top:auto;}
.sidebar .logout:hover{background:#9d0208;}
.main-content{margin-left:220px;padding:30px;flex:1;}
.card{background:#fff;border-radius:15px;padding:25px;box-shadow:0 8px 20px rgba(0,0,0,0.12);margin-bottom:25px;}
.card h2{color:#2d6a4f;margin-bottom:15px;}
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:20px;}
.kpi-box{border-radius:15px;padding:20px;text-align:center;box-shadow:0 4px 10px rgba(0,0,0,0.08);}
.kpi-box h3{font-size:1.1em;margin-bottom:10px;}
.kpi-box p{font-size:1.8em;font-weight:bold;margin-top:5px;}
.kpi-green{background:#d1fae5;color:#2d6a4f;}
.kpi-yellow{background:#fff3cd;color:#856404;}
.kpi-red{background:#ffe5e5;color:#d00000;}
.alert-box{background:#ffe5e5;color:#d00000;padding:15px;border-radius:12px;font-weight:bold;margin-bottom:15px;}
canvas{max-width:100%;height:300px;}
</style>
</head>
<body>
<div class="sidebar">
<h2>Dashboard</h2>
<a href="dashboard.php">Home</a>
<a href="add_deliveries.php">Add Deliveries</a>
<a href="orders.php">Orders</a>
<a href="stocks.php">Stocks</a>
<a href="returns.php">Returns</a>
<a href="profile.php">Profile</a>
<a href="../home.php" class="logout">Logout</a>
</div>

<div class="main-content">

<!-- Client Info -->
<div class="card">
<h2>👤 Client Info</h2>
<p><strong>Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
<p><strong>Branch:</strong> <?php echo htmlspecialchars($branch_name); ?></p>
<p><strong>Last Inventory Update:</strong> <?php echo $inventory['updated_at']; ?></p>
</div>

<!-- KPI GRID -->
<div class="card">
<h2>📊 Key Metrics</h2>
<div class="kpi-grid">
<div class="kpi-box <?php echo $inventory['big_trays']>10?'kpi-green':($inventory['big_trays']>5?'kpi-yellow':'kpi-red'); ?>">
<h3>Big Trays Remaining <?php echo $trend_big; ?></h3><p><?php echo $inventory['big_trays']; ?></p></div>

<div class="kpi-box <?php echo $inventory['small_trays']>10?'kpi-green':($inventory['small_trays']>5?'kpi-yellow':'kpi-red'); ?>">
<h3>Small Trays Remaining <?php echo $trend_small; ?></h3><p><?php echo $inventory['small_trays']; ?></p></div>

<div class="kpi-box <?php echo $egg_remaining>50?'kpi-green':($egg_remaining>20?'kpi-yellow':'kpi-red'); ?>">
<h3>Egg Pieces Remaining</h3><p><?php echo $egg_remaining; ?></p></div>

<div class="kpi-box kpi-red">
<h3>Returned Eggs</h3><p><?php echo $egg_returned; ?></p></div>

<div class="kpi-box kpi-green">
<h3>Total Big Trays Sold</h3><p><?php echo $total_big_sold; ?></p></div>

<div class="kpi-box kpi-green">
<h3>Total Small Trays Sold</h3><p><?php echo $total_small_sold; ?></p></div>

<div class="kpi-box kpi-green">
<h3>Total Egg Pieces Sold</h3><p><?php echo $egg_sold_total; ?></p></div>

<div class="kpi-box kpi-green">
<h3>Total Income</h3><p>₱<?php echo number_format($total_income,2); ?></p></div>
</div>
</div>

<!-- Low Stock Alerts -->
<?php if($low_big_trays || $low_small_trays): ?>
<div class="card">
<h2>⚠ Low Stock Alerts</h2>
<?php if($low_big_trays): ?><div class="alert-box">Low Big Trays: Only <?php echo $inventory['big_trays']; ?> left!</div><?php endif; ?>
<?php if($low_small_trays): ?><div class="alert-box">Low Small Trays: Only <?php echo $inventory['small_trays']; ?> left!</div><?php endif; ?>
</div>
<?php endif; ?>

<!-- 7-Day Stock Projection -->
<div class="card">
<h2>📈 Stock Projection (Next 7 Days)</h2>
<canvas id="stockChart"></canvas>
<script>
const ctx = document.getElementById('stockChart').getContext('2d');
const stockChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($days_labels); ?>,
        datasets: [
            { label: 'Big Trays', data: <?php echo json_encode($projected_big); ?>, borderColor: '#2d6a4f', backgroundColor: 'rgba(45,106,79,0.2)', fill:true, tension:0.3 },
            { label: 'Small Trays', data: <?php echo json_encode($projected_small); ?>, borderColor:'#38b000', backgroundColor:'rgba(56,176,0,0.2)', fill:true, tension:0.3 }
        ]
    },
    options:{ responsive:true, plugins:{ legend:{position:'top'}, title:{display:true,text:'Projected Stock vs Days'} }, scales:{ y:{ beginAtZero:true } } }
});
</script>
</div>

</div>
</body>
</html>