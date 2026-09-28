<?php
require_once __DIR__ . '/includes/config.php';

// ---- EDIT THESE ----
$email = 'adminlaundry@gmail.com';
$plainPassword = 'q\52<A52z9Ol';
// ---------------------

$users = new User($db);

if ($users->emailExists($email)) {
    die("An account with email '{$email}' already exists. Nothing was created.");
}

$id = $users->create($email, $plainPassword);

echo "<h2 style='color: green;'>✅ Admin account created!</h2>";
echo "<p>Email: " . h($email) . "</p>";
echo "<p>User ID: {$id}</p>";
echo "<p style='color: red; font-weight: bold;'>Now delete this file (create-admin.php) before going any further.</p>";