/**
 * Cart Manager Component using Alpine.js
 * Manages shopping cart functionality with real-time updates
 */

// Alpine.js component for cart management
export function cartManager() {
    return {
        cartCount: 0,
        isLoading: false,
        errorMessage: '',
        successMessage: '',

        init() {
            this.updateCartCount();
            // Auto-refresh cart count every 30 seconds
            setInterval(() => this.updateCartCount(), 30000);
        },

        /**
         * Update cart count from server
         */
        async updateCartCount() {
            try {
                const response = await fetch('/member/cart/count', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.cartCount = data.count || 0;
                }
            } catch (error) {
                console.error('Error updating cart count:', error);
            }
        },

        /**
         * Add item to cart
         */
        async addToCart(bookId) {
            if (this.isLoading) return;

            this.isLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const response = await fetch('/member/cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ book_id: bookId })
                });

                const data = await response.json();

                if (response.ok) {
                    this.successMessage = data.message || 'Buku berhasil ditambahkan ke keranjang';
                    this.updateCartCount();

                    // Clear success message after 3 seconds
                    setTimeout(() => { this.successMessage = ''; }, 3000);
                } else {
                    this.errorMessage = data.message || 'Gagal menambahkan buku ke keranjang';
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                this.errorMessage = 'Terjadi kesalahan saat menambahkan buku ke keranjang';
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Remove item from cart
         */
        async removeFromCart(bookId) {
            if (this.isLoading) return;

            if (!confirm('Apakah Anda yakin ingin menghapus buku ini dari keranjang?')) {
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const response = await fetch(`/member/cart/${bookId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.successMessage = data.message || 'Buku berhasil dihapus dari keranjang';
                    this.updateCartCount();

                    // Reload page after successful removal
                    setTimeout(() => { window.location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.message || 'Gagal menghapus buku dari keranjang';
                }
            } catch (error) {
                console.error('Error removing from cart:', error);
                this.errorMessage = 'Terjadi kesalahan saat menghapus buku dari keranjang';
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Clear all items from cart
         */
        async clearCart() {
            if (this.isLoading) return;

            if (!confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const response = await fetch('/member/cart/clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.successMessage = data.message || 'Keranjang berhasil dikosongkan';
                    this.updateCartCount();

                    // Reload page after clearing
                    setTimeout(() => { window.location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.message || 'Gagal mengosongkan keranjang';
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
                this.errorMessage = 'Terjadi kesalahan saat mengosongkan keranjang';
            } finally {
                this.isLoading = false;
            }
        }
    };
}

// Make available globally for Alpine.js
window.cartManager = cartManager;