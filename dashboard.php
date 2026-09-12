<?php
require_once __DIR__ . '/includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$ordersModel = new Orders($db);

// Fetch all orders (latest first)
$orders = $ordersModel->findAll('id DESC');

// Calculate stats
$totalOrders = count($orders);
$activeOrders = 0;
$revenueToday = 0;
$totalAmount = 0;

$today = date('Y-m-d');

foreach ($orders as $order) {
    if ($order['order_status'] !== 'finished') {
        $activeOrders++;
    }
    if (date('Y-m-d', strtotime($order['created_at'])) === $today) {
        $revenueToday += (float)$order['amount'];
    }
    $totalAmount += (float)$order['amount'];
}

$avgOrderValue = $totalOrders > 0 ? ($totalAmount / $totalOrders) : 0;

// This one is for the chart

$currentMonth = date('Y-m');
$daysInMonth = date('t'); 

$dailyRevenue = array_fill(1, $daysInMonth, 0); 

foreach ($orders as $order) {
    $orderMonth = date('Y-m', strtotime($order['created_at']));
    if ($orderMonth === $currentMonth) {
        $day = (int)date('j', strtotime($order['created_at']));
        $dailyRevenue[$day] += (float)$order['amount'];
    }
}

// Prepare data for chart
$revenueData = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    $revenueData[] = [
        'day' => $day,
        'total' => $dailyRevenue[$day]
    ];
}

include __DIR__ . '/includes/header.php';
?>

<main>
    <!-- STATS - this is where the admin can see those revenues. In the following.

    This is connected to the database but it is calculated here.
    -->
    <section class="stats">
        <div class="stat-card">
            <p class="label">TOTAL ORDERS</p>
            <h2><?= $totalOrders ?></h2>
            <p class="description">All time</p>
        </div>
        <div class="stat-card">
            <p class="label">ACTIVE ORDERS</p>
            <h2><?= $activeOrders ?></h2>
            <p class="description">In progress</p>
        </div>
        <div class="stat-card">
            <p class="label">REVENUE TODAY</p>
            <h2>&#8369; <?= number_format($revenueToday, 2) ?></h2>
            <p class="description">Today</p>
        </div>
        <div class="stat-card">
            <p class="label">AVG ORDER VALUE</p>
            <h2>&#8369; <?= number_format($avgOrderValue, 2) ?></h2>
            <p class="description">All time</p>
        </div>
    </section>

    <section class="orders-section">
        <div class="table-toolbar">
            <div class="filter-tabs">
                <span class="filter-label">ALL ORDERS</span>
                <button type="button" class="filter-btn active">ALL</button>
                <button type="button" class="filter-btn">PENDING</button>
                <button type="button" class="filter-btn">WASHING</button>
                <button type="button" class="filter-btn">FINISHED</button>
            </div>
            <input type="text" class="search-orders" placeholder="Search orders">
        </div>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>ORDER ID</th>
                    <th>CUSTOMER</th>
                    <th>SERVICE</th>
                    <th>WEIGHT</th>
                    <th>AMOUNT</th>
                    <th>DATE</th>
                    <th>SCHEDULE</th>
                    <th>ORDER STATUS</th>
                    <th>PAYMENT STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="10">No orders found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= h($order['order_code']) ?></td>
                            <td><?= h($order['customer_name']) ?></td>
                            <td><?= h($order['service_type'] ?? 'N/A') ?></td>
                            <td><?= h($order['weight_kg'] ?? '0') ?> kg</td>
                            <td>&#8369; <?= number_format((float)$order['amount'], 2) ?></td>
                            <td><?= date('M j', strtotime($order['created_at'])) ?></td>
                            <td><?= !empty($order['schedule_date']) ? date('M j', strtotime($order['schedule_date'])) : '—' ?></td>
                            <td><span class="status-text <?= strtolower($order['order_status']) ?>"><?= ucfirst($order['order_status']) ?></span></td>
                            <td><span class="payment-text" style="color: <?= $order['payment_status'] === 'paid' ? 'green' : 'red' ?>;"><?= ucfirst($order['payment_status']) ?></span></td>
                            <td>
                                <a href="update.php?id=<?= $order['id'] ?>" class="action-link">UPDATE</a>
                                <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this order?');">
                                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                    <button type="submit" class="action-link" style="background:none;border:none;cursor:pointer;">DELETE</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="table-footer">
            <p class="showing-text">SHOWING <?= count($orders) ?> OF <?= $totalOrders ?> ORDERS</p>
            <div class="pages">
                <button type="button" class="page-btn">&larr;</button>
                <button type="button" class="page-btn active">1</button>
                <button type="button" class="page-btn">&rarr;</button>
            </div>
        </div>
    </section>

    
    <section class="bottom-grid">
        <div class="panel revenue-panel">
            <p class="panel-title">REVENUE OVERVIEW &mdash; THIS MONTH</p>
            <div class="revenue-chart">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="panel quick-actions-panel">
            <p class="panel-title">QUICK ACTIONS</p>
            <button type="button" class="quick-action-btn"><a href="order.php">Create Order</a></button>
        </div>
    </section>
</main>
<script>
    window.revenueData = <?= json_encode($revenueData) ?>;
</script>
<script src="script/dashboard.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>