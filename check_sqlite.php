<?php
$db = new PDO('sqlite:database/database.sqlite');

echo "Total products: ";
echo $db->query("SELECT COUNT(*) FROM products")->fetchColumn() . PHP_EOL;

echo "Active products: ";
echo $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn() . PHP_EOL;
