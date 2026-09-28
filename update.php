<?php
require_once __DIR__ . '/includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$orderId = (int)$_GET['id'];
$orders = new Orders($db);
$order = $orders->findById($orderId);

if (!$order) {
    header('Location: dashboard.php');
    exit;
}

$customerModel = new Customer($db);
$machineModel  = new Machine($db);

$customers = $customerModel->findAll('name ASC');
$machines  = $machineModel->getGroupedByType();

// Machines currently assigned to this order
$assignedMachines = $machineModel->getForOrder($orderId);
$assignedIds = array_map(fn($m) => (int)$m['id'], $assignedMachines);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['customer_id']) && empty($_POST['customerName'])) {
        $errors[] = 'Please select a customer.';
    }
    if (empty($_POST['mode']) || !in_array($_POST['mode'], ['pickup', 'delivery'])) {
        $errors[] = 'Please select a valid mode.';
    }
    if (empty($_POST['service-type'])) {
        $errors[] = 'Service type is required.';
    }
    if (empty($_POST['weight']) || (float)$_POST['weight'] <= 0) {
        $errors[] = 'Please enter a valid weight.';
    }
    if ($_POST['mode'] === 'delivery' && empty($_POST['address'])) {
        $errors[] = 'Delivery address is required.';
    }

    $allowedStatuses = ['pending', 'washing', 'finished'];
    if (empty($_POST['order_status']) || !in_array($_POST['order_status'], $allowedStatuses)) {
        $errors[] = 'Invalid order status.';
    }

    if (empty($errors)) {
        // If a customer was selected, fill customer_name from the record
        if (!empty($_POST['customer_id'])) {
            $cust = $customerModel->findById((int)$_POST['customer_id']);
            if ($cust) {
                $_POST['customerName'] = $cust['name'];
                $_POST['address'] = $_POST['address'] ?: $cust['address'];
            }
        }

        $updated = $orders->updateOrder($orderId, $_POST);
        if ($updated) {
            $orders->updateStatus($orderId, $_POST['order_status']);
            $success = true;

            // Refresh order data and machine lists
            $order = $orders->findById($orderId);
            $machines = $machineModel->getGroupedByType();
            $assignedMachines = $machineModel->getForOrder($orderId);
            $assignedIds = array_map(fn($m) => (int)$m['id'], $assignedMachines);
        } else {
            $errors[] = 'Failed to update order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/global.css">
    <link rel="stylesheet" href="style/order.css">
    <link rel="stylesheet" href="style/update.css">
    <link rel="stylesheet" href="style/notif.css">
    <link rel="stylesheet" href="style/header.css">
    <title>Update Order</title>
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
            <a href="machine.php">Machines</a>
            <a href="customer.php">Add Customer</a>
        </nav>
        <a href="logout.php" class="logout">LOGOUT</a>
    </header>

    <main class="order-page">
        <?php if ($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotif('✅ Order updated successfully!');
                    setTimeout(hideNotif, 4000);
                });
            </script>
        <?php elseif (!empty($errors)): ?>
            <div style="color: red; margin-bottom: 20px;">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="update.php?id=<?= $order['id'] ?>" method="POST">
            <div class="order-instructions">
                <p>UPDATE ORDER</p>
                <p>Modify the order details below.</p>
            </div>

            <!-- Order ID (readonly) -->
            <section class="customer">
                <div class="section-title">ORDER ID</div>
                <div class="section-body">
                    <div class="field">
                        <label for="orderId">ORDER ID</label>
                        <input type="text" name="orderId" id="orderId" value="<?= h($order['order_code']) ?>" readonly>
                    </div>
                </div>
            </section>

            <!-- Customer -->
            <section class="customer">
                <div class="section-title">CUSTOMER</div>
                <div class="section-body">
                    <div class="field">
                        <label for="customer_id">SELECT CUSTOMER</label>
                        <select name="customer_id" id="customer_id" required>
                            <option value="">— Choose a customer —</option>
                            <?php foreach ($customers as $cust): ?>
                                <option value="<?= (int)$cust['id'] ?>"
                                    <?= (int)($order['customer_id'] ?? 0) === (int)$cust['id'] ? 'selected' : '' ?>>
                                    <?= h($cust['name']) ?><?= $cust['address'] ? ' — ' . h($cust['address']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="font-size: 11px; color: #666; margin-top: 6px;">
                            Customer not listed? <a href="customer.php">Add a new customer</a>.
                        </small>
                    </div>
                </div>
            </section>

            <!-- Payment Status -->
            <section class="payment-status">
                <div class="section-title">PAYMENT STATUS</div>
                <div class="payment-toggle">
                    <input type="radio" name="payment" id="payment-unpaid" class="payment-radio" value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'checked' : '' ?>>
                    <label for="payment-unpaid" class="payment-option unpaid">UNPAID</label>

                    <input type="radio" name="payment" id="payment-paid" class="payment-radio" value="paid" <?= $order['payment_status'] === 'paid' ? 'checked' : '' ?>>
                    <label for="payment-paid" class="payment-option paid">PAID</label>
                </div>
            </section>

            <!-- Order Status -->
            <section class="order-status">
                <div class="section-title">ORDER STATUS</div>
                <div class="section-body">
                    <div class="field">
                        <label for="order_status">STATUS</label>
                        <select id="order_status" name="order_status" required>
                            <option value="pending"  <?= $order['order_status'] === 'pending'  ? 'selected' : '' ?>>Pending</option>
                            <option value="washing"  <?= $order['order_status'] === 'washing'  ? 'selected' : '' ?>>Washing</option>
                            <option value="finished" <?= $order['order_status'] === 'finished' ? 'selected' : '' ?>>Finished</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Mode -->
            <section class="select-mode">
                <div class="section-title">SELECT MODE</div>
                <div class="mode-toggle">
                    <input type="radio" name="mode" id="mode-pickup" class="mode-radio" value="pickup" <?= $order['mode'] === 'pickup' ? 'checked' : '' ?>>
                    <label for="mode-pickup" class="mode-option">PICKUP</label>

                    <input type="radio" name="mode" id="mode-delivery" class="mode-radio" value="delivery" <?= $order['mode'] === 'delivery' ? 'checked' : '' ?>>
                    <label for="mode-delivery" class="mode-option">DELIVERY</label>
                </div>
            </section>

            <!-- Service Type -->
            <section class="service-type">
                <div class="section-title">SERVICE TYPE</div>
                <div class="service-type-grid">
                    <input type="radio" name="service-type" id="service-wash-fold" class="service-radio" value="wash-fold" <?= $order['service_type'] === 'wash-fold' ? 'checked' : '' ?>>
                    <label for="service-wash-fold" class="service-option">
                        <span class="service-name">WASH</span>
                        <span class="service-price">From &#8369; 60 (8KG)</span>
                    </label>

                    <input type="radio" name="service-type" id="service-dry-cleaning" class="service-radio" value="dry-cleaning" <?= $order['service_type'] === 'dry-cleaning' ? 'checked' : '' ?>>
                    <label for="service-dry-cleaning" class="service-option">
                        <span class="service-name">DRY</span>
                        <span class="service-price">&#8369; 60 (8KG)</span>
                    </label>

                    <input type="radio" name="service-type" id="service-express-wash" class="service-radio" value="full-service" <?= $order['service_type'] === 'full-service' ? 'checked' : '' ?>>
                    <label for="service-express-wash" class="service-option">
                        <span class="service-name">FULL SERVICE</span>
                        <span class="service-price">From &#8369; 180 (8KG)</span>
                    </label>

                    <input type="radio" name="service-type" id="service-ironing-only" class="service-radio" value="fold-only" <?= $order['service_type'] === 'fold-only' ? 'checked' : '' ?>>
                    <label for="service-ironing-only" class="service-option">
                        <span class="service-name">FOLD</span>
                        <span class="service-price">From &#8369; 30 (8KG)</span>
                    </label>
                </div>
            </section>

            <!-- Machines -->
            <section class="machines-section">
                <div class="section-title">ASSIGN MACHINES</div>
                <div class="section-body">
                    <div class="machine-picker">
                        <div class="machine-picker-col">
                            <p class="machine-picker-label">WASHERS</p>
                            <?php foreach ($machines['washer'] as $m): ?>
                                <?php
                                    $mid = (int)$m['id'];
                                    $isAssignedHere = in_array($mid, $assignedIds, true);
                                    $isVacant = $m['status'] === 'vacant';
                                    $canPick = $isAssignedHere || $isVacant;
                                ?>
                                <label class="machine-pick <?= $canPick ? '' : 'disabled' ?>">
                                    <input type="checkbox"
                                           name="machines[]"
                                           value="<?= $mid ?>"
                                           <?= $isAssignedHere ? 'checked' : '' ?>
                                           <?= $canPick ? '' : 'disabled' ?>>
                                    <?= h($m['name']) ?>
                                    <span class="machine-pick-status"><?= h($m['status']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="machine-picker-col">
                            <p class="machine-picker-label">DRYERS</p>
                            <?php foreach ($machines['dryer'] as $m): ?>
                                <?php
                                    $mid = (int)$m['id'];
                                    $isAssignedHere = in_array($mid, $assignedIds, true);
                                    $isVacant = $m['status'] === 'vacant';
                                    $canPick = $isAssignedHere || $isVacant;
                                ?>
                                <label class="machine-pick <?= $canPick ? '' : 'disabled' ?>">
                                    <input type="checkbox"
                                           name="machines[]"
                                           value="<?= $mid ?>"
                                           <?= $isAssignedHere ? 'checked' : '' ?>
                                           <?= $canPick ? '' : 'disabled' ?>>
                                    <?= h($m['name']) ?>
                                    <span class="machine-pick-status"><?= h($m['status']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Load Details -->
            <section class="service-details">
                <div class="section-title">LOAD DETAILS</div>
                <div class="section-body">
                    <div class="field-row">
                        <div class="field">
                            <label for="weight">ESTIMATED WEIGHT (KG)</label>
                            <div class="input-with-suffix">
                                <input type="number" id="weight" name="weight" step="0.1" min="0.1" value="<?= h($order['weight_kg']) ?>" required>
                                <span class="suffix">kg</span>
                            </div>
                        </div>

                        <div class="field">
                            <label for="items">NUMBER OF ITEMS</label>
                            <input type="number" id="items" name="items" min="0" value="<?= h($order['item_count']) ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label for="extras">EXTRAS / ADD-ONS</label>
                        <textarea id="extras" name="extras" rows="3" placeholder="e.g. Extra soap, fabric conditioner, bleach..."><?= h($order['extras']) ?></textarea>
                    </div>

                    <div class="field">
                        <label for="instructions">SPECIAL INSTRUCTIONS</label>
                        <textarea id="instructions" name="instructions" rows="4" placeholder="Use hypoallergenic detergent..."><?= h($order['special_instructions']) ?></textarea>
                    </div>
                </div>
            </section>

            <!-- Schedule -->
            <section class="service-schedule" id="schedule-section">
                <div class="section-title" id="schedule-title"><?= $order['mode'] === 'delivery' ? 'DELIVERY SCHEDULE' : 'PICKUP SCHEDULE' ?></div>
                <div class="section-body">
                    <div class="field-row">
                        <div class="field">
                            <label for="pickup-date" id="date-label"><?= $order['mode'] === 'delivery' ? 'DELIVERY DATE' : 'PICKUP DATE' ?></label>
                            <input type="date" id="pickup-date" name="pickup-date" value="<?= h($order['schedule_date']) ?>" required>
                        </div>

                        <div class="field">
                            <label for="pickup-time" id="time-label"><?= $order['mode'] === 'delivery' ? 'DELIVERY TIME' : 'PICKUP TIME' ?></label>
                            <select id="pickup-time" name="pickup-time" required>
                                <option value="" disabled>Select a time slot</option>
                                <option value="8-10"  <?= $order['schedule_time'] === '8-10'  ? 'selected' : '' ?>>8:00 AM – 10:00 AM</option>
                                <option value="10-12" <?= $order['schedule_time'] === '10-12' ? 'selected' : '' ?>>10:00 AM – 12:00 PM</option>
                                <option value="13-15" <?= $order['schedule_time'] === '13-15' ? 'selected' : '' ?>>1:00 PM – 3:00 PM</option>
                                <option value="15-17" <?= $order['schedule_time'] === '15-17' ? 'selected' : '' ?>>3:00 PM – 5:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="field" id="address-field" <?= $order['mode'] === 'delivery' ? '' : 'style="display: none;"' ?>>
                        <label for="address">DELIVERY ADDRESS</label>
                        <input type="text" id="address" name="address" value="<?= h($order['address']) ?>" <?= $order['mode'] === 'delivery' ? 'required' : '' ?>>
                    </div>

                    <div class="field" id="delivery-note-field" <?= $order['mode'] === 'delivery' ? '' : 'style="display: none;"' ?>>
                        <label for="delivery_note">DELIVERY NOTE</label>
                        <textarea id="delivery_note" name="delivery_note" rows="3" placeholder="e.g. Gate code, landmarks, directions..."><?= h($order['delivery_note']) ?></textarea>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="action-row">
                <a href="dashboard.php" class="action-btn btn-cancel">CANCEL</a>
                <button type="submit" class="action-btn btn-update">UPDATE</button>
            </div>
        </form>
    </main>

    <script src="script/order.js"></script>
</body>
</html>