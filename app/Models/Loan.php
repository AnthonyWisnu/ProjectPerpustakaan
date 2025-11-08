<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'reservation_id',
        'loan_code',
        'borrowed_at',
        'due_date',
        'extended_at',
        'returned_at',
        'fine_amount',
        'fine_paid',
        'fine_paid_at',
        'confirmed_by',
        'returned_by',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_date' => 'date',
            'extended_at' => 'datetime',
            'returned_at' => 'datetime',
            'fine_paid_at' => 'datetime',
            'fine_amount' => 'decimal:2',
            'fine_paid' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the loan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that belongs to the loan.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the reservation that belongs to the loan.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the admin who confirmed the loan.
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Get the admin who processed the return.
     */
    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Check if loan is overdue.
     */
    public function isOverdue(): bool
    {
        return !$this->returned_at && $this->due_date < now();
    }

    /**
     * Check if loan is active (not returned).
     */
    public function isActive(): bool
    {
        return $this->returned_at === null;
    }

    /**
     * Get days overdue.
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    /**
     * Calculate fine amount based on days overdue.
     */
    public function calculateFine(float $ratePerDay = 1000): float
    {
        $daysOverdue = $this->getDaysOverdue();
        if ($daysOverdue <= 0) {
            return 0;
        }
        return $daysOverdue * $ratePerDay;
    }

    /**
     * Check if loan can be extended.
     */
    public function canBeExtended(): bool
    {
        return $this->isActive() && !$this->extended_at && !$this->isOverdue();
    }

    /**
     * Scope a query to only include active loans.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }

    /**
     * Scope a query to only include overdue loans.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNull('returned_at')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope a query to only include returned loans.
     */
    public function scopeReturned($query)
    {
        return $query->whereNotNull('returned_at');
    }

    /**
     * Scope a query to only include loans with unpaid fines.
     */
    public function scopeUnpaidFines($query)
    {
        return $query->where('fine_amount', '>', 0)
                     ->where('fine_paid', false);
    }
}
