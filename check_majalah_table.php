<?php
require_once 'includes/config.php';

$result = $mysqli->query("DESCRIBE majalah");
echo "Struktur tabel majalah:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

$mysqli->close();
?>