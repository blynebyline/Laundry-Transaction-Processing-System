<?php
require_once __DIR__ . '/includes/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

// Check if order ID is provided
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    if (empty($_POST['customerName'])) {
        $errors[] = 'Customer name is required.';
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

    // Validate order status
    $allowedStatuses = ['pending', 'washing', 'finished'];
    if (empty($_POST['order_status']) || !in_array($_POST['order_status'], $allowedStatuses)) {
        $errors[] = 'Invalid order status.';
    }

    if (empty($errors)) {
        $updated = $orders->updateOrder($orderId, $_POST);
        if ($updated) {
            $orders->updateStatus($orderId, $_POST['order_status']);
            $success = true;
            // Refresh order data
            $order = $orders->findById($orderId);
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

    <header>
        <nav class="header-nav">
            <a href="dashboard.php">Dashboard</a>
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

            <!-- Customer Details -->
            <section class="customer">
                <div class="section-title">CUSTOMER DETAILS</div>
                <div class="section-body">
                    <div class="field">
                        <label for="customerName">CUSTOMER NAME</label>
                        <input type="text" name="customerName" id="customerName" value="<?= h($order['customer_name']) ?>" required>
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
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="washing" <?= $order['order_status'] === 'washing' ? 'selected' : '' ?>>Washing</option>
                            <option value="finished" <?= $order['order_status'] === 'finished' ? 'selected' : '' ?>>Finished</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Mode Selection -->
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
                                <option value="8-10" <?= $order['schedule_time'] === '8-10' ? 'selected' : '' ?>>8:00 AM – 10:00 AM</option>
                                <option value="10-12" <?= $order['schedule_time'] === '10-12' ? 'selected' : '' ?>>10:00 AM – 12:00 PM</option>
                                <option value="13-15" <?= $order['schedule_time'] === '13-15' ? 'selected' : '' ?>>1:00 PM – 3:00 PM</option>
                                <option value="15-17" <?= $order['schedule_time'] === '15-17' ? 'selected' : '' ?>>3:00 PM – 5:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address field (only for delivery) -->
                    <div class="field" id="address-field" <?= $order['mode'] === 'delivery' ? '' : 'style="display: none;"' ?>>
                        <label for="address">DELIVERY ADDRESS</label>
                        <input type="text" id="address" name="address" value="<?= h($order['address']) ?>" <?= $order['mode'] === 'delivery' ? 'required' : '' ?>>
                    </div>
                </div>
            </section>

            <!-- Action Buttons -->
            <div class="action-row">
                <a href="dashboard.php" class="action-btn btn-cancel">CANCEL</a>
                <button type="submit" class="action-btn btn-update">UPDATE</button>
            </div>
        </form>
    </main>

    <!-- Mode toggle script (inline) -->
    <script>
        const modeRadios = document.querySelectorAll('input[name="mode"]');
        const scheduleSection = document.getElementById('schedule-section');
        const addressField = document.getElementById('address-field');
        const scheduleTitle = document.getElementById('schedule-title');
        const dateLabel = document.getElementById('date-label');
        const timeLabel = document.getElementById('time-label');
        const addressInput = document.getElementById('address');

        const modeConfig = {
            'pickup': {
                showSchedule: true,
                showAddress: false,
                scheduleTitle: 'PICKUP SCHEDULE',
                dateLabel: 'PICKUP DATE',
                timeLabel: 'PICKUP TIME'
            },
            'delivery': {
                showSchedule: true,
                showAddress: true,
                scheduleTitle: 'DELIVERY SCHEDULE',
                dateLabel: 'DELIVERY DATE',
                timeLabel: 'DELIVERY TIME'
            }
        };

        function updateMode(mode) {
            const config = modeConfig[mode];
            if (!config) return;

            scheduleSection.style.display = config.showSchedule ? '' : 'none';

            if (config.showSchedule) {
                addressField.style.display = config.showAddress ? '' : 'none';
                scheduleTitle.textContent = config.scheduleTitle;
                dateLabel.textContent = config.dateLabel;
                timeLabel.textContent = config.timeLabel;
                addressInput.required = config.showAddress;
            } else {
                addressInput.required = false;
            }
        }

        modeRadios.forEach(radio => {
            radio.addEventListener('change', e => updateMode(e.target.value));
        });

        // Init
        const initialMode = document.querySelector('input[name="mode"]:checked');
        if (initialMode) updateMode(initialMode.value);
    </script>

    <!-- Notification functions (inline) -->
    <script>
        function showNotif(message) {
            const notif = document.getElementById('notif');
            const notifMsg = document.getElementById('notif-message');
            if (notif && notifMsg) {
                notifMsg.textContent = message;
                notif.style.display = 'flex';
                void notif.offsetWidth;
                notif.classList.add('show');
            }
        }

        function hideNotif() {
            const notif = document.getElementById('notif');
            if (notif) {
                notif.classList.remove('show');
                setTimeout(() => {
                    notif.style.display = 'none';
                }, 300);
            }
        }
    </script>
</body>
</html>