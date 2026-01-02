<?php
require_once '../config/connection.php';

$db = new Database();
$conn = $db->getConnection();

echo "Reservations Table Structure:\n";
echo str_repeat("=", 60) . "\n";
$structure = $conn->query("DESCRIBE reservations");
while ($col = $structure->fetch(PDO::FETCH_ASSOC)) {
    printf("%-30s %-20s %s\n", $col['Field'], $col['Type'], $col['Null']);
}

echo "\n\nSample Reservations (first 5):\n";
echo str_repeat("=", 60) . "\n";
$samples = $conn->query("SELECT * FROM reservations LIMIT 5");
if ($samples->rowCount() > 0) {
    while ($row = $samples->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
        echo "\n";
    }
} else {
    echo "No reservations found in the database.\n";
}
?>
