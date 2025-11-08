/**
 * Stock Checker Component
 * Real-time stock availability checker
 */

export default function stockChecker() {
    return {
        // State
        bookId: null,
        stockData: null,
        loading: false,
        error: null,
        checkInterval: null,

        // Initialize with book ID
        init(bookId, autoRefresh = false, intervalSeconds = 30) {
            this.bookId = bookId;
            this.checkStock();

            if (autoRefresh) {
                this.startAutoRefresh(intervalSeconds);
            }
        },

        // Check stock availability
        async checkStock() {
            if (!this.bookId) return;

            try {
                this.loading = true;
                this.error = null;

                const response = await fetch(`/api/books/${this.bookId}/stock`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to check stock');

                this.stockData = await response.json();
            } catch (error) {
                this.error = error.message;
                console.error('Stock check error:', error);
            } finally {
                this.loading = false;
            }
        },

        // Start auto-refresh
        startAutoRefresh(seconds = 30) {
            this.stopAutoRefresh();
            this.checkInterval = setInterval(() => {
                this.checkStock();
            }, seconds * 1000);
        },

        // Stop auto-refresh
        stopAutoRefresh() {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
                this.checkInterval = null;
            }
        },

        // Computed properties
        get isAvailable() {
            return this.stockData?.available_stock > 0;
        },

        get availableStock() {
            return this.stockData?.available_stock || 0;
        },

        get totalStock() {
            return this.stockData?.total_stock || 0;
        },

        get stockPercentage() {
            if (!this.totalStock) return 0;
            return Math.round((this.availableStock / this.totalStock) * 100);
        },

        get stockStatus() {
            if (!this.stockData) return 'unknown';
            if (this.availableStock === 0) return 'out-of-stock';
            if (this.availableStock <= 2) return 'low-stock';
            return 'in-stock';
        },

        get stockStatusColor() {
            switch (this.stockStatus) {
                case 'in-stock': return 'text-green-600';
                case 'low-stock': return 'text-orange-600';
                case 'out-of-stock': return 'text-red-600';
                default: return 'text-gray-600';
            }
        },

        get stockStatusBadge() {
            switch (this.stockStatus) {
                case 'in-stock': return 'bg-green-100 text-green-800';
                case 'low-stock': return 'bg-orange-100 text-orange-800';
                case 'out-of-stock': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        },

        // Cleanup on component destroy
        destroy() {
            this.stopAutoRefresh();
        }
    };
}
