<?php
require_once 'includes/init.php';
require_once 'classes/User.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = new User();
$profile = $user->getProfile();

if (!$profile) {
    setFlashMessage('error', 'Unable to load profile data. Please try again later.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'My Profile';
include 'includes/templates/header.php';
?>

<div class="mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card futuristic-card">
                <div class="card-header bg-dark text-white">
                    <h2 class="mb-0"><i class="fas fa-user-circle me-2"></i>Your Profile</h2>
                </div>
                <div class="card-body">
                    <div class="profile-info">
                        <div class="mb-4">
                            <h3 class="h5 text-muted mb-3">Personal Information</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Name</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($profile['name']); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Email</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($profile['email']); ?></p>
                                </div>
                                <?php if (!empty($profile['birthday'])): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Birthday</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($profile['birthday']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($profile['gender'])): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Gender</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($profile['gender']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($profile['address'])): ?>
                        <div class="mb-4">
                            <h3 class="h5 text-muted mb-3">Address</h3>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($profile['address'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="edit_profile.php" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a href="change_password.php" class="btn btn-outline-primary">
                                <i class="fas fa-key me-2"></i>Change Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>