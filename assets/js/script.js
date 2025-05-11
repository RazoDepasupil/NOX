// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add to cart animation
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });

    // Quantity input handling
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateCartItem(this.dataset.cartId, this.value);
        });
    });

    // Image gallery zoom
    const productImages = document.querySelectorAll('.product-gallery img');
    productImages.forEach(img => {
        img.addEventListener('mousemove', function(e) {
            const bounds = this.getBoundingClientRect();
            const x = (e.clientX - bounds.left) / bounds.width * 100;
            const y = (e.clientY - bounds.top) / bounds.height * 100;
            this.style.transformOrigin = `${x}% ${y}%`;
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Toast notifications
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    // Add to cart function
    function addToCart(productId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ productId: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Product added to cart!');
                updateCartCount(data.cartCount);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error adding to cart', 'error');
        });
    }

    // Update cart item quantity
    function updateCartItem(cartId, quantity) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                cartId: cartId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartTotal(data.total);
                showToast('Cart updated!');
            }
        })
        .catch(error => {
            showToast('Error updating cart', 'error');
        });
    }

    // Update cart count in header
    function updateCartCount(count) {
        const cartBadge = document.querySelector('.cart-count');
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.classList.add('animate__animated', 'animate__bounceIn');
            setTimeout(() => {
                cartBadge.classList.remove('animate__animated', 'animate__bounceIn');
            }, 1000);
        }
    }

    // Update cart total
    function updateCartTotal(total) {
        const totalElement = document.querySelector('.cart-total');
        if (totalElement) {
            totalElement.textContent = `$${total.toFixed(2)}`;
        }
    }

    // Lazy loading images
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imageObserver.observe(img));

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.trim();
                if (searchTerm.length >= 2) {
                    performSearch(searchTerm);
                }
            }, 300);
        });
    }

    function performSearch(term) {
        fetch(`search.php?q=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                updateSearchResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }

    function updateSearchResults(results) {
        const resultsContainer = document.querySelector('.search-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = results.map(item => `
                <div class="search-result-item">
                    <img src="${item.image}" alt="${item.name}">
                    <div class="search-result-info">
                        <h5>${item.name}</h5>
                        <p>$${item.price}</p>
                    </div>
                </div>
            `).join('');
        }
    }

    // Sidebar toggle for mobile
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebar && sidebarToggle && sidebarClose && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.add('open');
            sidebarOverlay.style.display = 'block';
        });
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('open');
            sidebarOverlay.style.display = 'none';
        });
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            sidebarOverlay.style.display = 'none';
        });
    }
});

document.addEventListener('scroll', function() {
    const nav = document.querySelector('.navbar.futuristic-nav');
    if (nav) {
        if (window.scrollY > 30) {
            nav.classList.add('shrink');
        } else {
            nav.classList.remove('shrink');
        }
    }
});
