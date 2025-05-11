<?php
require_once 'includes/init.php';
$pageTitle = 'About Us';
include 'includes/templates/header.php';
?>

<div class="container mt-5">
    <!-- Hero Section -->
    <div class="about-hero text-center mb-5">
        <h1 class="display-4">About NOX</h1>
        <p class="lead">Redefining Premium Fashion Since 2023</p>
    </div>

    <!-- Our Story -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <img src="assets/images/about/our-story.jpg" alt="Our Story" class="img-fluid rounded shadow">
        </div>
        <div class="col-md-6">
            <h2>Our Story</h2>
            <p>NOX was born from a passion for quality fashion and a vision to create clothing that combines style, comfort, and sustainability. What started as a small boutique has grown into a premium lifestyle brand that caters to those who appreciate the finer things in life.</p>
            <p>Our journey began with a simple mission: to create clothing that makes people feel confident and comfortable while being mindful of our environmental impact.</p>
        </div>
    </div>

    <!-- Our Values -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2>Our Values</h2>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-star mb-3 fa-2x text-primary"></i>
                    <h3 class="h4">Quality</h3>
                    <p>We believe in creating products that last. Every piece is crafted with attention to detail and the finest materials.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-leaf mb-3 fa-2x text-success"></i>
                    <h3 class="h4">Sustainability</h3>
                    <p>Our commitment to the environment is reflected in our eco-friendly practices and sustainable material choices.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-heart mb-3 fa-2x text-danger"></i>
                    <h3 class="h4">Community</h3>
                    <p>We believe in building strong relationships with our customers and giving back to the communities we serve.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Process -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6 order-md-2">
            <img src="assets/images/about/our-process.jpg" alt="Our Process" class="img-fluid rounded shadow">
        </div>
        <div class="col-md-6 order-md-1">
            <h2>Our Process</h2>
            <p>Every NOX piece goes through a meticulous creation process:</p>
            <ul class="list-unstyled">
                <li class="mb-3">
                    <i class="fas fa-pencil-alt me-2 text-primary"></i>
                    <strong>Design:</strong> Thoughtfully crafted by our expert designers
                </li>
                <li class="mb-3">
                    <i class="fas fa-search me-2 text-primary"></i>
                    <strong>Material Selection:</strong> Carefully sourced sustainable materials
                </li>
                <li class="mb-3">
                    <i class="fas fa-tools me-2 text-primary"></i>
                    <strong>Production:</strong> Ethically manufactured with attention to detail
                </li>
                <li class="mb-3">
                    <i class="fas fa-check-double me-2 text-primary"></i>
                    <strong>Quality Control:</strong> Rigorous testing and inspection
                </li>
            </ul>
        </div>
    </div>

    <!-- Team -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2>Our Team</h2>
            <p class="lead">Meet the people who make NOX possible</p>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <img src="assets/images/team/ceo.jpg" class="card-img-top" alt="CEO">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">John Doe</h5>
                    <p class="text-muted">CEO & Founder</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <img src="assets/images/team/designer.jpg" class="card-img-top" alt="Lead Designer">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Jane Smith</h5>
                    <p class="text-muted">Lead Designer</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <img src="assets/images/team/production.jpg" class="card-img-top" alt="Production Manager">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Mike Johnson</h5>
                    <p class="text-muted">Production Manager</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <img src="assets/images/team/marketing.jpg" class="card-img-top" alt="Marketing Director">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Sarah Wilson</h5>
                    <p class="text-muted">Marketing Director</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact CTA -->
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h2>Get in Touch</h2>
            <p class="lead mb-4">Have questions? We'd love to hear from you.</p>
            <a href="contact.php" class="btn btn-primary btn-lg">Contact Us</a>
        </div>
    </div>
</div>

<style>
.about-hero {
    padding: 80px 0;
    background-color: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 60px;
}

.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.team-member img {
    height: 300px;
    object-fit: cover;
}

.fas {
    transition: transform 0.3s ease;
}

.card:hover .fas {
    transform: scale(1.2);
}
</style>

<?php include 'includes/templates/footer.php'; ?> 