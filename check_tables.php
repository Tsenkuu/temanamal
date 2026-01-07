<?php
$_SERVER['SERVER_NAME'] = 'localhost'; // Simulate local environment
require 'includes/config.php';

$result = $mysqli->query('SHOW TABLES');
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "Tables in database:\n";
foreach ($tables as $table) {
    echo "- $table\n";
}
?>