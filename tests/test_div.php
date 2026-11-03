<?php
$c = file_get_contents(__DIR__ . '/../app/Views/admin/subscriptions/my_subscription.php');
preg_match_all('/<div\b/i', $c, $m1);
preg_match_all('/<\/div>/i', $c, $m2);
echo 'Open: ' . count($m1[0]) . ' Close: ' . count($m2[0]) . PHP_EOL;

