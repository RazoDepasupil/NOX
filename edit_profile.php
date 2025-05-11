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
$profile = $user->getProfile();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $birthday = trim($_POST['birthday']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);

    // Update user object
    $user->setUsername($username);
    $user->setEmail($email);
    $user->setBirthday($birthday);
    $user->setGender($gender);
    $user->setAddress($address);

    if ($user->updateProfile()) {
        $success = 'Profile updated successfully!';
        $profile = $user->getProfile();
    } else {
        $error = 'Failed to update profile.';
    }
}
?>
<div class="mt-5">
    <h2>Edit Profile</h2>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group mb-3">
            <label for="username">Name</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="birthday">Birthday</label>
            <input type="date" name="birthday" class="form-control" value="<?php echo htmlspecialchars($profile['birthday']); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="gender">Gender</label>
            <select name="gender" class="form-control">
                <option value="">Select</option>
                <option value="Male" <?php if ($profile['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if ($profile['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                <option value="Other" <?php if ($profile['gender'] === 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="address">Address</label>
            <textarea name="address" class="form-control"><?php echo htmlspecialchars($profile['address']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
<?php include 'includes/templates/footer.php'; ?> 