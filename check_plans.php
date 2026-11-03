<?php
require 'vendor/autoload.php';
$app = Config\Services::codeigniter();
$app->initialize();
$db = Config\Database::connect();

$plans = $db->table('plans')->orderBy('plan_id', 'ASC')->get()->getResultArray();
echo "CURRENT PLANS:\n";
foreach ($plans as $p) {
    echo "ID: " . $p['plan_id'] . " | Slug: " . $p['plan_slug'] . " | Name: " . $p['plan_name'] . " | Current Users: " . $p['plan_max_users'] . "\n";
}

