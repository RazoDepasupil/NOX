<?php
require_once 'includes/init.php';
require_once 'classes/User.php';

$user = new User();
$profile = $user->getProfile();

include 'includes/templates/header.php';
include 'includes/templates/navbar.php';
?>
<div class="container mt-5">
    <h2>Your Profile</h2>
    <p>Name: <?php echo htmlspecialchars($profile['name']); ?></p>
    <p>Email: <?php echo htmlspecialchars($profile['email']); ?></p>
</div>
<?php include 'includes/templates/footer.php'; ?>