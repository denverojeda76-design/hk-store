<?php
$db = new PDO('sqlite:database/database.sqlite');

$stmt = $db->query("SELECT id, name, is_active FROM products");

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
