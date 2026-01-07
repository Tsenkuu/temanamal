<?php
$_SERVER['SERVER_NAME'] = 'localhost'; // Simulate local environment
$_SERVER['SERVER_PORT'] = 80;
require 'includes/config.php';

$result = $mysqli->query('DESCRIBE berita');
echo "Structure of 'berita' table:\n";
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']} - {$row['Extra']}\n";
}
?>