<?php
require_once __DIR__ . '/includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$machineModel = new Machine($db);
$success = false;
$error   = null;

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['machine_id'], $_POST['new_status'])) {
    $id     = (int)$_POST['machine_id'];
    $status = $_POST['new_status'];

    // Only allow vacant <-> unavailable here. in-use is managed by orders.
    if (!in_array($status, ['vacant', 'unavailable'], true)) {
        $error = 'Invalid status change.';
    } else {
        if ($machineModel->setStatus($id, $status)) {
            $success = true;
        } else {
            $error = 'Failed to update machine status.';
        }
    }
}

// Fetch all machines grouped by type
$grouped = $machineModel->getGroupedByType();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/global.css">
    <link rel="stylesheet" href="style/admin.css">
    <link rel="stylesheet" href="style/machine.css">
    <link rel="stylesheet" href="style/notif.css">
    <link rel="stylesheet" href="style/header.css">
    <title>Machines</title>
</head>
<body>
    <div id="notif" class="notif" style="display: none;">
        <span id="notif-message" class="notif-message"></span>
        <button class="notif-close" onclick="hideNotif()">×</button>
    </div>
    <script src="script/notif.js"></script>

    <header>
        <nav class="header-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="order.php">New Order</a>
            <a href="customer.php">Add Customer</a>
        </nav>
        <a href="logout.php" class="logout">LOGOUT</a>
    </header>

    <main>
    <?php if ($success): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotif('✅ Machine status updated.');
                setTimeout(hideNotif, 4000);
            });
        </script>
    <?php elseif ($error): ?>
        <div style="color: red; margin-bottom: 20px;">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <h2 class="machine-page-title">MACHINES</h2>

    <?php foreach (['washer', 'dryer'] as $type): ?>
        <section class="machine-section">
            <div class="section-title"><?= strtoupper($type) ?>S</div>
            <div class="machine-grid">
                <?php if (empty($grouped[$type])): ?>
                    <p>No <?= $type ?>s found.</p>
                <?php else: ?>
                    <?php foreach ($grouped[$type] as $machine): ?>
                        <?php
                            $activeOrder = null;
                            if ($machine['status'] === 'in-use') {
                                $activeOrder = $machineModel->getActiveOrder((int)$machine['id']);
                            }
                        ?>
                        <div class="machine-card <?= h($machine['status']) ?>">
                            <div class="machine-card-head">
                                <span class="machine-name"><?= h($machine['name']) ?></span>
                                <span class="machine-dot <?= h($machine['status']) ?>"></span>
                            </div>

                            <span class="machine-status <?= h($machine['status']) ?>">
                                <?= strtoupper(h($machine['status'])) ?>
                            </span>

                            <div class="machine-card-body">
                                <?php if ($machine['status'] === 'in-use' && $activeOrder): ?>
                                    <p class="machine-meta">
                                        <strong><?= h($activeOrder['order_code']) ?></strong><br>
                                        <?= h($activeOrder['customer_name']) ?>
                                    </p>
                                <?php elseif ($machine['status'] === 'in-use'): ?>
                                    <p class="machine-meta">In use</p>
                                <?php elseif ($machine['status'] === 'vacant'): ?>
                                    <p class="machine-meta">Ready to use</p>
                                <?php else: ?>
                                    <p class="machine-meta">Out of service</p>
                                <?php endif; ?>
                            </div>

                            <?php if ($machine['status'] !== 'in-use'): ?>
                                <form method="POST" action="machine.php" class="machine-card-action">
                                    <input type="hidden" name="machine_id" value="<?= (int)$machine['id'] ?>">
                                    <?php if ($machine['status'] === 'vacant'): ?>
                                        <input type="hidden" name="new_status" value="unavailable">
                                        <button type="submit" class="machine-btn">Mark Unavailable</button>
                                    <?php else: ?>
                                        <input type="hidden" name="new_status" value="vacant">
                                        <button type="submit" class="machine-btn">Mark Vacant</button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</main>
</body>
</html>