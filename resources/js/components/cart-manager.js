/**
 * Cart Manager Component
 * Handles shopping cart functionality with Alpine.js
 */

export default function cartManager() {
    return {
        // State
        items: [],
        loading: false,
        error: null,
        maxItems: 3,

        // Initialize
        async init() {
            await this.loadCart();
        },

        // Load cart from server
        async loadCart() {
            try {
                this.loading = true;
                const response = await fetch('/member/cart/data', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to load cart');

                const data = await response.json();
                this.items = data.items || [];
            } catch (error) {
                this.error = error.message;
                console.error('Cart load error:', error);
            } finally {
                this.loading = false;
            }
        },

        // Add item to cart
        async addToCart(bookId) {
            if (this.items.length >= this.maxItems) {
                this.showAlert(`Maximum ${this.maxItems} books allowed in cart`);
                return false;
            }

            try {
                this.loading = true;
                const response = await fetch('/member/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ book_id: bookId })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to add to cart');
                }

                await this.loadCart();
                this.showAlert('Book added to cart successfully', 'success');
                return true;
            } catch (error) {
                this.error = error.message;
                this.showAlert(error.message, 'error');
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Remove item from cart
        async removeFromCart(itemId) {
            if (!confirm('Remove this book from cart?')) return;

            try {
                this.loading = true;
                const response = await fetch(`/member/cart/remove/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to remove item');

                await this.loadCart();
                this.showAlert('Book removed from cart', 'success');
            } catch (error) {
                this.error = error.message;
                this.showAlert(error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        // Get cart count
        get cartCount() {
            return this.items.length;
        },

        // Check if cart is full
        get isFull() {
            return this.items.length >= this.maxItems;
        },

        // Check if cart is empty
        get isEmpty() {
            return this.items.length === 0;
        },

        // Check if book is in cart
        isInCart(bookId) {
            return this.items.some(item => item.book_id === bookId);
        },

        // Helper: Get CSRF token
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        // Helper: Show alert
        showAlert(message, type = 'info') {
            // Dispatch custom event for alert display
            window.dispatchEvent(new CustomEvent('show-alert', {
                detail: { message, type }
            }));
        }
    };
}
