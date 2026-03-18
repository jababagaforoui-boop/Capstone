<?php
session_start();

// Include database connection (correct path)
include 'includes/db.php';

// Protect page - admin only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all sales with branch name
$sales_query = "
    SELECT s.id, s.branch_id, s.big_trays_sold, s.small_trays_sold, s.egg_pieces_sold, s.total_amount, s.sale_datetime,
           b.branch_name
    FROM sales s
    LEFT JOIN branches b ON s.branch_id = b.id
    ORDER BY s.sale_datetime ASC
";
$result = $conn->query($sales_query);
$sales = [];
if ($result) {
    while($row = $result->fetch_assoc()) $sales[] = $row;
}

// Prepare data for charts
$branch_totals = [];
$daily_totals = [];
$branch_trays = [];

$total_big_trays = 0;
$total_small_trays = 0;
$total_eggs = 0;
$total_sales_amount = 0;

foreach($sales as $s) {
    $branch = $s['branch_name'] ?? 'Unknown';

    if(!isset($branch_totals[$branch])) $branch_totals[$branch] = 0;
    $branch_totals[$branch] += $s['total_amount'];

    $date = date("Y-m-d", strtotime($s['sale_datetime']));
    if(!isset($daily_totals[$date])) $daily_totals[$date] = 0;
    $daily_totals[$date] += $s['total_amount'];

    if(!isset($branch_trays[$branch])) $branch_trays[$branch] = ['big'=>0,'small'=>0];
    $branch_trays[$branch]['big'] += $s['big_trays_sold'];
    $branch_trays[$branch]['small'] += $s['small_trays_sold'];

    $total_big_trays += $s['big_trays_sold'];
    $total_small_trays += $s['small_trays_sold'];
    $total_eggs += $s['egg_pieces_sold'];
    $total_sales_amount += $s['total_amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report - Admin Panel</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{background:#fff;color:#2d6a4f;font-family:'Segoe UI',Tahoma,Verdana;margin:0;}
.wrapper{display:flex;min-height:100vh;}
.sidebar{width:240px;background:#38b000;color:#fff;padding:25px;display:flex;flex-direction:column;}
.sidebar h2{text-align:center;font-size:1.8rem;margin-bottom:30px;font-weight:700;}
.sidebar a{display:flex;align-items:center;gap:10px;padding:12px 18px;margin-bottom:10px;background:#2d6a4f;color:#fff;border-radius:10px;font-weight:600;text-decoration:none;transition:0.3s;text-align:left;}
.sidebar a i{width:20px;text-align:center;}
.sidebar a.active,.sidebar a:hover{background:#70d6ff;color:#000;}
.sidebar .logout{background:#d90429;margin-top:auto;}
.sidebar .logout:hover{background:#9b0a20;}
.main-content{flex:1;padding:30px;}

/* Header with Dark Mode button aligned right */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}
.header h1{font-size:2.2rem;color:#2d6a4f;margin:0;}
.header p{color:#52796f;font-size:1rem;margin:0;}
#darkToggle{
    padding:8px 15px;border:none;border-radius:6px;background:#334155;color:#fff;cursor:pointer;font-weight:600;transition:0.3s;
}
#darkToggle:hover{background:#1e293b;}

/* Summary Cards */
.cards-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:30px;}
.card-summary{background:#fff;padding:20px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.08);text-align:center;transition:0.3s;}
.card-summary:hover{transform:translateY(-5px);}
.card-summary h3{font-size:1rem;color:#52796f;margin-bottom:10px;}
.card-summary p{font-size:1.5rem;font-weight:700;color:#2d6a4f;margin:0;}

/* Card & Table */
.card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.08);margin-bottom:25px;}
table{width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);}
th,td{padding:12px;text-align:center;font-size:0.95rem;border-bottom:1px solid #ddd;}
th{background:#38b000;color:#fff;font-weight:600;}
tr:nth-child(even){background:#f6fbf7;}
tr:hover{background:#e0f4e6;transition:0.2s;}

/* Responsive */
@media(max-width:768px){
    .sidebar{width:100%;flex-direction:row;overflow-x:auto;height:auto;padding:15px;}
    .sidebar a{margin-right:8px;margin-bottom:0;}
    .main-content{padding:20px;}
    .cards-container{grid-template-columns:repeat(auto-fit,minmax(140px,1fr));}
    .header{flex-direction:column;align-items:flex-start;}
    #darkToggle{margin-top:10px;}
}

/* Dark Mode */
body.dark{background:#121821;color:#e0e0e0;}
body.dark .main-content, body.dark .card, body.dark .card-summary, body.dark table{background-color:#1e293b;color:#e0e0e0;}
body.dark .sidebar{background-color:#0f172a;}
body.dark .sidebar a{color:#e0e0e0;}
body.dark .sidebar a.active, body.dark .sidebar a:hover{background-color:#2563eb;color:#fff;}
body.dark th{background-color:#1f2937;color:#e0e0e0;}
body.dark tr:nth-child(even){background-color:#1e293b;}
body.dark tr:hover{background-color:#334155;}
body.dark #darkToggle{background:#334155;color:#fff;}
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
    <a href="sales.php" class="active"><i class="fas fa-chart-line"></i> Sales Report</a>
    <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
    <a href="stocks.php"><i class="fas fa-boxes"></i> Stocks</a>
    <a href="users.php"><i class="fas fa-users"></i> Users</a>
    <a href="../home.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <!-- Header -->
    <div class="header">
        <div>
            <h1>Sales Report</h1>
            <p>Overview of all sales transactions, totals, and analytics.</p>
        </div>
        <button id="darkToggle">🌙 Dark Mode</button>
    </div>

    <!-- Summary Cards -->
    <div class="cards-container">
        <div class="card-summary">
            <h3>Total Sales (₱)</h3>
            <p><?= number_format($total_sales_amount,2) ?></p>
        </div>
        <div class="card-summary">
            <h3>Total Big Trays Sold</h3>
            <p><?= $total_big_trays ?></p>
        </div>
        <div class="card-summary">
            <h3>Total Small Trays Sold</h3>
            <p><?= $total_small_trays ?></p>
        </div>
        <div class="card-summary">
            <h3>Total Eggs Sold</h3>
            <p><?= $total_eggs ?></p>
        </div>
    </div>

    <!-- Charts -->
    <div class="card">
        <h2>Total Sales per Branch</h2>
        <canvas id="branchChart" style="width:100%;max-width:700px"></canvas>
    </div>
    <div class="card">
        <h2>Daily Sales Overview</h2>
        <canvas id="dailyChart" style="width:100%;max-width:700px"></canvas>
    </div>
    <div class="card">
        <h2>Big vs Small Trays Sold per Branch</h2>
        <canvas id="traysChart" style="width:100%;max-width:700px"></canvas>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <h2>Sales Records</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Branch</th>
                <th>Big Trays Sold</th>
                <th>Small Trays Sold</th>
                <th>Total Eggs Sold</th>
                <th>Total Amount (₱)</th>
                <th>Date & Time</th>
            </tr>
            <?php if(!empty($sales)): foreach($sales as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['branch_name'] ?? 'No Branch') ?></td>
                <td><?= $row['big_trays_sold'] ?></td>
                <td><?= $row['small_trays_sold'] ?></td>
                <td><?= $row['egg_pieces_sold'] ?></td>
                <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                <td><?= date("Y-m-d H:i", strtotime($row['sale_datetime'])) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7">No sales records found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>
</div>

<script>
// Charts
new Chart(document.getElementById("branchChart").getContext('2d'), {
    type: 'bar',
    data: { labels: <?= json_encode(array_keys($branch_totals)) ?>, datasets:[{label:'Total Sales (₱)',data:<?= json_encode(array_values($branch_totals)) ?>,backgroundColor:'rgb(236, 243, 243)'}] },
    options:{responsive:true,legend:{display:false},title:{display:true,text:'Total Sales by Branch'},scales:{yAxes:[{ticks:{beginAtZero:true}}]}}
});
new Chart(document.getElementById("dailyChart").getContext('2d'), {
    type: 'line',
    data:{labels:<?= json_encode(array_keys($daily_totals)) ?>,datasets:[{label:'Daily Sales (₱)',data:<?= json_encode(array_values($daily_totals)) ?>,backgroundColor:'rgb(239, 241, 241)',borderColor:'rgb(231, 235, 235)',fill:true,lineTension:0}]},
    options:{responsive:true,legend:{display:false},title:{display:true,text:'Daily Sales Over Time'},scales:{yAxes:[{ticks:{beginAtZero:true}}]}}
});
new Chart(document.getElementById("traysChart").getContext('2d'), {
    type: 'bar',
    data:{labels:<?= json_encode(array_keys($branch_trays)) ?>,datasets:[{label:'Big Trays Sold',data:<?= json_encode(array_map(fn($b)=>$b['big'],$branch_trays)) ?>,backgroundColor:'rgb(238, 229, 231)'},{label:'Small Trays Sold',data:<?= json_encode(array_map(fn($b)=>$b['small'],$branch_trays)) ?>,backgroundColor:'rgb(231, 234, 236)'}]},
    options:{responsive:true,title:{display:true,text:'Big vs Small Trays Sold per Branch'},scales:{xAxes:[{stacked:true}],yAxes:[{stacked:true,ticks:{beginAtZero:true}}]}}
});

// Dark Mode
const body = document.body;
const darkToggle = document.getElementById("darkToggle");
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
</script>

</body>
</html>
