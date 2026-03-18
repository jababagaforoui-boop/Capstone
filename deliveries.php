<?php
session_start();
include 'includes/db.php';

/* ===== AUTO ADMIN LOGIN (LOCAL TESTING) ===== */
if(!isset($_SESSION['user'])){
    $_SESSION['user'] = [
        "role"=>"admin",
        "name"=>"Administrator"
    ];
}

/* ===== SETTINGS ===== */
$month = date('Y-m');

/* ===== FETCH BRANCHES ===== */
$branches_list = [];
$result_branches = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC");

while($row = $result_branches->fetch_assoc()){
    $branches_list[$row['id']] = $row['branch_name'];
}

/* ===== FETCH OR CREATE MONTHLY STOCK ===== */
$stock_query = $conn->query("SELECT * FROM stocks WHERE month='$month' LIMIT 1");

if($stock_query->num_rows === 0){
    $conn->query("INSERT INTO stocks (month,big_trays,small_trays) VALUES ('$month',0,0)");
    $stock_query = $conn->query("SELECT * FROM stocks WHERE month='$month' LIMIT 1");
}

$stock = $stock_query->fetch_assoc();

$success="";
$error="";

/* ===== ADD STOCK ===== */
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['add_stock'])){

$add_big=max(0,(int)$_POST['add_big_trays']);
$add_small=max(0,(int)$_POST['add_small_trays']);

if($add_big==0 && $add_small==0){
$error="Please enter trays.";
}else{

$stock['big_trays'] += $add_big;
$stock['small_trays'] += $add_small;

$conn->query("UPDATE stocks
SET big_trays={$stock['big_trays']},
small_trays={$stock['small_trays']}
WHERE month='$month'");

$success="Stock added successfully!";
}

}

/* ===== RECORD DELIVERY ===== */
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['record_delivery'])){

$branch=(int)$_POST['branch'];
$bigTrays=max(0,(int)$_POST['big_trays']);
$smallTrays=max(0,(int)$_POST['small_trays']);

if(!isset($branches_list[$branch])){
$error="Invalid branch.";
}

elseif($bigTrays==0 && $smallTrays==0){
$error="Enter trays.";
}

elseif($bigTrays>$stock['big_trays'] || $smallTrays>$stock['small_trays']){
$error="Not enough stock!";
}

else{

/* INSERT DELIVERY */
$stmt=$conn->prepare("INSERT INTO deliveries
(branch_id,big_trays,small_trays,delivery_datetime,created_at)
VALUES(?,?,?,NOW(),NOW())");

$stmt->bind_param("iii",$branch,$bigTrays,$smallTrays);
$stmt->execute();
$stmt->close();

/* UPDATE CLIENT INVENTORY */
$stmt=$conn->prepare("SELECT big_trays,small_trays FROM inventory WHERE branch_id=? LIMIT 1");
$stmt->bind_param("i",$branch);
$stmt->execute();
$res=$stmt->get_result();
$inv=$res->fetch_assoc();

if($inv){

$new_big=$inv['big_trays']+$bigTrays;
$new_small=$inv['small_trays']+$smallTrays;

$stmt2=$conn->prepare("UPDATE inventory
SET big_trays=?,small_trays=?,updated_at=NOW()
WHERE branch_id=?");

$stmt2->bind_param("iii",$new_big,$new_small,$branch);
$stmt2->execute();

}else{

$stmt2=$conn->prepare("INSERT INTO inventory
(branch_id,big_trays,small_trays,created_at,updated_at)
VALUES(?,?,?,NOW(),NOW())");

$stmt2->bind_param("iii",$branch,$bigTrays,$smallTrays);
$stmt2->execute();

}

/* UPDATE ADMIN STOCK */
$stock['big_trays']-=$bigTrays;
$stock['small_trays']-=$smallTrays;

$conn->query("UPDATE stocks
SET big_trays={$stock['big_trays']},
small_trays={$stock['small_trays']}
WHERE month='$month'");

$success="Delivery recorded!";
}

}

/* ===== FETCH DELIVERIES ===== */
$deliveries=[];
$result=$conn->query("SELECT d.*,b.branch_name
FROM deliveries d
JOIN branches b ON d.branch_id=b.id
ORDER BY d.created_at DESC");

while($row=$result->fetch_assoc()){
$deliveries[]=$row;
}

/* ===== MONTHLY TOTAL ===== */
$totalData=$conn->query("SELECT
COUNT(*) as total_deliveries,
SUM(big_trays) as total_big,
SUM(small_trays) as total_small
FROM deliveries
WHERE DATE_FORMAT(created_at,'%Y-%m')='$month'")->fetch_assoc();

$total_deliveries=$totalData['total_deliveries'] ?? 0;
$total_big=$totalData['total_big'] ?? 0;
$total_small=$totalData['total_small'] ?? 0;

$totalEggsMonth=($total_big*12)+($total_small*6);

/* ===== DELIVERIES PER BRANCH FOR THIS MONTH ===== */
$branchDeliveries = [];
$chartLabels=[];
$chartBig=[];
$chartSmall=[];
$chartTotal=[];
foreach($branches_list as $id=>$name){
    $data = $conn->query("SELECT
        SUM(big_trays) as big,
        SUM(small_trays) as small,
        COUNT(*) as total
        FROM deliveries
        WHERE branch_id=$id AND DATE_FORMAT(created_at,'%Y-%m')='$month'
    ")->fetch_assoc();

    $branchDeliveries[$id] = [
        'branch_name'=>$name,
        'big'=> $data['big'] ?? 0,
        'small'=> $data['small'] ?? 0,
        'total_eggs'=> (($data['big'] ?? 0)*12)+(($data['small'] ?? 0)*6),
        'deliveries'=> $data['total'] ?? 0
    ];

    // Chart data
    $chartLabels[] = $name;
    $chartBig[] = $data['big'] ?? 0;
    $chartSmall[] = $data['small'] ?? 0;
    $chartTotal[] = (($data['big'] ?? 0)*12)+(($data['small'] ?? 0)*6);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delivery Management - Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana;}
body{background:#f0fdf4;color:#2d6a4f;}
.wrapper{display:flex;min-height:100vh;}
.sidebar{width:240px;background:#38b000;color:#fff;padding:25px;display:flex;flex-direction:column;justify-content:space-between;}
.sidebar .menu{display:flex;flex-direction:column;}
.sidebar a{padding:12px;margin-bottom:12px;background:#2d6a4f;color:#fff;text-decoration:none;border-radius:12px;font-weight:bold;transition:0.3s;}
.sidebar a.active{background:#70d6ff;color:#000;}
.sidebar a:hover{transform:translateX(5px);}
.sidebar .logout{background:#d00000;margin-top:20px;}
.sidebar .logout:hover{background:#9d0208;}
.main-content{flex:1;padding:30px;}
.alert{padding:12px;border-radius:12px;margin-bottom:20px;font-weight:600;}
.alert.success{background:#d1fae5;color:#16a34a;}
.alert.error{background:#fcd5ce;color:#b91c1c;}
.dashboard-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:25px;}
.dashboard-card{background:#fff;padding:25px;border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,0.08);text-align:center;transition:0.3s;}
.dashboard-card:hover{box-shadow:0 12px 35px rgba(0,0,0,0.12);}
form input, form select{width:100%;padding:12px;border-radius:12px;border:1px solid #ccc;margin-bottom:12px;}
form button{padding:12px 20px;background:#38b000;color:#fff;border:none;border-radius:12px;font-weight:bold;cursor:pointer;transition:0.3s;}
form button:hover{background:#2d6a4f;}
table{width:100%;border-collapse:collapse;margin-top:20px;background:#fff;border-radius:12px;overflow:hidden;}
th,td{padding:12px;text-align:center;border-bottom:1px solid #ddd;}
th{background:#38b000;color:#fff;}
tr:nth-child(even){background:#f0fdf4;}
.chart-container{background:#fff;padding:25px;border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,0.08);margin-bottom:25px;}
</style>
</head>
<body>

<div class="wrapper">

<div class="sidebar">
    <div class="menu">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="branches.php">Branches</a>
        <a href="deliveries.php" class="active">Deliveries</a>
        <a href="sales.php">Sales</a>
        <a href="reports.php">Reports</a>
        <a href="stocks.php">Stocks</a>
        <a href="users.php">Users</a>
    </div>
    <a href="../home.php" class="logout">Logout</a>
</div>

<div class="main-content">

<h1>Delivery Management</h1>

<?php if($success) echo "<div class='alert success'>$success</div>"; ?>
<?php if($error) echo "<div class='alert error'>$error</div>"; ?>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h2><?= $total_deliveries ?></h2>
        <p>Total Deliveries</p>
    </div>
    <div class="dashboard-card">
        <h2><?= $stock['big_trays'] ?></h2>
        <p>Big Trays Remaining</p>
    </div>
    <div class="dashboard-card">
        <h2><?= $stock['small_trays'] ?></h2>
        <p>Small Trays Remaining</p>
    </div>
    <div class="dashboard-card">
        <h2><?= $totalEggsMonth ?></h2>
        <p>Total Eggs Delivered</p>
    </div>
</div>

<!-- ADD STOCK -->
<form method="POST">
    <h3>Add Eggs to Available Stock</h3>
    <input type="number" name="add_big_trays" placeholder="Add Big Trays (12 eggs each)" min="0" value="0">
    <input type="number" name="add_small_trays" placeholder="Add Small Trays (6 eggs each)" min="0" value="0">
    <button type="submit" name="add_stock">Add Eggs</button>
</form>

<br>

<!-- DELIVERY FORM -->
<form method="POST">
    <h3>Select Branch</h3>
    <select name="branch" required>
        <option value="">-- Choose Branch --</option>
        <?php foreach($branches_list as $id => $name): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
        <?php endforeach; ?>
    </select>

    <h3>Delivery Order</h3>
    <input type="number" name="big_trays" placeholder="Big Trays (12 eggs each)" min="0" value="0">
    <input type="number" name="small_trays" placeholder="Small Trays (6 eggs each)" min="0" value="0">
    <button type="submit" name="record_delivery">Record Delivery</button>
</form>

<h2>Monthly Deliveries Chart (All Branches)</h2>
<div class="chart-container">
    <canvas id="branchChart"></canvas>
</div>

<h2>Deliveries Per Month (All Branches)</h2>
<table>
<tr>
<th>Branch</th>
<th>Total Deliveries</th>
<th>Big Trays</th>
<th>Small Trays</th>
<th>Total Eggs</th>
</tr>
<?php foreach($branchDeliveries as $b): ?>
<tr>
<td><?= htmlspecialchars($b['branch_name']) ?></td>
<td><?= $b['deliveries'] ?></td>
<td><?= $b['big'] ?></td>
<td><?= $b['small'] ?></td>
<td><?= $b['total_eggs'] ?> pcs</td>
</tr>
<?php endforeach; ?>
</table>

<h2>Recent Deliveries</h2>
<table>
<tr>
<th>ID</th>
<th>Branch</th>
<th>Big Trays</th>
<th>Small Trays</th>
<th>Total Eggs</th>
<th>Date</th>
</tr>

<?php if(!empty($deliveries)): foreach($deliveries as $d): 
$total_eggs = ($d['big_trays'] * 12) + ($d['small_trays'] * 6);
?>
<tr>
<td><?= $d['id'] ?></td>
<td><?= htmlspecialchars($d['branch_name']) ?></td>
<td><?= $d['big_trays'] ?></td>
<td><?= $d['small_trays'] ?></td>
<td><?= $total_eggs ?> pcs</td>
<td><?= date("Y-m-d H:i", strtotime($d['created_at'])) ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6">No deliveries recorded.</td></tr>
<?php endif; ?>
</table>

</div>
</div>

<script>
const ctx = document.getElementById('branchChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            {
                label: 'Big Trays',
                data: <?= json_encode($chartBig) ?>,
                backgroundColor: '#38b000'
            },
            {
                label: 'Small Trays',
                data: <?= json_encode($chartSmall) ?>,
                backgroundColor: '#70d6ff'
            },
            {
                label: 'Total Eggs',
                data: <?= json_encode($chartTotal) ?>,
                backgroundColor: '#ffba08'
            }
        ]
    },
    options: {
        responsive:true,
        plugins:{
            legend:{position:'top'},
            title:{display:true,text:'Branch Deliveries This Month'}
        },
        scales:{
            y:{beginAtZero:true}
        }
    }
});
</script>

</body>
</html>