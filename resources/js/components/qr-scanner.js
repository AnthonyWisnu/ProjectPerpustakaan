/**
 * QR Scanner Component
 * Scan QR codes for reservation pickup
 */

export default function qrScanner() {
    return {
        // State
        scanning: false,
        scannedCode: null,
        error: null,
        stream: null,
        video: null,
        canvas: null,

        // Initialize scanner
        async init() {
            this.video = this.$refs.video;
            this.canvas = this.$refs.canvas;

            if (!this.video || !this.canvas) {
                this.error = 'Video or canvas element not found';
                return;
            }
        },

        // Start scanning
        async startScanning() {
            try {
                this.scanning = true;
                this.error = null;
                this.scannedCode = null;

                // Request camera access
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment', // Use back camera
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });

                // Set video source
                this.video.srcObject = this.stream;
                await this.video.play();

                // Start scanning loop
                this.scanFrame();
            } catch (error) {
                this.error = 'Camera access denied or not available';
                this.scanning = false;
                console.error('Scanner error:', error);
            }
        },

        // Stop scanning
        stopScanning() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }

            if (this.video) {
                this.video.srcObject = null;
            }

            this.scanning = false;
        },

        // Scan video frame
        scanFrame() {
            if (!this.scanning || !this.video || !this.canvas) return;

            const context = this.canvas.getContext('2d');

            // Set canvas dimensions to match video
            this.canvas.width = this.video.videoWidth;
            this.canvas.height = this.video.videoHeight;

            // Draw current video frame to canvas
            context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);

            // Get image data
            const imageData = context.getImageData(0, 0, this.canvas.width, this.canvas.height);

            // Try to decode QR code (using a QR library like jsQR)
            const code = this.decodeQRCode(imageData);

            if (code) {
                this.onCodeScanned(code);
            } else {
                // Continue scanning
                requestAnimationFrame(() => this.scanFrame());
            }
        },

        // Decode QR code from image data
        decodeQRCode(imageData) {
            // This is a placeholder for actual QR decoding
            // In real implementation, use a library like jsQR
            // Example: const code = jsQR(imageData.data, imageData.width, imageData.height);

            // For now, return null (no QR code detected)
            return null;

            // Actual implementation with jsQR would be:
            // if (typeof jsQR !== 'undefined') {
            //     const code = jsQR(imageData.data, imageData.width, imageData.height);
            //     return code ? code.data : null;
            // }
            // return null;
        },

        // Handle scanned code
        onCodeScanned(code) {
            this.scannedCode = code;
            this.stopScanning();

            // Dispatch event with scanned code
            window.dispatchEvent(new CustomEvent('qr-scanned', {
                detail: { code }
            }));

            // Optionally process the code
            this.processScannedCode(code);
        },

        // Process scanned reservation code
        async processScannedCode(code) {
            try {
                const response = await fetch('/admin/reservations/verify-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ code })
                });

                const data = await response.json();

                if (response.ok) {
                    // Redirect to reservation details
                    window.location.href = `/admin/reservations/${data.reservation_id}`;
                } else {
                    this.error = data.message || 'Invalid QR code';
                }
            } catch (error) {
                this.error = 'Failed to verify QR code';
                console.error('QR verification error:', error);
            }
        },

        // Manual code input fallback
        async processManualCode(code) {
            this.scannedCode = code;
            await this.processScannedCode(code);
        },

        // Helper: Get CSRF token
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        // Cleanup on component destroy
        destroy() {
            this.stopScanning();
        }
    };
}
