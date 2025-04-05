document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuButton = document.querySelector('.fa-bars').parentElement;
    const mobileMenu = document.getElementById('mobile-menu');

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Product quantity inputs
    const quantityInputs = document.querySelectorAll('.quantity-input');

    quantityInputs.forEach(input => {
        const minusBtn = input.parentElement.querySelector('.quantity-minus');
        const plusBtn = input.parentElement.querySelector('.quantity-plus');

        if (minusBtn && plusBtn) {
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                }
            });

            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                input.value = value + 1;
            });
        }
    });

    // Product image gallery
    const mainImage = document.getElementById('main-product-image');
    const thumbnails = document.querySelectorAll('.product-thumbnail');

    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                // Update main image source
                mainImage.src = this.src;

                // Update active thumbnail
                thumbnails.forEach(t => t.classList.remove('border-rs-red'));
                this.classList.add('border-rs-red');
            });
        });
    }

    // Add to cart animation
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Get the cart icon position
            const cartIcon = document.querySelector('.fa-shopping-cart');
            if (!cartIcon) return;

            const cartRect = cartIcon.getBoundingClientRect();
            const buttonRect = button.getBoundingClientRect();

            // Create the flying item
            const flyingItem = document.createElement('div');
            flyingItem.classList.add('flying-item');
            flyingItem.style.position = 'fixed';
            flyingItem.style.width = '20px';
            flyingItem.style.height = '20px';
            flyingItem.style.backgroundColor = '#EE0000';
            flyingItem.style.borderRadius = '50%';
            flyingItem.style.zIndex = '1000';
            flyingItem.style.left = `${buttonRect.left + buttonRect.width / 2}px`;
            flyingItem.style.top = `${buttonRect.top + buttonRect.height / 2}px`;

            document.body.appendChild(flyingItem);

            // Animate it to the cart
            setTimeout(() => {
                flyingItem.style.transition = 'all 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
                flyingItem.style.left = `${cartRect.left + cartRect.width / 2}px`;
                flyingItem.style.top = `${cartRect.top + cartRect.height / 2}px`;
                flyingItem.style.opacity = '0.8';
                flyingItem.style.transform = 'scale(0.5)';

                // Remove the flying item after animation
                setTimeout(() => {
                    document.body.removeChild(flyingItem);

                    // Update cart counter or shake the cart icon
                    cartIcon.classList.add('shake');
                    setTimeout(() => {
                        cartIcon.classList.remove('shake');
                    }, 500);
                }, 800);
            }, 10);
        });
    });
});
