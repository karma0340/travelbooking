<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['admin_user'];
$pageTitle = 'Manage Users';
$activePage = 'users';
$hideDefaultHeader = true;

// Initialize variables
$userData = [
    'id' => '',
    'username' => '',
    'name' => '',
    'email' => '',
    'role' => 'manager',
    'active' => 1
];
$errors = [];
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData['username'] = trim($_POST['username'] ?? '');
    $userData['name'] = trim($_POST['name'] ?? '');
    $userData['email'] = trim($_POST['email'] ?? '');
    $userData['role'] = trim($_POST['role'] ?? 'manager');
    $userData['active'] = isset($_POST['active']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $userId = $_POST['id'] ?? '';
    
    if (empty($userData['username'])) $errors['username'] = 'Username is required';
    if (empty($userData['name'])) $errors['name'] = 'Name is required';
    if (empty($userData['email'])) $errors['email'] = 'Email is required';
    if (empty($userData['role'])) $errors['role'] = 'Role is required';
    
    if (empty($userId) || !empty($password)) {
        if (empty($password)) $errors['password'] = 'Password is required';
        if ($password !== $confirm_password) $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        if (!empty($password)) {
            $userData['password'] = $password;
        }
        
        $result = false;
        if (!empty($userId)) {
            $result = saveUser($userData, $userId);
            if ($result) {
                $message = 'User updated successfully';
                $messageType = 'success';
            } else {
                $errors['general'] = 'Failed to update user';
                $messageType = 'error';
            }
        } else {
            $result = saveUser($userData);
            if ($result) {
                $message = 'User added successfully';
                $messageType = 'success';
                $userData = ['id' => '', 'username' => '', 'name' => '', 'email' => '', 'role' => 'manager', 'active' => 1];
            } else {
                $errors['general'] = 'Failed to add user';
                $messageType = 'error';
            }
        }
    }
}

// Handle edit request
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editUser = getUserById($editId);
    if ($editUser) {
        $userData = $editUser;
    }
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId !== $user['id']) {
        if (deleteUser($deleteId)) {
            $message = 'User deleted successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete user';
            $messageType = 'error';
        }
    } else {
        $message = 'You cannot delete your own account';
        $messageType = 'error';
    }
}

$users = getUsers();
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
            <span class="bg-gradient-to-br from-violet-500 to-purple-600 text-white p-2.5 rounded-xl">
                <i class="fas fa-users"></i>
            </span>
            Manage Users
        </h1>
        <p class="text-gray-500 text-sm mt-1">Create and manage admin users</p>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> mb-4">
    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <span><?= $message ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add/Edit User Form -->
    <div class="lg:col-span-1">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <span class="icon bg-primary/10 text-primary">
                        <i class="fas fa-<?= !empty($userData['id']) ? 'edit' : 'user-plus' ?>"></i>
                    </span>
                    <?= !empty($userData['id']) ? 'Edit User' : 'Add New User' ?>
                </h3>
            </div>
            <div class="admin-card-body">
                <form method="post" action="" class="space-y-4">
                    <input type="hidden" name="id" value="<?= $userData['id'] ?? '' ?>">
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Username</span></label>
                        <input type="text" name="username" 
                               class="input input-bordered w-full <?= isset($errors['username']) ? 'input-error' : '' ?>" 
                               value="<?= htmlspecialchars($userData['username'] ?? '') ?>" required>
                        <?php if (isset($errors['username'])): ?>
                            <span class="text-error text-sm mt-1"><?= $errors['username'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Full Name</span></label>
                        <input type="text" name="name" 
                               class="input input-bordered w-full <?= isset($errors['name']) ? 'input-error' : '' ?>" 
                               value="<?= htmlspecialchars($userData['name'] ?? '') ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <span class="text-error text-sm mt-1"><?= $errors['name'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email</span></label>
                        <input type="email" name="email" 
                               class="input input-bordered w-full <?= isset($errors['email']) ? 'input-error' : '' ?>" 
                               value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="text-error text-sm mt-1"><?= $errors['email'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Role</span></label>
                        <select name="role" class="select select-bordered w-full" required>
                            <option value="admin" <?= (isset($userData['role']) && $userData['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="manager" <?= (isset($userData['role']) && $userData['role'] === 'manager') ? 'selected' : '' ?>>Manager</option>
                        </select>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium"><?= !empty($userData['id']) ? 'New Password (leave blank to keep)' : 'Password' ?></span>
                        </label>
                        <input type="password" name="password" 
                               class="input input-bordered w-full <?= isset($errors['password']) ? 'input-error' : '' ?>" 
                               <?= empty($userData['id']) ? 'required' : '' ?>>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Confirm Password</span></label>
                        <input type="password" name="confirm_password" 
                               class="input input-bordered w-full <?= isset($errors['confirm_password']) ? 'input-error' : '' ?>" 
                               <?= empty($userData['id']) ? 'required' : '' ?>>
                    </div>
                    
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="active" class="checkbox checkbox-primary" 
                                   <?= (!isset($userData['active']) || $userData['active']) ? 'checked' : '' ?>>
                            <span class="label-text">Active Account</span>
                        </label>
                    </div>
                    
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="admin-btn admin-btn-primary flex-1">
                            <i class="fas fa-<?= !empty($userData['id']) ? 'save' : 'plus' ?>"></i>
                            <span><?= !empty($userData['id']) ? 'Update User' : 'Add User' ?></span>
                        </button>
                        <?php if (!empty($userData['id'])): ?>
                        <a href="users.php" class="admin-btn admin-btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="lg:col-span-2">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <span class="icon bg-indigo-100 text-indigo-600">
                        <i class="fas fa-list"></i>
                    </span>
                    All Users
                </h3>
            </div>
            <div class="overflow-x-auto">
                <?php if (empty($users)): ?>
                <div class="admin-empty-state">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>No users found</h3>
                    <p>Add users using the form on the left</p>
                </div>
                <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="admin-avatar <?= $u['role'] === 'admin' ? 'bg-red-100 text-red-600' : 'admin-avatar-primary' ?>">
                                        <?= strtoupper(substr($u['name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="text-xs text-gray-500">@<?= htmlspecialchars($u['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="admin-badge <?= $u['role'] === 'admin' ? 'admin-badge-error' : 'admin-badge-primary' ?>">
                                    <i class="fas fa-<?= $u['role'] === 'admin' ? 'shield-alt' : 'user-tie' ?>"></i>
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="admin-badge <?= $u['active'] ? 'admin-badge-success' : 'admin-badge-neutral' ?>">
                                    <?= $u['active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-gray-500 text-sm">
                                <?= $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never' ?>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a href="users.php?edit=<?= $u['id'] ?>" 
                                       class="admin-action-btn edit tooltip" data-tip="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($u['id'] !== $user['id']): ?>
                                    <button class="admin-action-btn delete delete-user tooltip" 
                                            data-id="<?= $u['id'] ?>" data-tip="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Delete</h3>
        <p class="py-4">Are you sure you want to delete this user? This action cannot be undone.</p>
        <div class="modal-action">
            <button class="btn btn-ghost" onclick="deleteModal.close()">Cancel</button>
            <a href="#" class="btn btn-error" id="confirmDelete">Delete</a>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-user');
    const deleteModal = document.getElementById('deleteModal');
    const confirmDelete = document.getElementById('confirmDelete');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            confirmDelete.href = `users.php?delete=${btn.dataset.id}`;
            deleteModal.showModal();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
