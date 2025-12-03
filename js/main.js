// Online Computer Store - Main JavaScript File
console.log('main.js loaded successfully!');

// ============================================
// FORM VALIDATION
// ============================================

// Registration form validation
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm') || document.querySelector('form[action*="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username')?.value.trim();
            const email = document.getElementById('email')?.value.trim();
            const password = document.getElementById('password')?.value;
            const confirmPassword = document.getElementById('confirm_password')?.value;
            const fullName = document.getElementById('full_name')?.value.trim();

            let isValid = true;
            let errorMessage = '';

            // Validate username
            if (!username || username.length < 3) {
                isValid = false;
                errorMessage = 'Username must be at least 3 characters long.';
            }

            // Validate email
            if (!email || !isValidEmail(email)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }

            // Validate password
            if (!password || password.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters long.';
            }

            // Validate password confirmation
            if (password !== confirmPassword) {
                isValid = false;
                errorMessage = 'Passwords do not match.';
            }

            // Validate full name
            if (!fullName || fullName.length < 2) {
                isValid = false;
                errorMessage = 'Please enter your full name.';
            }

            if (!isValid) {
                e.preventDefault();
                showError(errorMessage);
                return false;
            }
        });
    }

    // Login form validation
    const loginForm = document.getElementById('loginForm') || document.querySelector('form[action*="login"]');
    if (loginForm && !loginForm.action.includes('admin')) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username')?.value.trim();
            const password = document.getElementById('password')?.value;

            if (!username || !password) {
                e.preventDefault();
                showError('Please enter both username and password.');
                return false;
            }
        });
    }
});

// Email validation helper
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show error message
function showError(message) {
    // Remove existing error alerts
    const existingAlert = document.querySelector('.alert-danger');
    if (existingAlert) {
        existingAlert.remove();
    }

    // Create new error alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Insert at the top of main content
    const main = document.querySelector('main');
    if (main) {
        main.insertBefore(alertDiv, main.firstChild);
    }
}

// ============================================
// CART QUANTITY UPDATES
// ============================================

// Dynamic cart quantity updates
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('input[name^="quantities"]');
    
    quantityInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const quantity = parseInt(this.value);
            const maxStock = parseInt(this.max);
            const minStock = parseInt(this.min);

            // Validate quantity
            if (quantity < minStock) {
                this.value = minStock;
                showError('Minimum quantity is ' + minStock);
            } else if (quantity > maxStock) {
                this.value = maxStock;
                showError('Maximum available stock is ' + maxStock);
            }

            // Update subtotal if on cart page
            updateCartSubtotal(this);
        });

        // Prevent negative numbers
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
                e.preventDefault();
            }
        });
    });
});

// Update cart subtotal for a specific row
function updateCartSubtotal(input) {
    const row = input.closest('tr');
    if (row) {
        const priceCell = row.querySelector('td:nth-child(2)');
        const subtotalCell = row.querySelector('td:nth-child(5)');
        
        if (priceCell && subtotalCell) {
            const price = parseFloat(priceCell.textContent.replace('$', '').replace(',', ''));
            const quantity = parseInt(input.value);
            const subtotal = price * quantity;
            
            subtotalCell.innerHTML = '<strong>$' + subtotal.toFixed(2) + '</strong>';
            
            // Update total
            updateCartTotal();
        }
    }
}

// Update cart total
function updateCartTotal() {
    const subtotals = document.querySelectorAll('tbody tr td:nth-child(5) strong');
    let total = 0;
    
    subtotals.forEach(function(cell) {
        const value = parseFloat(cell.textContent.replace('$', '').replace(',', ''));
        if (!isNaN(value)) {
            total += value;
        }
    });
    
    const totalCell = document.querySelector('tfoot tr td:nth-child(4) strong');
    if (totalCell) {
        totalCell.textContent = '$' + total.toFixed(2);
    }
}

// ============================================
// ADD TO CART BUTTON HANDLERS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const addToCartForms = document.querySelectorAll('form[action*="add-to-cart"]');
    
    addToCartForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const quantityInput = form.querySelector('input[name="quantity"]');
            const productId = form.querySelector('input[name="product_id"]');
            
            if (quantityInput && productId) {
                const quantity = parseInt(quantityInput.value);
                const maxStock = parseInt(quantityInput.max);
                
                // Validate quantity
                if (quantity <= 0) {
                    e.preventDefault();
                    showError('Please enter a valid quantity.');
                    return false;
                }
                
                if (quantity > maxStock) {
                    e.preventDefault();
                    showError('Insufficient stock. Only ' + maxStock + ' available.');
                    quantityInput.value = maxStock;
                    return false;
                }
                
                // Show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Adding...';
                }
            }
        });
    });

    // Handle add to cart buttons (if using button instead of form)
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const quantity = this.closest('form')?.querySelector('input[name="quantity"]')?.value || 1;
            
            if (productId) {
                addToCartAjax(productId, quantity);
            }
        });
    });
});

// ============================================
// AJAX EXAMPLE - FETCH PRODUCTS
// ============================================

// Example AJAX function to fetch products
function fetchProductsAjax(categoryId = null, searchTerm = '') {
    const url = new URL('products.php', window.location.origin);
    if (categoryId) {
        url.searchParams.append('category', categoryId);
    }
    if (searchTerm) {
        url.searchParams.append('search', searchTerm);
    }

    // Show loading indicator
    const productsContainer = document.querySelector('.products-container');
    if (productsContainer) {
        productsContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    }

    fetch(url.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Parse the HTML response and extract product data
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const products = doc.querySelectorAll('.product-card, .card');
            
            console.log('Products fetched:', products.length);
            
            // You can process the products here
            // For example, update the products container
            if (productsContainer) {
                // This is just an example - you'd need to properly render the products
                productsContainer.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            showError('Failed to load products. Please refresh the page.');
        });
}

// Example: Add to cart via AJAX (if you want to implement AJAX cart)
function addToCartAjax(productId, quantity) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('add-to-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log('Product added to cart:', productId);
        // Show success message
        showSuccess('Product added to cart!');
        // Optionally update cart count in navbar
        updateCartCount();
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showError('Failed to add product to cart.');
    });
}

// Show success message
function showSuccess(message) {
    const existingAlert = document.querySelector('.alert-success');
    if (existingAlert) {
        existingAlert.remove();
    }

    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const main = document.querySelector('main');
    if (main) {
        main.insertBefore(alertDiv, main.firstChild);
    }
}

// Update cart count in navbar (if you add a cart count badge)
function updateCartCount() {
    // This would fetch the cart count from the server
    // For now, just a placeholder
    const cartBadge = document.querySelector('.cart-count-badge');
    if (cartBadge) {
        // You could make an AJAX call to get the actual count
        // fetch('get-cart-count.php').then(...)
    }
}

// ============================================
// SEARCH FUNCTIONALITY
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            // Debounce search - wait 500ms after user stops typing
            if (searchTerm.length >= 2) {
                searchTimeout = setTimeout(function() {
                    console.log('Searching for:', searchTerm);
                    // You could implement live search here using fetchProductsAjax
                }, 500);
            }
        });
    }
});

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Format price
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

