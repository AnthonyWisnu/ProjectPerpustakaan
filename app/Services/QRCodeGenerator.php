<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * QRCodeGenerator Service
 *
 * NOTE: This service is designed to work with SimpleSoftwareIO/simple-qrcode package.
 * Install with: composer require simplesoftwareio/simple-qrcode
 */
class QRCodeGenerator
{
    /**
     * Generate a QR code image.
     *
     * @param string $data The data to encode in the QR code
     * @param int $size The size of the QR code in pixels
     * @return string|null Base64 encoded image or null on failure
     */
    public function generate(string $data, int $size = 200): ?string
    {
        // Requires SimpleSoftwareIO/simple-qrcode package
        // Example implementation:
        /*
        try {
            $qrCode = \QrCode::format('png')
                ->size($size)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($data);

            return base64_encode($qrCode);
        } catch (\Exception $e) {
            \Log::error('QR code generation failed: ' . $e->getMessage());
            return null;
        }
        */

        // Placeholder implementation
        \Log::warning('QRCodeGenerator::generate() called but SimpleSoftwareIO/simple-qrcode is not installed.');

        return null;
    }

    /**
     * Generate and save a QR code for a reservation.
     *
     * @param Reservation $reservation
     * @return string|null The path to the saved QR code file
     */
    public function generateForReservation(Reservation $reservation): ?string
    {
        // Create data for QR code (reservation code and essential info)
        $data = json_encode([
            'type' => 'reservation',
            'code' => $reservation->reservation_code,
            'id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'total_books' => $reservation->total_books,
            'expired_at' => $reservation->expired_at?->format('Y-m-d H:i:s'),
        ]);

        // Generate QR code
        $qrCodeImage = $this->generate($data, 300);

        if (!$qrCodeImage) {
            return null;
        }

        // Generate filename
        $filename = 'qrcodes/reservations/' . $reservation->reservation_code . '.png';

        // Save to storage
        try {
            Storage::disk('public')->put($filename, base64_decode($qrCodeImage));

            // Update reservation record with QR code path
            $reservation->update(['qr_code_path' => $filename]);

            \Log::info("QR code generated for reservation: {$reservation->reservation_code}", [
                'path' => $filename,
            ]);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Failed to save QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate and save a QR code for a member card.
     *
     * @param User $user
     * @return string|null The path to the saved QR code file
     */
    public function generateForMember(User $user): ?string
    {
        // Create data for QR code (member number and essential info)
        $data = json_encode([
            'type' => 'member',
            'member_number' => $user->member_number,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        // Generate QR code
        $qrCodeImage = $this->generate($data, 250);

        if (!$qrCodeImage) {
            return null;
        }

        // Generate filename
        $filename = 'qrcodes/members/' . $user->member_number . '.png';

        // Save to storage
        try {
            Storage::disk('public')->put($filename, base64_decode($qrCodeImage));

            \Log::info("QR code generated for member: {$user->member_number}", [
                'path' => $filename,
                'user_id' => $user->id,
            ]);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Failed to save member QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the full storage path for a QR code file.
     *
     * @param string $filename
     * @return string
     */
    public function getPath(string $filename): string
    {
        return Storage::disk('public')->path($filename);
    }

    /**
     * Get the public URL for a QR code file.
     *
     * @param string $filename
     * @return string
     */
    public function getUrl(string $filename): string
    {
        return Storage::disk('public')->url($filename);
    }

    /**
     * Delete a QR code file.
     *
     * @param string $filename
     * @return bool
     */
    public function delete(string $filename): bool
    {
        try {
            if (Storage::disk('public')->exists($filename)) {
                Storage::disk('public')->delete($filename);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Failed to delete QR code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a simple data URL QR code (for inline display).
     *
     * @param string $data
     * @param int $size
     * @return string|null
     */
    public function generateDataUrl(string $data, int $size = 200): ?string
    {
        $qrCode = $this->generate($data, $size);

        if (!$qrCode) {
            return null;
        }

        return 'data:image/png;base64,' . $qrCode;
    }
}
