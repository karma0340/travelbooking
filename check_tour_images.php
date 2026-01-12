<?php
/**
 * Check Tour Images in Database
 * Verify that tours are using CDN URLs
 */

require_once 'includes/db.php';

$conn = getDbConnection();

echo "<h2>Tour Images Verification</h2>";
echo "<p>Checking if tours are using CDN URLs...</p>";
echo "<hr>";

$sql = "SELECT id, title, image FROM tours ORDER BY id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th>";
    echo "<th>Tour Title</th>";
    echo "<th>Image URL</th>";
    echo "<th>Status</th>";
    echo "<th>Preview</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        $isCDN = (strpos($row['image'], 'http') === 0);
        $status = $isCDN ? "<span style='color:green;'>✅ CDN</span>" : "<span style='color:orange;'>⚠️ Local</span>";
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td style='word-break: break-all; max-width: 400px;'>" . htmlspecialchars($row['image']) . "</td>";
        echo "<td>$status</td>";
        echo "<td><img src='{$row['image']}' style='max-width: 150px; height: auto;' alt='Preview' onerror=\"this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27150%27 height=%27100%27%3E%3Crect fill=%27%23ddd%27 width=%27150%27 height=%27100%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 fill=%27%23999%27%3EImage Error%3C/text%3E%3C/svg%3E'\"></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Summary
    $cdnCount = $conn->query("SELECT COUNT(*) as count FROM tours WHERE image LIKE 'http%'")->fetch_assoc()['count'];
    $localCount = $conn->query("SELECT COUNT(*) as count FROM tours WHERE image NOT LIKE 'http%'")->fetch_assoc()['count'];
    
    echo "<hr>";
    echo "<h3>Summary</h3>";
    echo "<p><strong>Total Tours:</strong> " . ($cdnCount + $localCount) . "</p>";
    echo "<p><strong>Using CDN URLs:</strong> <span style='color:green;'>$cdnCount</span></p>";
    echo "<p><strong>Using Local Paths:</strong> <span style='color:orange;'>$localCount</span></p>";
    
    if ($cdnCount == $result->num_rows) {
        echo "<p style='color:green; font-weight:bold;'>✅ All tours are using CDN URLs! Storage space saved.</p>";
    }
} else {
    echo "<p style='color:red;'>No tours found in database.</p>";
}

$conn->close();
?>
