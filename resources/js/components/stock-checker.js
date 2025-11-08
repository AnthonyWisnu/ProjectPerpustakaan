/**
 * Stock Checker Component using Alpine.js
 * Real-time stock availability checker with auto-refresh
 */

// Alpine.js component for stock checking
export function stockChecker(bookId, initialStock = 0) {
    return {
        bookId: bookId,
        availableStock: initialStock,
        isChecking: false,
        isAvailable: initialStock > 0,
        lastChecked: null,
        autoRefreshInterval: null,
        errorMessage: '',

        init() {
            // Initial check
            this.checkStock();

            // Auto-refresh every 30 seconds
            this.autoRefreshInterval = setInterval(() => {
                this.checkStock();
            }, 30000);
        },

        destroy() {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
            }
        },

        /**
         * Check stock availability from server
         */
        async checkStock() {
            if (this.isChecking) return;

            this.isChecking = true;
            this.errorMessage = '';

            try {
                const response = await fetch(`/api/books/${this.bookId}/stock`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.availableStock = data.available_stock || 0;
                    this.isAvailable = this.availableStock > 0;
                    this.lastChecked = new Date();
                } else {
                    console.error('Error checking stock:', response.statusText);
                }
            } catch (error) {
                console.error('Error checking stock:', error);
                this.errorMessage = 'Gagal memeriksa ketersediaan stok';
            } finally {
                this.isChecking = false;
            }
        },

        /**
         * Manual refresh
         */
        refreshStock() {
            this.checkStock();
        },

        /**
         * Get stock status text
         */
        getStatusText() {
            if (this.isChecking) {
                return 'Memeriksa...';
            }

            if (!this.isAvailable) {
                return 'Stok habis';
            }

            if (this.availableStock <= 3) {
                return `Stok terbatas (${this.availableStock})`;
            }

            return `Tersedia (${this.availableStock})`;
        },

        /**
         * Get stock status CSS class
         */
        getStatusClass() {
            if (this.isChecking) {
                return 'text-gray-500';
            }

            if (!this.isAvailable) {
                return 'text-red-600 font-semibold';
            }

            if (this.availableStock <= 3) {
                return 'text-orange-500 font-semibold';
            }

            return 'text-green-600 font-semibold';
        },

        /**
         * Get badge color class
         */
        getBadgeClass() {
            if (this.isChecking) {
                return 'bg-gray-100 text-gray-700';
            }

            if (!this.isAvailable) {
                return 'bg-red-100 text-red-800';
            }

            if (this.availableStock <= 3) {
                return 'bg-orange-100 text-orange-800';
            }

            return 'bg-green-100 text-green-800';
        },

        /**
         * Get last checked time (human readable)
         */
        getLastCheckedText() {
            if (!this.lastChecked) {
                return 'Belum pernah dicek';
            }

            const now = new Date();
            const diff = Math.floor((now - this.lastChecked) / 1000); // difference in seconds

            if (diff < 60) {
                return `${diff} detik yang lalu`;
            } else if (diff < 3600) {
                const minutes = Math.floor(diff / 60);
                return `${minutes} menit yang lalu`;
            } else {
                const hours = Math.floor(diff / 3600);
                return `${hours} jam yang lalu`;
            }
        },

        /**
         * Check if stock is low
         */
        isLowStock() {
            return this.isAvailable && this.availableStock > 0 && this.availableStock <= 3;
        },

        /**
         * Check if out of stock
         */
        isOutOfStock() {
            return !this.isAvailable || this.availableStock === 0;
        },

        /**
         * Get stock percentage (assuming max stock of 10 for visual indicator)
         */
        getStockPercentage() {
            const maxStock = 10;
            const percentage = (this.availableStock / maxStock) * 100;
            return Math.min(percentage, 100);
        },

        /**
         * Get progress bar color class
         */
        getProgressBarClass() {
            const percentage = this.getStockPercentage();

            if (percentage === 0) {
                return 'bg-red-500';
            } else if (percentage <= 30) {
                return 'bg-orange-500';
            } else if (percentage <= 60) {
                return 'bg-yellow-500';
            } else {
                return 'bg-green-500';
            }
        }
    };
}

// Make available globally for Alpine.js
window.stockChecker = stockChecker;