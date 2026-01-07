<?php
require_once 'includes/config.php';

try {
    // Check if nama_file column exists
    $result = $mysqli->query("DESCRIBE majalah");
    $has_nama_file = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'nama_file') {
            $has_nama_file = true;
            break;
        }
    }

    if ($has_nama_file) {
        // Alter table to change nama_file to link
        $sql = "ALTER TABLE majalah CHANGE nama_file link VARCHAR(500) NOT NULL";
        $mysqli->query($sql);
        echo "Table majalah updated successfully. Column 'nama_file' changed to 'link'.\n";
    } else {
        echo "Column 'nama_file' does not exist. Table already has 'link' column.\n";
    }
} catch (Exception $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}

$mysqli->close();
?>