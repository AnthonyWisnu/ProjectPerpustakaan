import './bootstrap';
import Alpine from 'alpinejs';

// Import components
import cartManager from './components/cart-manager';
import stockChecker from './components/stock-checker';
import countdownTimer from './components/countdown-timer';
import qrScanner from './components/qr-scanner';

// Register Alpine components
Alpine.data('cartManager', cartManager);
Alpine.data('stockChecker', stockChecker);
Alpine.data('countdownTimer', countdownTimer);
Alpine.data('qrScanner', qrScanner);

// Start Alpine
window.Alpine = Alpine;
Alpine.start();
