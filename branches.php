<?php
session_start();
include 'includes/db.php';

/* ===== AUTO LOGIN (LOCAL TESTING) ===== */
if(!isset($_SESSION['admin'])){
    $_SESSION['admin'] = 1;
}

/* ===== AJAX VIEW DELIVERIES ===== */
if(isset($_GET['ajax_branch'])){
    $id = (int)$_GET['ajax_branch'];

    $branch = $conn->query("SELECT * FROM branches WHERE id=$id")->fetch_assoc();
    if(!$branch){ exit; }

    $deliveries = [];
    $summary = ['big'=>0,'small'=>0,'eggs'=>0];

    $res = $conn->query("SELECT * FROM deliveries WHERE branch_id=$id ORDER BY delivery_datetime DESC");
    while($d = $res->fetch_assoc()){
        $deliveries[] = $d;
        $summary['big'] += $d['big_trays'];
        $summary['small'] += $d['small_trays'];
        $summary['eggs'] += ($d['big_trays']*12)+($d['small_trays']*6); // Corrected
    }

    echo json_encode([
        'branch'=>$branch,
        'summary'=>$summary,
        'deliveries'=>$deliveries
    ]);
    exit;
}

/* ===== PREDEFINED BRANCHES ===== */
$predefined_branches = [
    'Iloilo Supermart Villa',
    'Iloilo Supermart Molo',
    'Iloilo Supermart Atrium',
    'Iloilo Supermart GQ',
    'Iloilo Supermart Washington'
];

/* ===== DASHBOARD COUNTS ===== */
$total_deliveries = $conn->query("SELECT COUNT(*) t FROM deliveries")->fetch_assoc()['t'];
$total_eggs = $conn->query("SELECT SUM(big_trays*12 + small_trays*6) t FROM deliveries")->fetch_assoc()['t']; // Corrected
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Branches | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
body{margin:0;background:#e6f4ea;color:#2d6a4f;transition:0.3s;}
.wrapper{display:flex;min-height:100vh}

/* ===== SIDEBAR ===== */
.sidebar{
    width:240px;
    background:#38b000;
    color:#fff;
    padding:25px;
    display:flex;
    flex-direction:column;
}
.sidebar h2{text-align:center;margin-bottom:25px}
.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 18px;
    margin-bottom:10px;
    background:#2d6a4f;
    color:#fff;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}
.sidebar a i{width:20px;text-align:center}
.sidebar a:hover,.sidebar a.active{background:#70d6ff;color:#000}
.sidebar .logout{margin-top:auto;background:#d90429}
.sidebar .logout:hover{background:#9b0a20}

/* ===== MAIN ===== */
.main-content{flex:1;padding:30px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.header button{
    padding:6px 14px;
    border:none;
    border-radius:8px;
    background:#334155;
    color:#fff;
    cursor:pointer;
}

/* ===== DASHBOARD ===== */
.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:25px;
}
.dashboard-card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}
.dashboard-card i{
    font-size:2.5rem;
    color:#38b000;
    margin-bottom:10px;
}

/* ===== TABLE ===== */
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}
th,td{padding:12px;text-align:center}
th{background:#38b000;color:#fff}
tr:nth-child(even){background:#f6fbf7}
.view-btn{
    background:#38b000;
    color:#fff;
    border:none;
    padding:6px 14px;
    border-radius:8px;
    cursor:pointer;
}

/* ===== MODAL ===== */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.55);
    justify-content:center;
    align-items:center;
    z-index:1000;
}
.modal-content{
    background:#fff;
    width:90%;
    max-width:900px;
    max-height:85vh;
    overflow-y:auto;
    border-radius:15px;
    padding:25px;
    position:relative;
}
.close-btn{
    position:absolute;
    top:15px;
    right:20px;
    font-size:26px;
    cursor:pointer;
    color:#d90429;
}

/* ===== SUMMARY ===== */
.summary-box{
    display:flex;
    gap:15px;
    justify-content:center;
    flex-wrap:wrap;
    margin:20px 0;
}
.summary-card{
    background:#f6fbf7;
    padding:15px;
    border-radius:12px;
    min-width:160px;
    text-align:center;
}

/* ===== DARK MODE ===== */
body.dark{background:#1e293b;color:#f1f5f9}
body.dark table,
body.dark th,
body.dark td{background:#334155;color:#f1f5f9}
body.dark .dashboard-card,
body.dark .modal-content{background:#334155}
</style>
</head>

<body>
<div class="wrapper">

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="branches.php" class="active"><i class="fas fa-store"></i> Branches</a>
    <a href="deliveries.php"><i class="fas fa-truck"></i> Deliveries</a>
    <a href="sales.php"><i class="fas fa-chart-line"></i> Sales Report</a>
    <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
    <a href="stocks.php"><i class="fas fa-boxes"></i> Stocks</a>
    <a href="users.php"><i class="fas fa-users"></i> Users</a>
    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- ===== MAIN ===== -->
<div class="main-content">
<div class="header">
    <div>
        <h1>Branches Management</h1>
        <p>Click “View Deliveries” to see branch details</p>
    </div>
    <button id="darkToggle">🌙 Dark Mode</button>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card"><i class="fas fa-store"></i><h2><?=count($predefined_branches)?></h2><p>Total Branches</p></div>
    <div class="dashboard-card"><i class="fas fa-truck"></i><h2><?=$total_deliveries?></h2><p>Total Deliveries</p></div>
    <div class="dashboard-card"><i class="fas fa-egg"></i><h2><?=$total_eggs?></h2><p>Total Eggs</p></div>
</div>

<table>
<tr><th>#</th><th>Branch Name</th><th>Action</th></tr>
<?php $i=1; foreach($predefined_branches as $b):
$r=$conn->query("SELECT id FROM branches WHERE branch_name='".$conn->real_escape_string($b)."'")->fetch_assoc();
if(!$r) continue; ?>
<tr>
<td><?=$i++?></td>
<td><?=htmlspecialchars($b)?></td>
<td>
<button class="view-btn" onclick="openDeliveries(<?=$r['id']?>)">
<i class="fas fa-eye"></i> View Deliveries
</button>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<!-- ===== MODAL ===== -->
<div id="deliveriesModal" class="modal">
<div class="modal-content">
<span class="close-btn" onclick="closeModal()">&times;</span>
<h2 id="modalBranchName"></h2>
<div class="summary-box" id="modalSummary"></div>
<div id="modalTable"></div>
</div>
</div>

<script>
const body=document.body;
document.getElementById("darkToggle").onclick=()=>{
    body.classList.toggle("dark");
};

function openDeliveries(id){
fetch("?ajax_branch="+id)
.then(r=>r.json())
.then(d=>{
document.getElementById("modalBranchName").innerText=d.branch.branch_name+" – Delivery Details";
document.getElementById("modalSummary").innerHTML=`
<div class="summary-card"><h3>Big Trays</h3><p>${d.summary.big}</p></div>
<div class="summary-card"><h3>Small Trays</h3><p>${d.summary.small}</p></div>
<div class="summary-card"><h3>Total Eggs</h3><p>${d.summary.eggs}</p></div>`;
let t=`<table><tr><th>ID</th><th>Big</th><th>Small</th><th>Total Eggs</th><th>Date</th></tr>`;
if(d.deliveries.length){
d.deliveries.forEach(x=>{
t+=`<tr>
<td>${x.id}</td>
<td>${x.big_trays}</td>
<td>${x.small_trays}</td>
<td>${x.big_trays*12+x.small_trays*6}</td>
<td>${x.delivery_datetime}</td>
</tr>`;
});
}else{
t+=`<tr><td colspan="5">No deliveries found</td></tr>`;
}
t+=`</table>`;
document.getElementById("modalTable").innerHTML=t;
document.getElementById("deliveriesModal").style.display="flex";
});
}

function closeModal(){
document.getElementById("deliveriesModal").style.display="none";
}
</script>

</body>
</html>