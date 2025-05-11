<?php
require_once 'includes/init.php';
include 'includes/templates/header.php';
?>
<div class="container mt-5">
    <h2>Register</h2>
    <form method="post" action="process_register.php">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Register</button>
    </form>
</div>
<?php include 'includes/templates/footer.php'; ?>