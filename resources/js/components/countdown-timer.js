/**
 * Countdown Timer Component using Alpine.js
 * Displays countdown to a specific date/time with real-time updates
 */

// Alpine.js component for countdown timer
export function countdownTimer(targetDate) {
    return {
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        isExpired: false,
        intervalId: null,

        init() {
            this.updateCountdown();
            // Update every second
            this.intervalId = setInterval(() => this.updateCountdown(), 1000);
        },

        destroy() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
            }
        },

        /**
         * Update countdown values
         */
        updateCountdown() {
            const now = new Date().getTime();
            const target = new Date(targetDate).getTime();
            const distance = target - now;

            if (distance < 0) {
                this.isExpired = true;
                this.days = 0;
                this.hours = 0;
                this.minutes = 0;
                this.seconds = 0;

                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
                return;
            }

            this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
            this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
        },

        /**
         * Get formatted time string
         */
        getFormattedTime() {
            if (this.isExpired) {
                return 'Sudah berakhir';
            }

            const parts = [];
            if (this.days > 0) parts.push(`${this.days} hari`);
            if (this.hours > 0) parts.push(`${this.hours} jam`);
            if (this.minutes > 0) parts.push(`${this.minutes} menit`);
            if (this.seconds > 0) parts.push(`${this.seconds} detik`);

            return parts.join(' ') || 'Kurang dari 1 detik';
        },

        /**
         * Get total hours remaining
         */
        getTotalHours() {
            return (this.days * 24) + this.hours;
        },

        /**
         * Check if time is critical (less than 6 hours remaining)
         */
        isCritical() {
            return !this.isExpired && this.getTotalHours() < 6;
        },

        /**
         * Get CSS class based on time remaining
         */
        getStatusClass() {
            if (this.isExpired) return 'text-red-600 font-bold';
            if (this.isCritical()) return 'text-orange-600 font-semibold';
            return 'text-gray-700';
        }
    };
}

// Make available globally for Alpine.js
window.countdownTimer = countdownTimer;