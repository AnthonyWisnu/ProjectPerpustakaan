/**
 * Countdown Timer Component
 * Display countdown for reservation expiry
 */

export default function countdownTimer() {
    return {
        // State
        targetDate: null,
        currentTime: Date.now(),
        timer: null,
        expired: false,

        // Initialize with target date
        init(targetDate) {
            this.targetDate = new Date(targetDate).getTime();
            this.currentTime = Date.now();

            // Check if already expired
            if (this.currentTime >= this.targetDate) {
                this.expired = true;
                return;
            }

            // Start countdown
            this.startCountdown();
        },

        // Start countdown timer
        startCountdown() {
            this.timer = setInterval(() => {
                this.currentTime = Date.now();

                if (this.currentTime >= this.targetDate) {
                    this.expired = true;
                    this.stopCountdown();
                    this.onExpired();
                }
            }, 1000);
        },

        // Stop countdown timer
        stopCountdown() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        // Calculate time remaining
        get timeRemaining() {
            const diff = this.targetDate - this.currentTime;

            if (diff <= 0) {
                return {
                    total: 0,
                    days: 0,
                    hours: 0,
                    minutes: 0,
                    seconds: 0
                };
            }

            const seconds = Math.floor((diff / 1000) % 60);
            const minutes = Math.floor((diff / 1000 / 60) % 60);
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));

            return {
                total: diff,
                days,
                hours,
                minutes,
                seconds
            };
        },

        // Format time component with leading zero
        formatTime(value) {
            return value.toString().padStart(2, '0');
        },

        // Get formatted countdown string
        get formattedTime() {
            const { days, hours, minutes, seconds } = this.timeRemaining;

            if (days > 0) {
                return `${days}d ${this.formatTime(hours)}:${this.formatTime(minutes)}:${this.formatTime(seconds)}`;
            }

            return `${this.formatTime(hours)}:${this.formatTime(minutes)}:${this.formatTime(seconds)}`;
        },

        // Get human-readable time
        get humanReadable() {
            const { days, hours, minutes } = this.timeRemaining;

            if (this.expired) return 'Expired';

            const parts = [];

            if (days > 0) parts.push(`${days} day${days !== 1 ? 's' : ''}`);
            if (hours > 0) parts.push(`${hours} hour${hours !== 1 ? 's' : ''}`);
            if (days === 0 && minutes > 0) parts.push(`${minutes} minute${minutes !== 1 ? 's' : ''}`);

            return parts.join(', ') || 'Less than a minute';
        },

        // Check urgency level
        get urgency() {
            const { total } = this.timeRemaining;
            const hours = total / (1000 * 60 * 60);

            if (hours <= 1) return 'critical';
            if (hours <= 6) return 'high';
            if (hours <= 12) return 'medium';
            return 'low';
        },

        // Get urgency color class
        get urgencyColor() {
            switch (this.urgency) {
                case 'critical': return 'text-red-600';
                case 'high': return 'text-orange-600';
                case 'medium': return 'text-yellow-600';
                case 'low': return 'text-green-600';
                default: return 'text-gray-600';
            }
        },

        // Get urgency badge class
        get urgencyBadge() {
            switch (this.urgency) {
                case 'critical': return 'bg-red-100 text-red-800';
                case 'high': return 'bg-orange-100 text-orange-800';
                case 'medium': return 'bg-yellow-100 text-yellow-800';
                case 'low': return 'bg-green-100 text-green-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        },

        // Callback when timer expires
        onExpired() {
            window.dispatchEvent(new CustomEvent('countdown-expired', {
                detail: { targetDate: this.targetDate }
            }));
        },

        // Cleanup on component destroy
        destroy() {
            this.stopCountdown();
        }
    };
}
