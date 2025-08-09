    <!-- Footer -->
    <footer class="bg-light pt-5 pb-4 mt-5 border-top">
        <div class="container text-center text-md-start">
            <div class="row">
                <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold">About Us</h6>
                    <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: var(--primary-color); height: 2px"/>
                    <p class="small text-muted">We are a leading dropshipping platform that connects entrepreneurs with quality suppliers worldwide. Our mission is to make e-commerce accessible to everyone.</p>
                </div>
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold">Quick Links</h6>
                    <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: var(--primary-color); height: 2px"/>
                    <p><a href="about.php" class="text-muted">About</a></p>
                    <p><a href="contact.php" class="text-muted">Contact</a></p>
                    <p><a href="testimonials.php" class="text-muted">Testimonials</a></p>
                    <p><a href="faq.php" class="text-muted">FAQ</a></p>
                </div>
                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold">Newsletter</h6>
                    <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: var(--primary-color); height: 2px"/>
                    <form action="subscribe.php" method="POST">
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Your email" required>
                            <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                    <h6 class="text-uppercase fw-bold">Follow Us</h6>
                    <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: var(--primary-color); height: 2px"/>
                    <div>
                        <a href="#" class="btn btn-outline-primary btn-floating m-1"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-floating m-1"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-floating m-1"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?> — All rights reserved.
        </div>
    </footer>

    <!-- JS Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>

    <!-- Custom AJAX Cart Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const cartCountEl = document.getElementById('cart-count');
        const toastEl = document.getElementById('add-to-cart-toast');
        const toast = toastEl ? new bootstrap.Toast(toastEl) : null;
        const toastBody = toastEl ? toastEl.querySelector('.toast-body') : null;

        // Add to cart functionality
        document.body.addEventListener('click', function(e) {
            if (e.target.matches('.add-to-cart')) {
                const button = e.target;
                const productId = button.dataset.productId;
                const originalButtonText = button.innerHTML;

                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('action', 'add');

                fetch('cart-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (cartCountEl) {
                            cartCountEl.textContent = data.cartCount;
                            cartCountEl.classList.remove('d-none');
                        }
                        if (toast && toastBody) {
                            toastBody.textContent = data.message;
                            toast.show();
                        }
                    } else {
                        if (toast && toastBody) {
                            toastBody.textContent = data.message || 'An error occurred.';
                            toast.show();
                        }
                    }
                })
                .catch(error => {
                    if (toast && toastBody) {
                        toastBody.textContent = 'Could not connect to the server.';
                        toast.show();
                    }
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalButtonText;
                });
            }
        });

        // Checkout page quantity controls
        const quantityButtons = document.querySelectorAll('.quantity-change');
        quantityButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const action = this.dataset.action;
                
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('action', action);

                fetch('cart-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`row-${productId}`);

                        if (data.itemRemoved) {
                            row.remove();
                        } else {
                            row.querySelector('.quantity-text').textContent = data.newQty;
                            row.querySelector('.subtotal').textContent = `₹${data.newItemSubtotal}`;
                        }

                        document.querySelector('.subtotal-amount').textContent = `₹${data.subtotal}`;
                        document.querySelector('.shipping-amount').textContent = `₹${data.shipping}`;
                        document.querySelector('.gst-amount').textContent = `₹${data.gst}`;
                        document.querySelector('.total-amount').textContent = `₹${data.grandTotal}`;

                        if (cartCountEl) {
                            cartCountEl.textContent = data.cartCount;
                            if(data.cartCount === 0) cartCountEl.classList.add('d-none');
                        }
                        if(data.cartCount === 0) {
                            document.querySelector('.page-content').innerHTML = '<h1 class="section-title">Your Cart</h1><div class="alert alert-info">Your cart is empty. <a href="products.php">Browse products</a>.</div>';
                        }
                    }
                });
            });
        });

        // Wishlist toggle functionality
        document.body.addEventListener('click', function(e) {
            const button = e.target.closest('.toggle-wishlist');
            if (button) {
                const productId = button.dataset.productId;
                const formData = new FormData();
                formData.append('product_id', productId);

                fetch('wishlist-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = button.querySelector('i');
                        if (data.action === 'added') {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        }
                        // You can also show a toast notification here if you want
                    } else {
                        alert(data.message);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
