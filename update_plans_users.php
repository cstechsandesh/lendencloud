<?php
$host = '127.0.0.1';
$db   = 'lenden_db';
$user = 'root';
$pass = 'CSTech_0099';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$stmt = $pdo->query("SELECT plan_id, plan_code, plan_name, plan_max_users, plan_features FROM ld_subscription_plans ORDER BY plan_id ASC");
$plans = $stmt->fetchAll();

echo "CURRENT PLANS IN DB:\n";
foreach ($plans as $p) {
    echo "ID: {$p['plan_id']} | Code: {$p['plan_code']} | Name: {$p['plan_name']} | Users: {$p['plan_max_users']}\n";
}

// User request: "update 1,3,5 and 10 users in plans respectively"
$updates = [
    1 => 1,
    2 => 3,
    3 => 5,
    4 => 10,
];

foreach ($updates as $id => $maxUsers) {
    // Also update plan_features JSON string to reflect the updated seat limits
    $currPlan = null;
    foreach ($plans as $p) {
        if ((int)$p['plan_id'] === $id) {
            $currPlan = $p;
            break;
        }
    }
    
    if ($currPlan) {
        $features = json_decode($currPlan['plan_features'], true) ?: [];
        $newFeatures = [];
        foreach ($features as $f) {
            if ($id === 1 && (strpos($f, 'User') !== false || strpos($f, 'user') !== false)) {
                $newFeatures[] = "1 Active User Login";
            } elseif ($id === 2 && (strpos($f, 'User') !== false || strpos($f, 'user') !== false)) {
                $newFeatures[] = "Up to 3 Multi-Role User Logins";
            } elseif ($id === 3 && (strpos($f, 'Team Members') !== false || strpos($f, 'User') !== false)) {
                $newFeatures[] = "Up to 5 Active Team Members";
            } elseif ($id === 4 && (strpos($f, 'User') !== false || strpos($f, 'user') !== false)) {
                $newFeatures[] = "Up to 10 User Accounts & Departments";
            } else {
                $newFeatures[] = $f;
            }
        }
        
        $upStmt = $pdo->prepare("UPDATE ld_subscription_plans SET plan_max_users = :users, plan_features = :features WHERE plan_id = :id");
        $upStmt->execute([
            ':users' => $maxUsers,
            ':features' => json_encode($newFeatures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':id' => $id,
        ]);
        echo "Updated Plan ID {$id} -> {$maxUsers} users.\n";
    }
}

echo "\nAFTER UPDATE:\n";
$stmt = $pdo->query("SELECT plan_id, plan_code, plan_name, plan_max_users, plan_features FROM ld_subscription_plans ORDER BY plan_id ASC");
foreach ($stmt->fetchAll() as $p) {
    echo "ID: {$p['plan_id']} | Code: {$p['plan_code']} | Name: {$p['plan_name']} | Users: {$p['plan_max_users']}\n";
    echo "   Features: " . $p['plan_features'] . "\n";
}

