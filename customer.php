<?php
require_once __DIR__ . '/includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $errors[] = 'Customer name is required.';
    }

    if (empty($errors)) {
        $customer = new Customer($db);
        $id = $customer->create($name, $address ?: null);
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
    <link rel="stylesheet" href="style/update.css">
    <title>Add Customer</title>
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
            <a href="machine.php">Machines</a>
        </nav>
        <a href="logout.php" class="logout">LOGOUT</a>
    </header>

    <main class="order-page">
        <?php if ($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotif('✅ Customer added successfully!');
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

        <form action="customer.php" method="POST">
            <div class="order-instructions">
                <p>ADD CUSTOMER</p>
                <p>Register a new customer to the system.</p>
            </div>

            <section class="customer">
                <div class="section-title">CUSTOMER DETAILS</div>
                <div class="section-body">
                    <div class="field">
                        <label for="name">CUSTOMER NAME</label>
                        <input type="text" name="name" id="name" placeholder="Enter customer name" required>
                    </div>

                    <div class="field" style="margin-top: 20px;">
                        <label for="address">ADDRESS</label>
                        <input type="text" name="address" id="address" placeholder="Enter address (optional)">
                    </div>
                </div>
            </section>

            <div class="action-row">
                <a href="dashboard.php" class="action-btn btn-cancel">CANCEL</a>
                <button type="submit" class="action-btn btn-update">ADD CUSTOMER</button>
            </div>
        </form>
    </main>
</body>
</html>