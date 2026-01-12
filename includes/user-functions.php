<?php
/**
 * User Functions
 * CRUD operations for user management and authentication
 */

// Ensure db-connection is loaded
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/db-connection.php';
}

/**
 * Get user by username
 * 
 * @param string $username Username
 * @return array|null User data or null if not found
 */
function getUserByUsername($username) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `users` WHERE `username` = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

/**
 * Verify user credentials
 * 
 * @param string $username Username
 * @param string $password Password
 * @return array|null User data or null if invalid
 */
function verifyUserCredentials($username, $password) {
    $user = getUserByUsername($username);
    
    if (!$user) {
        return null;
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }
    
    // Update last login time
    $conn = getDbConnection();
    $sql = "UPDATE `users` SET `last_login` = NOW() WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $stmt->close();
    
    return $user;
}

/**
 * Get all users for admin panel
 * 
 * @return array Array of user data
 */
function getUsers() {
    $conn = getDbConnection();
    
    $sql = "SELECT `id`, `username`, `name`, `email`, `role`, `active`, `last_login`, `created_at` 
            FROM `users` ORDER BY `id`";
    
    $result = $conn->query($sql);
    $users = [];
    
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }
    
    return $users;
}

/**
 * Create or update a user
 * 
 * @param array $userData User data
 * @param int|null $userId User ID for updates, null for new user
 * @return int|false The user ID or false on failure
 */
function saveUser($userData, $userId = null) {
    $conn = getDbConnection();
    
    if ($userId) {
        // Update existing user
        $sql = "UPDATE `users` SET
            `username` = ?,
            `name` = ?,
            `email` = ?,
            `role` = ?,
            `active` = ?,
            `updated_at` = NOW()
        WHERE `id` = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssii',
            $userData['username'],
            $userData['name'],
            $userData['email'],
            $userData['role'],
            $userData['active'],
            $userId
        );
        
        // If password is provided, update it
        if (!empty($userData['password'])) {
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE `users` SET `password_hash` = ? WHERE `id` = ?");
            $updateStmt->bind_param('si', $passwordHash, $userId);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        // Insert new user
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO `users` (
            `username`, `password_hash`, `name`, `email`, 
            `role`, `active`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssi',
            $userData['username'],
            $passwordHash,
            $userData['name'],
            $userData['email'],
            $userData['role'],
            $userData['active']
        );
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    
    $newId = $userId ?: $stmt->insert_id;
    $stmt->close();
    return $newId;
}

/**
 * Get a user by ID
 * 
 * @param int $id User ID
 * @return array|null User data or null if not found
 */
function getUserById($id) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `users` WHERE `id` = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

/**
 * Delete a user
 * 
 * @param int $userId User ID
 * @return bool Success or failure
 */
function deleteUser($userId) {
    $conn = getDbConnection();
    
    // First check if this is the last admin
    $sql = "SELECT COUNT(*) as count FROM `users` WHERE `role` = 'admin' AND `id` != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    // Don't allow deleting the last admin
    if ($count === 0) {
        return false;
    }
    
    // Delete the user
    $sql = "DELETE FROM `users` WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
?>
