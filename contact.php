<?php
require_once 'includes/init.php';
$pageTitle = 'Contact Us';
include 'includes/templates/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h1 class="mb-4">Contact Us</h1>
            <p class="lead mb-4">We'd love to hear from you. Please fill out this form and we will get in touch with you shortly.</p>
            
            <form action="process_contact.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div class="invalid-feedback">
                        Please provide your name.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">
                        Please provide a valid email address.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <select class="form-select" id="subject" name="subject" required>
                        <option value="">Choose a subject...</option>
                        <option value="general">General Inquiry</option>
                        <option value="product">Product Question</option>
                        <option value="order">Order Status</option>
                        <option value="support">Customer Support</option>
                    </select>
                    <div class="invalid-feedback">
                        Please select a subject.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    <div class="invalid-feedback">
                        Please provide your message.
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="card-body">
                    <h2 class="h4 mb-4">Other Ways to Reach Us</h2>
                    
                    <div class="mb-4">
                        <h3 class="h5"><i class="fas fa-map-marker-alt text-primary me-2"></i>Visit Us</h3>
                        <p class="mb-0">123 Fashion Street</p>
                        <p class="mb-0">Fashion District</p>
                        <p>New York, NY 10001</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="h5"><i class="fas fa-phone text-primary me-2"></i>Call Us</h3>
                        <p class="mb-0">Toll-free: 1-800-NOX-FASHION</p>
                        <p>International: +1 212-555-0123</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="h5"><i class="fas fa-envelope text-primary me-2"></i>Email Us</h3>
                        <p class="mb-0">Customer Service: support@nox.com</p>
                        <p>Business Inquiries: business@nox.com</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="h5"><i class="fas fa-clock text-primary me-2"></i>Business Hours</h3>
                        <p class="mb-0">Monday - Friday: 9:00 AM - 8:00 PM EST</p>
                        <p>Saturday - Sunday: 10:00 AM - 6:00 PM EST</p>
                    </div>
                    
                    <div>
                        <h3 class="h5"><i class="fas fa-share-alt text-primary me-2"></i>Follow Us</h3>
                        <div class="d-flex gap-3 fs-4">
                            <a href="#" class="text-dark"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include 'includes/templates/footer.php'; ?> 