<?php
$conn = new mysqli('localhost', 'root', '', 'codexmlt_shimla_airlines');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SHOW TABLES");
echo "Tables in codexmlt_shimla_airlines:\n";
while ($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}
?>
