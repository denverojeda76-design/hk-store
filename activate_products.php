<?php
$db = new PDO('sqlite:database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("UPDATE products SET is_active = 1 WHERE is_active = 0");
$stmt->execute();

echo "Products activated: " . $stmt->rowCount() . PHP_EOL;
echo "Total products: " .
    $db->query("SELECT COUNT(*) FROM products")->fetchColumn() . PHP_EOL;
echo "Active products: " .
    $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn() . PHP_EOL;
