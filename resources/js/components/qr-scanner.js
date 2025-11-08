/**
 * QR Code Scanner Component using Alpine.js
 * Note: Requires html5-qrcode library for actual QR scanning
 * Install: npm install html5-qrcode
 */

// Alpine.js component for QR code scanner
export function qrScanner() {
    return {
        isScanning: false,
        isCameraReady: false,
        scannedData: null,
        errorMessage: '',
        html5QrCode: null,
        cameraId: null,

        init() {
            // Check if html5-qrcode library is available
            if (typeof Html5Qrcode === 'undefined') {
                console.warn('html5-qrcode library not found. Please install: npm install html5-qrcode');
                this.errorMessage = 'Library QR Scanner belum diinstall. Gunakan input manual.';
                return;
            }

            // Initialize scanner
            this.html5QrCode = new Html5Qrcode("qr-reader");
        },

        /**
         * Start QR code scanning
         */
        async startScanning() {
            if (typeof Html5Qrcode === 'undefined') {
                this.errorMessage = 'QR Scanner tidak tersedia. Silakan install html5-qrcode library.';
                return;
            }

            this.isScanning = true;
            this.errorMessage = '';
            this.scannedData = null;

            try {
                // Get available cameras
                const devices = await Html5Qrcode.getCameras();

                if (devices && devices.length > 0) {
                    // Prefer back camera
                    const backCamera = devices.find(device =>
                        device.label.toLowerCase().includes('back') ||
                        device.label.toLowerCase().includes('rear')
                    );
                    this.cameraId = backCamera ? backCamera.id : devices[0].id;

                    // Start scanning
                    await this.html5QrCode.start(
                        this.cameraId,
                        {
                            fps: 10,    // Frames per second
                            qrbox: { width: 250, height: 250 }  // Scanning box size
                        },
                        (decodedText, decodedResult) => {
                            this.onScanSuccess(decodedText, decodedResult);
                        },
                        (errorMessage) => {
                            // Scan error (can be ignored, happens when no QR in view)
                        }
                    );

                    this.isCameraReady = true;
                } else {
                    this.errorMessage = 'Tidak ada kamera yang tersedia';
                    this.isScanning = false;
                }
            } catch (error) {
                console.error('Error starting QR scanner:', error);
                this.errorMessage = 'Gagal memulai scanner: ' + error.message;
                this.isScanning = false;
            }
        },

        /**
         * Stop QR code scanning
         */
        async stopScanning() {
            if (this.html5QrCode && this.isScanning) {
                try {
                    await this.html5QrCode.stop();
                    this.isScanning = false;
                    this.isCameraReady = false;
                } catch (error) {
                    console.error('Error stopping scanner:', error);
                }
            }
        },

        /**
         * Handle successful QR code scan
         */
        onScanSuccess(decodedText, decodedResult) {
            this.scannedData = decodedText;

            // Stop scanning after successful scan
            this.stopScanning();

            // Try to parse JSON data
            try {
                const data = JSON.parse(decodedText);
                this.handleScannedData(data);
            } catch (e) {
                // If not JSON, treat as plain text
                this.handleScannedData({ code: decodedText });
            }
        },

        /**
         * Handle scanned data (customize based on your needs)
         */
        handleScannedData(data) {
            // Check if it's a reservation QR code
            if (data.type === 'reservation' && data.reservation_id) {
                this.processReservation(data.reservation_id);
            }
            // Check if it's a member card QR code
            else if (data.type === 'member_card' && data.member_number) {
                this.processMemberCard(data.member_number);
            }
            // Generic code
            else if (data.code) {
                this.processGenericCode(data.code);
            }
            else {
                this.errorMessage = 'Format QR code tidak valid';
            }
        },

        /**
         * Process reservation QR code
         */
        processReservation(reservationId) {
            // Redirect to reservation confirmation page
            window.location.href = `/admin/reservations/${reservationId}`;
        },

        /**
         * Process member card QR code
         */
        processMemberCard(memberNumber) {
            // Auto-fill member number input if exists
            const input = document.querySelector('input[name="member_number"]');
            if (input) {
                input.value = memberNumber;
                input.dispatchEvent(new Event('input'));
            } else {
                // Or search for member
                window.location.href = `/admin/members?search=${memberNumber}`;
            }
        },

        /**
         * Process generic code (reservation code or member number)
         */
        processGenericCode(code) {
            // Try to auto-fill any code input field
            const codeInput = document.querySelector('input[name="code"], input[name="reservation_code"], input[name="member_number"]');
            if (codeInput) {
                codeInput.value = code;
                codeInput.dispatchEvent(new Event('input'));
            }
        },

        /**
         * Reset scanner
         */
        reset() {
            this.scannedData = null;
            this.errorMessage = '';
        }
    };
}

// Make available globally for Alpine.js
window.qrScanner = qrScanner;