
<?php

namespace App\Services;

use Illuminate\Support\Str;

class QRCodeGenerator
{
    /**
     * Generate unique reservation QR code
     */
    public function generateReservationCode(?int $reservationId = null): string
    {
        $prefix = config('library.code', 'RSV');
        $randomPart = strtoupper(Str::random(6));

        if ($reservationId) {
            return $prefix . str_pad($reservationId, 6, '0', STR_PAD_LEFT);
        }

        return $prefix . $randomPart;
    }

    /**
     * Generate unique loan QR code
     */
    public function generateLoanCode(?int $loanId = null): string
    {
        $prefix = 'LOAN';
        $randomPart = strtoupper(Str::random(6));

        if ($loanId) {
            return $prefix . str_pad($loanId, 6, '0', STR_PAD_LEFT);
        }

        return $prefix . $randomPart;
    }

    /**
     * Generate unique book barcode
     */
    public function generateBarcode(?int $bookId = null): string
    {
        $prefix = config('library.stock.barcode_prefix', 'BK');
        $randomPart = strtoupper(Str::random(8));

        if ($bookId) {
            return $prefix . str_pad($bookId, 8, '0', STR_PAD_LEFT);
        }

        return $prefix . $randomPart;
    }

    /**
     * Generate member card number
     */
    public function generateMemberNumber(int $userId): string
    {
        $prefix = config('library.member.number_prefix', 'MBR');
        $length = config('library.member.number_length', 6);

        return $prefix . str_pad($userId, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Validate reservation QR code format
     */
    public function validateReservationCode(string $code): bool
    {
        $prefix = config('library.code', 'RSV');

        return str_starts_with($code, $prefix) && strlen($code) >= strlen($prefix) + 6;
    }

    /**
     * Validate loan QR code format
     */
    public function validateLoanCode(string $code): bool
    {
        return str_starts_with($code, 'LOAN') && strlen($code) >= 10;
    }

    /**
     * Validate barcode format
     */
    public function validateBarcode(string $code): bool
    {
        $prefix = config('library.stock.barcode_prefix', 'BK');

        return str_starts_with($code, $prefix) && strlen($code) >= strlen($prefix) + 8;
    }

    /**
     * Extract ID from reservation code
     */
    public function extractReservationId(string $code): ?int
    {
        $prefix = config('library.code', 'RSV');

        if (!$this->validateReservationCode($code)) {
            return null;
        }

        $idPart = substr($code, strlen($prefix));

        if (is_numeric($idPart)) {
            return (int) $idPart;
        }

        return null;
    }

    /**
     * Extract ID from loan code
     */
    public function extractLoanId(string $code): ?int
    {
        if (!$this->validateLoanCode($code)) {
            return null;
        }

        $idPart = substr($code, 4); // 'LOAN' = 4 characters

        if (is_numeric($idPart)) {
            return (int) $idPart;
        }

        return null;
    }

    /**
     * Extract ID from barcode
     */
    public function extractBookId(string $code): ?int
    {
        $prefix = config('library.stock.barcode_prefix', 'BK');

        if (!$this->validateBarcode($code)) {
            return null;
        }

        $idPart = substr($code, strlen($prefix));

        if (is_numeric($idPart)) {
            return (int) $idPart;
        }

        return null;
    }
}
