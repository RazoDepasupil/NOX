<?php
require_once 'includes/init.php';
require_once 'classes/User.php';
include 'includes/templates/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = new User();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        // Get users data
        $users = readJsonFile(USERS_FILE);
        $user_found = false;
        foreach ($users as $userType => &$userList) {
            foreach ($userList as &$userData) {
                if ($userData['userID'] === $_SESSION['user_id']) {
                    if (!password_verify($current_password, $userData['password'])) {
                        $error = 'Current password is incorrect.';
                    } else {
                        $userData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                        writeJsonFile(USERS_FILE, $users);
                        $success = 'Password changed successfully!';
                    }
                    $user_found = true;
                    break 2;
                }
            }
        }
        if (!$user_found) {
            $error = 'User not found.';
        }
    }
}
?>
<div class="container mt-5">
    <h2>Change Password</h2>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group mb-3">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>
<?php include 'includes/templates/footer.php'; ?> 