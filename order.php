<?php
require_once __DIR__ . '/includes/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orders = new Orders($db);

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

    // If delivery, address is required
    if ($_POST['mode'] === 'delivery' && empty($_POST['address'])) {
        $errors[] = 'Delivery address is required.';
    }

    
    if (empty($errors)) {
        $id = $orders->create($_POST);
        $success = true;
    
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
    <link rel="stylesheet" href="style/notif.css">
    <link rel="stylesheet" href="style/header.css">
    <title>New Order</title>
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
                    showNotif('✅ Order created successfully!');
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

        <form action="order.php" method="POST">
            <div class="order-instructions">
                <p>NEW LAUNDRY ORDER</p>
                <p>Fill in the details below to place your order.</p>
            </div>

            <!-- Customer details -->
            <section class="customer">
                <div class="section-title">CUSTOMER DETAILS</div>
                <div class="section-body">
                    <div class="field">
                        <label for="customerName">CUSTOMER NAME</label>
                        <input type="text" name="customerName" id="customerName" placeholder="Enter customer name" required>
                    </div>
                </div>
            </section>

            <!-- Mode Selection -->
            <section class="select-mode">
                <div class="section-title">SELECT MODE</div>
                <div class="mode-toggle">
                    <input type="radio" name="mode" id="mode-pickup" class="mode-radio" value="pickup" checked>
                    <label for="mode-pickup" class="mode-option">PICKUP</label>

                    <input type="radio" name="mode" id="mode-delivery" class="mode-radio" value="delivery">
                    <label for="mode-delivery" class="mode-option">DELIVERY</label>
                </div>
            </section>

            <!-- Service Rtype -->
            <section class="service-type">
                <div class="section-title">SERVICE TYPE</div>
                <div class="service-type-grid">
                    <input type="radio" name="service-type" id="service-wash-fold" class="service-radio" value="wash-fold" checked>
                    <label for="service-wash-fold" class="service-option">
                        <span class="service-name">WASH</span>
                        <span class="service-price">From &#8369; 60 (8KG)</span>
                    </label>

                    <input type="radio" name="service-type" id="service-dry-cleaning" class="service-radio" value="dry-cleaning">
                    <label for="service-dry-cleaning" class="service-option">
                        <span class="service-name">DRY</span>
                        <span class="service-price">&#8369; 60 (8KG)</span>
                    </label>

                    <input type="radio" name="service-type" id="service-express-wash" class="service-radio" value="full-service">
                    <label for="service-express-wash" class="service-option">
                        <span class="service-name">FULL SERVICE</span>
                        <span class="service-price">From &#8369; 180 (8KG)</span>
                    </label>

                    <input type="radio" name="service-type" id="service-ironing-only" class="service-radio" value="fold-only">
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
                                <input type="number" id="weight" name="weight" step="0.1" min="0.1" placeholder="3.5" required>
                                <span class="suffix">kg</span>
                            </div>
                        </div>

                        <div class="field">
                            <label for="items">NUMBER OF ITEMS</label>
                            <input type="number" id="items" name="items" min="0" placeholder="12">
                        </div>
                    </div>

                    <div class="field">
                        <label for="instructions">SPECIAL INSTRUCTIONS</label>
                        <textarea id="instructions" name="instructions" rows="4" placeholder="Use hypoallergenic detergent..."></textarea>
                    </div>
                </div>
            </section>

            <!-- Schedule -->
            <section class="service-schedule" id="schedule-section">
                <div class="section-title" id="schedule-title">PICKUP SCHEDULE</div>
                <div class="section-body">
                    <div class="field-row">
                        <div class="field">
                            <label for="pickup-date" id="date-label">PICKUP DATE</label>
                            <input type="date" id="pickup-date" name="pickup-date" required>
                        </div>

                        <div class="field">
                            <label for="pickup-time" id="time-label">PICKUP TIME</label>
                            <select id="pickup-time" name="pickup-time" required>
                                <option value="" selected disabled>Select a time slot</option>
                                <option value="8-10">8:00 AM – 10:00 AM</option>
                                <option value="10-12">10:00 AM – 12:00 PM</option>
                                <option value="13-15">1:00 PM – 3:00 PM</option>
                                <option value="15-17">3:00 PM – 5:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="field" id="address-field" style="display: none;">
                        <label for="address">DELIVERY ADDRESS</label>
                        <input type="text" id="address" name="address" placeholder="Enter delivery address">
                    </div>
                </div>
            </section>

            <button type="submit" class="submit-order">ADD ORDER</button>
        </form>
    </main>

    <script src="script/order.js"></script>
    <script src="script/notif.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>